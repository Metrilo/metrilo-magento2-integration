<?php

namespace Metrilo\Analytics\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const chunkItems = 50;

    const MODULE_NAME = 'Metrilo_Analytics';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface         $storeManager
    ) {
        $this->config       = $config;
        $this->storeManager = $storeManager;
    }

    public function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
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
        $apiEndpoint = $this->config->getValue(
            'metrilo_analytics/general/api_endpoint'
        );
        
        return ($apiEndpoint) ? $apiEndpoint : 'https://trk.mtrl.me';
    }

    public function getActivityEndpoint()
    {
        $activityEndpoint = $this->config->getValue(
            'metrilo_analytics/general/activity_endpoint'
        );
        
        return ($activityEndpoint) ? $activityEndpoint : 'https://p.metrilo.com';
    }

    public function logError($exception)
    {
        if ($exception instanceof \Exception) {
            $this->log($exception->getMessage());
        } else {
            $this->log($exception);
        }
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
    
    private function log($value)
    {
        $logLocation = BP . '/var/log/metrilo.log';
        if (file_exists($logLocation) && filesize($logLocation) > 10 * 1024 * 1024) {
            unlink($logLocation);
        }
        
        $writer = new \Zend\Log\Writer\Stream($logLocation);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        $logger->err($value);
    }
}
