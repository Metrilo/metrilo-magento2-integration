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
        \Metrilo\Analytics\Helper\Data               $helper,
        \Metrilo\Analytics\Helper\ApiClient          $apiClient,
        \Metrilo\Analytics\Helper\CategorySerializer $categorySerializer,
        \Metrilo\Analytics\Model\CategoryData        $categoryData
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->categorySerializer = $categorySerializer;
        $this->categoryData       = $categoryData;
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
                $categoryObject = $this->categoryData->getCategoryWithRequestPath($category->getId(), $storeId);
                $client         = $this->apiClient->getClient($storeId);
                $client->category($this->categorySerializer->serialize($categoryObject));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
