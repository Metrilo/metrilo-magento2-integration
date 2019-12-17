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
    
    private function getCustomerFromEvent($observer) {
        switch ($observer->getEvent()->getName()) {
            case 'customer_save_after':
                $customer = $observer->getEvent()->getCustomer();
                if ($this->hasCustomerChanged($customer)) {
                    return $customer;
                }
                
                break;
            case 'newsletter_subscriber_save_after':
                $subscriber = $observer->getEvent()->getSubscriber();
                if ($subscriber->isStatusChanged()) {
                    return $this->customerRepository->getById($subscriber->getCustomerId());
                }
                
                break;
            case 'customer_account_edited':
                return $this->customerRepository->get($observer->getEvent()->getEmail());
                
                break;
            case 'customer_register_success':
                return $observer->getEvent()->getCustomer();
                
                break;
            default:
                break;
        }
        
        return false;
    }
    
    private function hasCustomerChanged($customer) {
        $originalCustomer = $this->customerRepository->getById($customer->getId());
        
        return $customer->getEmail() != $originalCustomer->getEmail() ||
                $customer->getFirstname() != $originalCustomer->getFirstname() ||
                $customer->getLastname() != $originalCustomer->getLastname() ||
                $customer->getGroupId() != $originalCustomer->getGroupId();
    }
    
    
    public function execute(Observer $observer)
    {
        try {
            $customer = $this->getCustomerFromEvent($observer);
            if ($customer && $this->helper->isEnabled($customer->getStoreId())) {
                if (!trim($customer->getEmail())) {
                    $this->helper->logError('Customer with id = '. $customer->getId(). '  has no email address!');
                    return;
                }
                
                $client             = $this->apiClient->getClient($customer->getStoreId());
                $serializedCustomer = $this->customerSerializer->serialize($customer);
                $client->customer($serializedCustomer);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
