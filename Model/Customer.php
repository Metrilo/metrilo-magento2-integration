<?php
/**
 * @author Nedelin Slavov <ned@metrilo.com>
 */

namespace Metrilo\Analytics\Model;

/**
 * Model getting orders by chunks for Metrilo import
 *
 * @author Nedelin Slavov <ned@metrilo.com>
 */
class Customer
{
    private $ordersTotal    = 0;
    private $totalChunks    = 0;
    private $chunkItems     = 15;
    private $customersArray = [];

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Newsletter\Model\Subscriber $subscriber
    ) {
        $this->customerCollection = $customerCollection;
        $this->subscriber         = $subscriber;
    }

    /**
     * Get chunk customer data for import
     *
     * @param  int
     * @return mixed
     */
    public function getCustomers($storeId, $chunkId)
    {
        $customers = $this->getCustomerQuery();

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

    /**
     * Get customer collection
     *
     * @param int $storeId
     *
     * @return mixed
     */
    protected function getCustomerQuery($storeId = 0)
    {
        return $this->customerCollection->create();
    }

}
