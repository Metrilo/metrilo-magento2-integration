<?php

namespace Metrilo\Analytics\Model;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Metrilo\Analytics\Helper\Data;

class CustomerData
{
    private CollectionFactory $customerCollection;

    private Subscriber $subscriberModel;

    private GroupRepositoryInterface $groupRepository;

    private $chunkItems = Data::CHUNK_ITEMS;

    private MetriloCustomerFactory $customerFactory;

    public function __construct(
        CollectionFactory $customerCollection,
        Subscriber $subscriberModel,
        GroupRepositoryInterface $groupRepository,
        MetriloCustomerFactory $customerFactory
    ) {
        $this->customerCollection = $customerCollection;
        $this->subscriberModel = $subscriberModel;
        $this->groupRepository = $groupRepository;
        $this->customerFactory = $customerFactory;
    }

    public function getCustomers($storeId, $chunkId)
    {
        $metriloCustomers = [];
        $customers = $this->getCustomerQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);

        foreach ($customers as $customer) {
            $metriloCustomers[] = $this->customerFactory->create([
                    'storeId' => $customer->getStoreId(),
                    'email' => $customer->getEmail(),
                    'createdAt' => strtotime($customer->getCreatedAt()) * 1000,
                    'firstname' => $customer->getData('firstname'),
                    'lastname' => $customer->getData('lastname'),
                    'subscribed' => $this->getCustomerSubscriberStatus($customer->getId()),
                    'tags' => $this->getCustomerGroup($customer->getGroupId())
                ]);
        }

        return $metriloCustomers;
    }

    public function getCustomerChunks($storeId)
    {
        $totalCustomers = $this->getCustomerQuery($storeId)->getSize();

        return (int)ceil($totalCustomers / $this->chunkItems);
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
        $group = $this->groupRepository->getById($groupId);
        $groupName[] = $group->getCode();

        return $groupName;
    }
}
