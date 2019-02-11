<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerUpdate implements ObserverInterface
{

    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper             = $helper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Track customer update profile information
     * and trigger "identify" to Metrilo
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $email   = $observer->getEvent()->getEmail();
            $storeId = $this->helper->getStoreId();
            
            if (empty($email) || !$email) {
                return;
            }
            
            $customer           = $this->customerRepository->get($email);
            $client             = $this->apiClient->getClient($storeId);
            $serializedCustomer = $this->helper->customerSerializer->serializeCustomer($customer);
            
            $client->customer($serializedCustomer);
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
