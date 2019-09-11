<?php

namespace Metrilo\Analytics\Model;

class DeletedProductData
{
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\ResourceModel\Order\Item\Collection   $orderItemCollection
    ) {
        $this->orderCollection     = $orderCollection;
        $this->orderItemCollection = $orderItemCollection;
    }
    
    public function getDeletedProductOrders($storeId)
    {
        $deletedProductOrdersQuery = $this->orderItemCollection->getSelect()
            ->distinct()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['order_id'])
            ->joinLeft(array('catalog' => 'catalog_product_entity'), 'main_table.product_id = catalog.entity_id', array())
            ->where('catalog.entity_id IS NULL')
            ->where('main_table.store_id = ?', $storeId);
        
        $deletedProductOrderIds = $this->orderItemCollection->getConnection()->fetchAll($deletedProductOrdersQuery);
        
        return $this->orderCollection->create()->addFieldToFilter('entity_id', ['in' => $deletedProductOrderIds]);
    }
}
