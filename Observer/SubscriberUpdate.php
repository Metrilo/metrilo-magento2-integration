<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubscriberUpdate implements ObserverInterface
{
    
    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
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
            $subscriber = $observer->getEvent()->getSubscriber();
            $customer   = $this->customerRepository->get($subscriber->getSubscriberEmail());
            $storeId    = $this->helper->getStoreId();
            
            if (!trim($subscriber->getEmail())) {
                $this->helper->logError('SubscriberUpdate without email detected!');
                return;
            }
            
            if($subscriber->isStatusChanged()) {
                $client               = $this->apiClient->getClient($storeId);
                $serializedSubscriber = $this->helper->customerSerializer->serializeCustomer($customer);
                $client->customer($serializedSubscriber);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}

