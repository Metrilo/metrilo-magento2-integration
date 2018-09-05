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
            $customer = $customer->toArray();
            $subscriberStatus = $this->subscriber->loadByCustomerId($customer['entity_id']);

            $customersArray[$customer['entity_id']] = [
                'email'     => $customer['email'],
                'createdAt' => strtotime($customer['created_at']),
                'updatedAt' => strtotime($customer['updated_at']),
                'firstName' => $customer['firstname'],
                'lastName'  => $customer['lastname'],
                'subscribed'=> $subscriberStatus->isSubscribed()
            ];
        }
        return $customersArray;
    }

}
