<?php

namespace Metrilo\Analytics\Model;

class OrderData
{
    public $chunkItems = \Metrilo\Analytics\Helper\Data::chunkItems;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
    ) {
        $this->orderCollection = $orderCollection;
    }

    public function getOrders($storeId, $chunkId)
    {
        return $this->getOrderQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }

    public function getOrderQuery($storeId)
    {
        
        return $this->orderCollection->create()
            ->addAttributeToFilter('store_id', $storeId)
            ->addAttributeToFilter('customer_email', array('neq' => '')) //only return orders with email
            ->addAttributeToSelect('*')
            ->setOrder('entity_id', 'asc');
    }

    public function getOrderChunks($storeId)
    {
        $totalOrders = $this->getOrderQuery($storeId)->getSize();
        return (int) ceil($totalOrders / $this->chunkItems);
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
