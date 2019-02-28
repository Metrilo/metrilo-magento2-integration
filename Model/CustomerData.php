<?php

namespace Metrilo\Analytics\Model;

class CustomerData
{
    public $chunkItems = \Metrilo\Analytics\Helper\Data::chunkItems;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection
    ) {
        $this->customerCollection = $customerCollection;
    }

    public function getCustomerQuery($storeId)
    {
        return $this->customerCollection->create()->addAttributeToFilter('store_id', $storeId);
    }

    public function getCustomers($storeId, $chunkId)
    {
        return $this->getCustomerQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }

    public function getCustomerChunks($storeId)
    {
        $totalCustomers = $this->getCustomerQuery($storeId)->getSize();
        return (int) ceil($totalCustomers / $this->chunkItems);
    }
}
