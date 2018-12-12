<?php

namespace Metrilo\Analytics\Model;

class CategoryData
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->storeManager = $storeManager;
    }

    public function getCategoryQuery($storeId = 0)
    {
        return $this->categoryCollection->create()->addAttributeToSelect('name')
                    ->joinTable(
                        ['url' => 'url_rewrite'],
                        'entity_id = entity_id',
                        ['request_path', 'store_id'],
                        ['entity_type' => 'category', 'store_id' => $storeId]
                    );
    }

    public function getCategories($storeId, $chunkId, $chunkItems)
    {
        $storeBaseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(); // Used for multiwebsite configuration base url
        $categoriesArray = [];
        $categories = $this->getCategoryQuery($storeId)
                           ->setPageSize($chunkItems)
                           ->setCurPage($chunkId + 1);

        foreach ($categories as $category) {
            $categoriesArray[] = [
                'id'   => $category->getId(),
                'name' => $category->getName(),
                'url'  => $storeBaseUrl . $category->getRequestPath()
            ];
        }
        return $categoriesArray;
    }
}