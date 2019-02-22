<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Customer implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Metrilo\Analytics\Helper\ApiClient               $apiClient
     * @param \Metrilo\Analytics\Helper\CustomerSerializer      $customerSerializer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data                    $helper,
        \Metrilo\Analytics\Helper\ApiClient               $apiClient,
        \Metrilo\Analytics\Helper\CustomerSerializer      $customerSerializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->customerSerializer = $customerSerializer;
        $this->customerRepository = $customerRepository;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $storeId    = $this->helper->getStoreId();
            $eventName  = $observer->getEvent()->getName();
            $hasChanges = true;
            
            switch ($eventName) {
                case 'customer_save_after':
                    $customer         = $observer->getEvent()->getCustomer();
                    $originalCustomer = $this->customerRepository->getById($customer->getId());
                    
                    // Create api call only if there is difference between original and saved after customer data.
                    if ($customer->getEmail() == $originalCustomer->getEmail() &&
                        $customer->getFirstname() == $originalCustomer->getFirstname() &&
                        $customer->getLastname() == $originalCustomer->getLastname()) {
                        $hasChanges = false;
                    } else {
                        $hasChanges = true;
                    }
                    break;
                case 'newsletter_subscriber_save_after':
                    $subscriber = $observer->getEvent()->getSubscriber();
                    $customer   = $this->customerRepository->getById($subscriber->getCustomerId());
                    $hasChanges = $subscriber->isStatusChanged();
                    break;
                case 'customer_account_edited':
                    $customer = $this->customerRepository->get($observer->getEvent()->getEmail());
                    break;
                case 'customer_register_success':
                    $customer = $observer->getEvent()->getCustomer();
                    break;
                default:
                    break;
            }
            
            if (!trim($customer->getEmail())) {
                $this->helper->logError('Customer with id = '. $customer->getId(). '  has no email address!');
                return;
            }
            
            if ($hasChanges) {
                $client             = $this->apiClient->getClient($storeId);
                $serializedCustomer = $this->customerSerializer->serialize($customer);
                $client->customer($serializedCustomer);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
