<?php

namespace Metrilo\Analytics\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const chunkItems = 50;

    const MODULE_NAME = 'Metrilo_Analytics';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Psr\Log\LoggerInterface                           $logger,
        \Metrilo\Analytics\Helper\Client                   $clientHelper,
        \Metrilo\Analytics\Helper\AdminStoreResolver       $resolver,
        \Metrilo\Analytics\Helper\CartEvents               $cartEvents,
        \Magento\Store\Model\StoreManagerInterface         $storeManager
    ) {
        $this->config       = $config;
        $this->logger       = $logger;
        $this->clientHelper = $clientHelper;
        $this->resolver     = $resolver;
        $this->cartEvents   = $cartEvents;
        $this->storeManager = $storeManager;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function isEnabled($storeId)
    {
        return $this->config->getValue(
            'metrilo_analytics/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiToken($storeId)
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiSecret($storeId)
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getApiEndpoint()
    {
        return $this->config->getValue(
            'metrilo_analytics/general/api_endpoint'
        );
    }

    public function log($value)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/metrilo.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->err($value);
    }

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
            if ($storeId == 0 || !$this->isEnabled($storeId)) { // store 0 is always admin
                continue;
            }
            
            $storeIdConfigMap[$storeId] = $this->config
                ->getValue(
                    'metrilo_analytics/general/api_key',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
        }
        $storeIdConfigMap = array_unique($storeIdConfigMap);
        
        return array_keys($storeIdConfigMap);
    }
}
