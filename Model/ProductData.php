<?php

namespace Metrilo\Analytics\Model;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Metrilo\Analytics\Helper\Data;

class ProductData
{
    private $chunkItems = Data::CHUNK_ITEMS;

    private CollectionFactory $productCollection;

    public function __construct(
        CollectionFactory $productCollection
    ) {
        $this->productCollection = $productCollection;
    }

    public function getProducts($storeId, $chunkId)
    {
        return $this->getProductQuery($storeId)
                    ->setPageSize($this->chunkItems)
                    ->setCurPage($chunkId + 1)
                    ->setDataToAll('store_id', $storeId);
    }

    public function getProductChunks($storeId)
    {
        $totalProducts = $this->getProductQuery($storeId)->getSize();
        return (int) ceil($totalProducts / $this->chunkItems);
    }

    public function getProductWithRequestPath($productId, $storeId)
    {
        $productObject = $this->productCollection
            ->create()
            ->addStoreFilter($storeId)
            ->addAttributeToSelect(['name','price','image', 'special_price'])
            ->joinTable(
                ['url' => 'url_rewrite'],
                'entity_id = entity_id',
                ['request_path', 'store_id', 'metadata'],
                ['entity_id' => $productId,
                    'entity_type' => 'product',
                    'store_id' => $storeId,
                    'metadata' => ['null' => true]]
            )
            ->getFirstItem();

        $productObject->setStoreId($storeId);

        return $productObject;
    }

    private function getProductQuery($storeId)
    {
        return $this->productCollection
            ->create()
            ->addUrlRewrite()
            ->addAttributeToSelect([
                'entity_id',
                'type_id',
                'sku',
                'created_at',
                'updated_at',
                'name',
                'image',
                'price',
                'special_price',
                'request_path',
                'visibility'
            ])
            ->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH)
            ->addStoreFilter($storeId);
    }
}
