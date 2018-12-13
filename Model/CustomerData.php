<?php

namespace Metrilo\Analytics\Model;

class CustomerData
{
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Newsletter\Model\Subscriber $subscriber
    ) {
        $this->customerCollection = $customerCollection;
        $this->subscriber         = $subscriber;
    }

    public function getCustomerQuery($storeId)
    {
        return $this->customerCollection->create()->addAttributeToFilter('store_id', $storeId);
    }

    public function getCustomers($storeId, $chunkId, $chunkItems)
    {
        $customersArray = [];
        $customers = $this->getCustomerQuery($storeId)
                          ->setPageSize($chunkItems)
                          ->setCurPage($chunkId + 1);

        foreach ($customers as $customer) {
            $subscriberStatus = $this->subscriber->loadByEmail($customer['email'])->isSubscribed();
            $this->subscriber->unsetData();
            $customersArray[] = [
                'email'       => $customer->getEmail(),
                'createdAt'   => strtotime($customer->getCreatedAt()),
                'firstName'   => $customer->getFirstname(),
                'lastName'    => $customer->getLastname(),
                'subscribed'  => $subscriberStatus
            ];
        }
        return $customersArray;
    }
}
