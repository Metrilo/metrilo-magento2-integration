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

    public function getCustomers($storeId)
    {
        $customersArray = [];
        $customers = $this->customerCollection->create()->addAttributeToFilter('store_id', $storeId);

        foreach ($customers as $customer) {
            $subscriberStatus = $this->subscriber->loadByEmail($customer['email'])->isSubscribed();
            $this->subscriber->unsetData();
            $customersArray[] = [
                'email'     => $customer->getEmail(),
                'createdAt' => strtotime($customer->getCreatedAt()),
                'firstName' => $customer->getFirstname(),
                'lastName'  => $customer->getLastname(),
                'subscribed'=> $subscriberStatus
            ];
        }
        return $customersArray;
    }
}
