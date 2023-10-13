<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\CategorySerializer;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Model\CategoryData;

class Category implements ObserverInterface
{
    private Data $helper;

    private ApiClient $apiClient;

    private CategorySerializer $categorySerializer;

    private CategoryData $categoryData;

    /**
     * @param Data $helper
     * @param ApiClient $apiClient
     * @param CategorySerializer $categorySerializer
     * @param CategoryData $categoryData
     */
    public function __construct(
        Data $helper,
        ApiClient $apiClient,
        CategorySerializer $categorySerializer,
        CategoryData $categoryData
    ) {
        $this->helper = $helper;
        $this->apiClient = $apiClient;
        $this->categorySerializer = $categorySerializer;
        $this->categoryData = $categoryData;
    }

    public function execute(Observer $observer): void
    {
        try {
            $category = $observer->getEvent()->getCategory();
            $categoryStoreId = $category->getStoreId();

            if ($categoryStoreId == 0) {
                $categoryStoreIds = $this->helper->getStoreIdsPerProject($category->getStoreIds());
            } else {
                if (!$this->helper->isEnabled($categoryStoreId)) {
                    return;
                }
                $categoryStoreIds[] = $categoryStoreId;
            }

            foreach ($categoryStoreIds as $storeId) {
                $categoryObject = $this->categoryData->getCategoryWithRequestPath($category->getId(), $storeId);
                $client = $this->apiClient->getClient($storeId);
                $client->category($this->categorySerializer->serialize($categoryObject));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
