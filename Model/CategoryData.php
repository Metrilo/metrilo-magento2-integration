<?php

namespace Metrilo\Analytics\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Metrilo\Analytics\Helper\Data;

class CategoryData
{
    private $chunkItems = Data::CHUNK_ITEMS;

    private CollectionFactory $categoryCollection;

    public function __construct(
        CollectionFactory $categoryCollection
    ) {
        $this->categoryCollection = $categoryCollection;
    }

    public function getCategories($storeId, $chunkId)
    {
        return $this->getCategoryQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }

    public function getCategoryChunks($storeId)
    {
        $totalCategories = $this->getCategoryQuery($storeId)->getSize();
        return (int) ceil($totalCategories / $this->chunkItems);
    }

    public function getCategoryWithRequestPath($categoryId, $storeId)
    {
        return $this->categoryCollection
                    ->create()
                    ->addAttributeToSelect('name')
                    ->addAttributeToFilter('entity_id', $categoryId)
                    ->setStore($storeId)
                    ->addUrlRewriteToResult()
                    ->getFirstItem()
                    ->setStoreId($storeId);
    }

    private function getCategoryQuery($storeId)
    {
        return $this->categoryCollection->create()->addAttributeToSelect('name')
            ->joinTable(
                ['url' => 'url_rewrite'],
                'entity_id = entity_id',
                ['request_path', 'store_id'],
                ['entity_type' => 'category', 'store_id' => $storeId]
            );
    }
}
