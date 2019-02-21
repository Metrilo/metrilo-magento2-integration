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
            $client     = $this->apiClient->getClient($storeId);
            $eventName  = $observer->getEvent()->getName();
            $hasChanges = true;
            
            switch ($eventName) {
                case 'customer_save_after':
                    $customer     = $observer->getEvent()->getCustomer();
                    $origCustomer = $this->customerRepository->getById($customer->getId());
                    $email        = $customer->getEmail();
                    
                    // Create api call only if there is difference between original and saved after customer data.
                    if ($customer->getEmail() == $origCustomer->getEmail() &&
                        $customer->getFirstname() == $origCustomer->getFirstname() &&
                        $customer->getLastname() == $origCustomer->getLastname()) {
                        $hasChanges = false;
                    } else {
                        $hasChanges = true;
                    }
                    break;
                case 'newsletter_subscriber_save_after':
                    $subscriber = $observer->getEvent()->getSubscriber();
                    $customer   = $this->customerRepository->getById($subscriber->getCustomerId());
                    $email      = $subscriber->getEmail();
                    $hasChanges = $subscriber->isStatusChanged();
                    break;
                case 'customer_account_edited':
                    $email    = $observer->getEvent()->getEmail();
                    $customer = $this->customerRepository->get($email);
                    break;
                case 'customer_register_success':
                    $customer = $observer->getEvent()->getCustomer();
                    $email    = $customer->getEmail();
                    break;
                default:
                    break;
            }
            
            if (!trim($email)) {
                $this->helper->logError('Customer with id = '. $customer->getId(). '  have no email address!');
                return;
            }
            
            if ($hasChanges) {
                $serializedCustomer = $this->customerSerializer->serializeCustomer($customer);
                $client->customer($serializedCustomer);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
