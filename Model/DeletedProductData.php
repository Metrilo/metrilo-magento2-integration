<?php

namespace Metrilo\Analytics\Model;

use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;

class DeletedProductData
{
    private CollectionFactory $orderCollection;

    private Collection $orderItemCollection;

    public function __construct(
        CollectionFactory $orderCollection,
        Collection $orderItemCollection
    ) {
        $this->orderCollection = $orderCollection;
        $this->orderItemCollection = $orderItemCollection;
    }

    public function getDeletedProductOrders($storeId)
    {
        $deletedProductOrdersQuery = $this->orderItemCollection->getSelect();
        $deletedProductOrdersQuery->distinct(true)
                                  ->reset(Select::COLUMNS)
                                  ->columns(['order_id'])
                                  ->joinLeft(
                                      ['catalog' => 'catalog_product_entity'],
                                      'main_table.product_id = catalog.entity_id',
                                      []
                                  )
                                  ->where('catalog.entity_id IS NULL')
                                  ->where('main_table.store_id = ?', $storeId);

        $deletedProductOrderIds = $this->orderItemCollection->getConnection()->fetchAll($deletedProductOrdersQuery);

        return $this->orderCollection->create()->addFieldToFilter('entity_id', ['in' => $deletedProductOrderIds]);
    }
}
