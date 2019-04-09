<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Product implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                     $helper
     * @param \Metrilo\Analytics\Helper\ApiClient                $apiClient
     * @param \Metrilo\Analytics\Helper\ProductSerializer        $productSerializer,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data                     $helper,
        \Metrilo\Analytics\Helper\ApiClient                $apiClient,
        \Metrilo\Analytics\Helper\ProductSerializer        $productSerializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper            = $helper;
        $this->apiClient         = $apiClient;
        $this->productSerializer = $productSerializer;
        $this->scopeConfig       = $scopeConfig;
    }
    
    private function getStoreIdsPerProject($storeIds) {
        $storeIdConfigMap = [];
        foreach ($storeIds as $storeId) {
            if ($storeId == 0) { // store 0 is always admin
                continue;
            }
            $storeIdConfigMap[$storeId] = $this->scopeConfig
                ->getValue(
                    'metrilo_analytics/general/api_key',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
        }
        $storeIdConfigMap = array_unique($storeIdConfigMap);
        
        return array_keys($storeIdConfigMap);
    }
    
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($product->getStoreId() == 0) {
                $productStoreIds = $this->getStoreIdsPerProject($product->getStoreIds());
            } else {
                $productStoreIds[] = $product->getStoreId();
            }
    
            foreach ($productStoreIds as $storeId) {
                $product->setStoreId($storeId);
                $client = $this->apiClient->getClient($storeId);
                $client->product($this->productSerializer->serialize($product));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
