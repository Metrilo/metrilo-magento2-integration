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
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Customer\Model\Session $session,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Metrilo\Analytics\Helper\Client $clientHelper,
        \Metrilo\Analytics\Helper\OrderSerializer $orderSerializer,
        \Metrilo\Analytics\Helper\AdminStoreResolver $resolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadata $metaData,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->config          = $config;
        $this->session         = $session;
        $this->logger          = $logger;
        $this->jsonHelper      = $jsonHelper;
        $this->clientHelper    = $clientHelper;
        $this->orderSerializer = $orderSerializer;
        $this->resolver        = $resolver;
        $this->storeManager    = $storeManager;
        $this->metaData        = $metaData;
        $this->moduleList      = $moduleList;
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

    /**
     * API call to Metrilo to submit information
     *
     * @param  int $storeId
     * @param  array $orders
     * @return void
     */
    public function callBatchApi($storeId, $orders)
    {
        $ordersForSubmission = $this->_buildOrdersForSubmission($orders);
        $call = $this->_buildCall($storeId, $ordersForSubmission);
        $this->_callMetriloApi($storeId, $call);
    }

    /**
     * Create submition ready arrays from Array of \Magento\Sales\Model\Order
     *
     * @param \Magento\Sales\Model\Order[] $orders
     * @return array
     */
    protected function _buildOrdersForSubmission($orders)
    {
        $ordersForSubmission = [];
        foreach ($orders as $order) {
            if ($order->getId()) {
                array_push($ordersForSubmission, $this->orderSerializer->buildOrderForSubmission($order));
            }
        }
        return $ordersForSubmission;
    }

    /**
     * Create call array
     *
     * @param  int $storeId
     * @param  array $ordersForSubmission
     * @return array
     */
    protected function _buildCall($storeId, $ordersForSubmission)
    {
        return array(
            'token'    => $this->getApiToken($storeId),
            'events'   => $ordersForSubmission,
            // for debugging/support purposes
            'platform' => 'Magento ' . $this->metaData->getEdition() . ' ' . $this->metaData->getVersion(),
            'version'  => $this->moduleList->getOne(self::MODULE_NAME)['setup_version']
        );
    }

    /**
     * Submit orders to Metrilo API via post request
     *
     * @param  int $storeId
     * @param  array $call
     * @return void
     */
    protected function _callMetriloApi($storeId, $call)
    {
        ksort($call);
        $basedCall = base64_encode($this->jsonHelper->jsonEncode($call));
        $signature = md5($basedCall . $this->getApiSecret($storeId));
        $requestBody = [
            's'   => $signature,
            'hs'  => $basedCall
        ];
        $this->clientHelper->post($this->push_domain . '/bt', $requestBody);
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
}
