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
        \Metrilo\Analytics\Helper\Data                                  $helper,
        \Metrilo\Analytics\Helper\ApiClient                             $apiClient,
        \Metrilo\Analytics\Helper\CategorySerializer                    $categorySerializer,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->categorySerializer = $categorySerializer;
        $this->categoryCollection = $categoryCollection;
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
        array_unique($storeIdConfigMap);
        
        return array_keys($storeIdConfigMap);
    }
    
    public function execute(Observer $observer)
    {
        try {
            $category = $observer->getEvent()->getCategory();
            if ($category->getStoreId() == 0) {
                $categoryStoreIds = $this->mapConfigToStore($category->getStoreIds());
            } else {
                $categoryStoreIds[] = $category->getStoreId();
            }
            foreach ($categoryStoreIds as $storeId) {
                $categoryObjectWithRequestPath = $this->categoryCollection->create()->setStore($storeId)->addAttributeToSelect('name')->addAttributeToFilter('entity_id', $category->getId())->addUrlRewriteToResult()->getFirstItem();
                $categoryObjectWithRequestPath->setStoreId($storeId);
                $client = $this->apiClient->getClient($storeId);
                $client->category($this->categorySerializer->serialize($categoryObjectWithRequestPath));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
