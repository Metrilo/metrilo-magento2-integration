<?php

namespace Metrilo\Analytics\Helper;

/**
 * Helper class
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const chunkItems = 50;

    const DATA_TAG = 'metrilo_events';

    const MODULE_NAME = 'Metrilo_Analytics';

    public $js_domain = 't.metrilo.com';
    private $push_domain = 'http://p.metrilo.com';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Customer\Model\Session                    $session
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param Client                                             $clientHelper
     * @param OrderSerializer                                    $orderSerializer
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\ProductMetadata             $metaData
     * @param \Magento\Framework\Module\ModuleListInterface      $moduleList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Customer\Model\Session                    $session,
        \Psr\Log\LoggerInterface                           $logger,
        \Magento\Framework\Json\Helper\Data                $jsonHelper,
        \Metrilo\Analytics\Helper\Client                   $clientHelper,
        \Metrilo\Analytics\Helper\AdminStoreResolver       $resolver,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        \Magento\Framework\App\ProductMetadata             $metaData,
        \Magento\Framework\Module\ModuleListInterface      $moduleList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->config       = $config;
        $this->session      = $session;
        $this->logger       = $logger;
        $this->jsonHelper   = $jsonHelper;
        $this->clientHelper = $clientHelper;
        $this->resolver     = $resolver;
        $this->storeManager = $storeManager;
        $this->metaData     = $metaData;
        $this->moduleList   = $moduleList;
        $this->scopeConfig  = $scopeConfig;
    }

    /**
     * Get storeId for the current request context
     *
     * @param null $request
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Check if metrilo module is enabled
     *
     * @return boolean
     */
    public function isEnabled($storeId)
    {
        return $this->config->getValue(
            'metrilo_analytics/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get API Token from system configuration
     *
     * @return string
     */
    public function getApiToken($storeId)
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiSecret($storeId)
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_endpoint'
        );
    }

    /**
     * Get session data with "metrilo_events" key
     *
     * @return array
     */
    public function getSessionEvents()
    {
        $events = [];
        if ($this->session->getData(self::DATA_TAG)) {
            $events = $this->session->getData(self::DATA_TAG, true);
        }
        return $events;
    }

    /**
     * Add event to session
     *
     * @param string  $method
     * @param string  $type
     * @param array   $data
     * @param boolean|string $metaData
     */
    public function addSessionEvent($method, $type, $data, $metaData = false)
    {
        $events = [];
        if ($this->session->getData(self::DATA_TAG) != '') {
            $events = (array)$this->session->getData(self::DATA_TAG);
        }
        $eventToAdd = array(
            'method' => $method,
            'type' => $type,
            'data' => $data
        );
        if ($metaData) {
            $eventToAdd['metaData'] = $metaData;
        }
        array_push($events, $eventToAdd);
        $this->session->setData(self::DATA_TAG, $events);
    }

    public function log($value)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/metrilo.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->err($value);
    }

    /**
     * Creates project activity
     *
     * @param string $type The type of the activity to create
     *
     * @return boolean Indicates if the creation was successful
     */
    public function createActivity($storeId, $type)
    {
        $key = $this->getApiToken($storeId);
        $secret = $this->getApiSecret($storeId);

        $data = array(
            'type' => $type,
            'signature' => md5($key . $type . $secret)
        );

        $url = $this->push_domain.'/tracking/' . $key . '/activity';

        $responseCode = $this->clientHelper->post($url, $data)['code'];

        return $responseCode == 200;
    }

    /**
     * Log error to logs
     *
     * @param  \Exception $exception
     * @return void
     */
    public function logError($exception)
    {
        if ($exception instanceof \Exception) {
            $this->log($exception->getMessage());
        } else {
            $this->log($exception);
        }
    }
    
    public function requestLogger($loggerPath, $loggerData) {
        file_put_contents($loggerPath, $loggerData, FILE_APPEND);
        file_put_contents($loggerPath, PHP_EOL, FILE_APPEND);
    }
    
    public function getStoreIdsPerProject($storeIds) {
        $storeIdConfigMap = [];
        foreach ($storeIds as $storeId) {
            if ($storeId == 0) { // store 0 is always admin
                continue;
            }
            
            if ($this->isEnabled($storeId)) {
                $storeIdConfigMap[$storeId] = $this->scopeConfig
                    ->getValue(
                        'metrilo_analytics/general/api_key',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
            }
        }
        $storeIdConfigMap = array_unique($storeIdConfigMap);
        
        return array_keys($storeIdConfigMap);
    }
}
