<?php

namespace Metrilo\Analytics\Model;

class OrderData
{
    private $chunkItems = \Metrilo\Analytics\Helper\Data::CHUNK_ITEMS;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
    ) {
        $this->orderCollection = $orderCollection;
    }

    public function getOrders($storeId, $chunkId)
    {
        return $this->getOrderQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }

    public function getOrderChunks($storeId)
    {
        $totalOrders = $this->getOrderQuery($storeId)->getSize();
        return (int) ceil($totalOrders / $this->chunkItems);
    }

    private function getOrderQuery($storeId)
    {
        return $this->orderCollection->create()
            ->addAttributeToFilter('store_id', $storeId)
            ->addAttributeToSelect('*')
            ->setOrder('entity_id', 'asc');
    }
}
