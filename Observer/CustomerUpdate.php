<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerUpdate implements ObserverInterface
{
    
    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Metrilo\Analytics\Helper\ApiClient               $apiClient
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Helper\ApiClient $apiClient,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->customerRepository = $customerRepository;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $email    = $observer->getEvent()->getEmail();
            $customer = $this->customerRepository->get($email);
            $storeId  = $this->helper->getStoreId();
            
            if (!trim($email)) {
                $this->helper->logError('CustomerUpdate without email detected!');
                return;
            }
            
            $client             = $this->apiClient->getClient($storeId);
            $serializedCustomer = $this->helper->customerSerializer->serializeCustomer($customer);
            $client->customer($serializedCustomer);
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
