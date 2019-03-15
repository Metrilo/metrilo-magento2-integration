<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Category implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                     $helper
     * @param \Metrilo\Analytics\Helper\ApiClient                $apiClient
     * @param \Metrilo\Analytics\Helper\CustomerSerializer       $customerSerializer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data                     $helper,
        \Metrilo\Analytics\Helper\ApiClient                $apiClient,
        \Metrilo\Analytics\Helper\CategorySerializer       $categorySerializer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->categorySerializer = $categorySerializer;
        $this->scopeConfig        = $scopeConfig;
    }
    
    private function mapConfigToStore($storeIds) {
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
        
        return array_unique($storeIdConfigMap);
    }
    
    private function sendSerializedData($category) {
        $client = $this->apiClient->getClient($category->getStoreId());
        $client->category($this->categorySerializer->serialize($category));
    }
    
    public function execute(Observer $observer)
    {
        try {
            $category = $observer->getEvent()->getCategory();
            if ($category->getStoreId() == 0) {
                foreach ($this->mapConfigToStore($category->getStoreIds()) as $storeId => $configVal) {
                    $category->setStoreId($storeId);
                    $this->sendSerializedData($category);
                }
            } else {
                $this->sendSerializedData($category);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
