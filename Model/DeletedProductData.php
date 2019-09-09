<?php

namespace Metrilo\Analytics\Model;

class DeletedProductData
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
    ) {
        $this->orderCollection = $orderCollection;
    }
    
    public function getDeletedProducts($storeId) {
        $query = "SELECT sales_order_item.item_id, sales_order_item.parent_item_id, sales_order_item.product_id,
                 sales_order_item.store_id, sales_order_item.product_type, sales_order_item.name,
                 sales_order_item.sku, sales_order_item.price, sales_order_item.order_id
          FROM `sales_order_item`
          LEFT OUTER JOIN `catalog_product_entity`
          ON sales_order_item.product_id = catalog_product_entity.entity_id
          WHERE catalog_product_entity.entity_id IS NULL
          AND sales_order_item.store_id = '$storeId'";
        
        return $this->orderCollection->create()->getConnection()->fetchAll($query);
    }
}
