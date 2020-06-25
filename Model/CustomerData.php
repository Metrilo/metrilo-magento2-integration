<?php

namespace Metrilo\Analytics\Model;

use Metrilo\Analytics\Helper\MetriloCustomer;

class CustomerData
{
    private $customerCollection;
    private $subscriberModel;
    private $groupRepository;
    
    private $chunkItems = \Metrilo\Analytics\Helper\Data::CHUNK_ITEMS;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Newsletter\Model\Subscriber                             $subscriberModel,
        \Magento\Customer\Api\GroupRepositoryInterface                   $groupRepository
    ) {
        $this->customerCollection = $customerCollection;
        $this->subscriberModel    = $subscriberModel;
        $this->groupRepository    = $groupRepository;
    }

    public function getCustomers($storeId, $chunkId)
    {
        $metriloCustomers = [];
        $customers  = $this->getCustomerQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
        
        foreach ($customers as $customer) {
            $metriloCustomers[] = new MetriloCustomer(
                $customer->getStoreId(),
                $customer->getEmail(),
                strtotime($customer->getCreatedAt()) * 1000,
                $customer->getData('firstname'),
                $customer->getData('lastname'),
                $this->getCustomerSubscriberStatus($customer->getId()),
                $this->getCustomerGroup($customer->getGroupId())
            );
        }
        
        return $metriloCustomers;
    }

    public function getCustomerChunks($storeId)
    {
        $totalCustomers = $this->getCustomerQuery($storeId)->getSize();
        return (int) ceil($totalCustomers / $this->chunkItems);
    }

    private function getCustomerQuery($storeId)
    {
        return $this->customerCollection->create()->addAttributeToFilter('store_id', $storeId);
    }

    private function getCustomerSubscriberStatus($customerId)
    {
        $this->subscriberModel->unsetData();
        return $this->subscriberModel->loadByCustomerId($customerId)->isSubscribed();
    }

    private function getCustomerGroup($groupId)
    {
        $group       = $this->groupRepository->getById($groupId);
        $groupName[] = $group->getCode();
        return $groupName;
    }
}
