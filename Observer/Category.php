<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Category implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                                  $helper
     * @param \Metrilo\Analytics\Helper\ApiClient                             $apiClient
     * @param \Metrilo\Analytics\Helper\CategorySerializer                    $categorySerializer
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data                                  $helper,
        \Metrilo\Analytics\Helper\ApiClient                             $apiClient,
        \Metrilo\Analytics\Helper\CategorySerializer                    $categorySerializer,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Store\Model\StoreManagerInterface                      $storeManager
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->categorySerializer = $categorySerializer;
        $this->categoryCollection = $categoryCollection;
        $this->storeManager       = $storeManager;
    }
    
    private function getCategoryWithRequestPath($categoryId, $storeId) {
        $this->storeManager->setCurrentStore($storeId);
        
        $categoryObject = $this->categoryCollection
            ->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('entity_id', $categoryId)
            ->addUrlRewriteToResult()
            ->getFirstItem();
        
        $categoryObject->setStoreId($storeId);
        
        return $categoryObject;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $category = $observer->getEvent()->getCategory();
            if ($category->getStoreId() == 0) {
                $categoryStoreIds = $this->helper->getStoreIdsPerProject($category->getStoreIds());
            } else {
                $categoryStoreIds[] = $category->getStoreId();
            }
            foreach ($categoryStoreIds as $storeId) {
                $categoryObject = $this->getCategoryWithRequestPath($category->getId(), $storeId);
                $client = $this->apiClient->getClient($storeId);
                $client->category($this->categorySerializer->serialize($categoryObject));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
