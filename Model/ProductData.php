<?php

namespace Metrilo\Analytics\Model;

class ProductData
{
    public $chunkItems = \Metrilo\Analytics\Helper\Data::chunkItems;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
    ) {
        $this->productCollection = $productCollection;
    }

    public function getProductQuery($storeId)
    {
        return $this->productCollection
                    ->create()
                    ->addAttributeToSelect('*')
                    ->addUrlRewrite()
                    ->addStoreFilter($storeId);
    }

    public function getProducts($storeId, $chunkId)
    {
        return $this->getProductQuery($storeId)
                    ->setPageSize($this->chunkItems)
                    ->setCurPage($chunkId + 1);
    }
    
    public function getProductChunks($storeId)
    {
        $totalProducts = $this->getProductQuery($storeId)->getSize();
        return (int) ceil($totalProducts / $this->chunkItems);
    }
}
