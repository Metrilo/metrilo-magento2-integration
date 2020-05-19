<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\MetriloCustomer;

class Customer implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Metrilo\Analytics\Helper\ApiClient               $apiClient
     * @param \Metrilo\Analytics\Helper\CustomerSerializer      $customerSerializer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Newsletter\Model\Subscriber              $subscriberModel
     * @param \Magento\Customer\Api\GroupRepositoryInterface    $groupRepository
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data                    $helper,
        \Metrilo\Analytics\Helper\ApiClient               $apiClient,
        \Metrilo\Analytics\Helper\CustomerSerializer      $customerSerializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Newsletter\Model\Subscriber              $subscriberModel,
        \Magento\Customer\Api\GroupRepositoryInterface    $groupRepository
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->customerSerializer = $customerSerializer;
        $this->customerRepository = $customerRepository;
        $this->subscriberModel    = $subscriberModel;
        $this->groupRepository    = $groupRepository;
    }
    
    private function getCustomerFromEvent($observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'customer_save_after':
                $customer = $observer->getEvent()->getCustomer();
                if ($this->hasCustomerChanged($customer)) {
                    return new MetriloCustomer(
                        $customer->getStoreId(),
                        $customer->getEmail(),
                        strtotime($customer->getCreatedAt()) * 1000,
                        $customer->getData('firstname'),
                        $customer->getData('lastname'),
                        $this->getCustomerSubscriberStatus($customer->getId()),
                        $this->getCustomerGroup($customer->getGroupId())
                    );
                }
                
                break;
            case 'newsletter_subscriber_save_after':
                $subscriber = $observer->getEvent()->getSubscriber();
                $customerId = $subscriber->getCustomerId();
                if ($subscriber->isStatusChanged() && $customerId !== 0) {
                    return $this->metriloCustomer($this->customerRepository->getById($customerId));
                } else {
                    $subscriberEmail = $subscriber->getEmail();
                    return new MetriloCustomer(
                        $subscriber->getStoreId(),
                        $subscriberEmail,
                        strtotime($subscriber->getData('change_status_at')) * 1000,
                        $subscriberEmail,
                        $subscriberEmail,
                        true,
                        ['guest_customer']
                    );
                }
                
                break;
            case 'customer_account_edited':
                return $this->metriloCustomer($this->customerRepository->get($observer->getEvent()->getEmail()));
                
                break;
            case 'customer_register_success':
                return $this->metriloCustomer($observer->getEvent()->getCustomer());
                
                break;
            case 'sales_order_save_after':
                return new MetriloCustomer(
                    $observer->getEvent()->getOrder()->getStoreId(),
                    $observer->getEvent()->getOrder()->getCustomerEmail(),
                    strtotime($observer->getEvent()->getOrder()->getCreatedAt()) * 1000,
                    $observer->getEvent()->getOrder()->getBillingAddress()->getData('firstname'),
                    $observer->getEvent()->getOrder()->getBillingAddress()->getData('lastname'),
                    true,
                    ['guest_customer']
                );
                
                break;
            default:
                break;
        }
        
        return false;
    }
    
    private function hasCustomerChanged($customer)
    {
        $originalCustomer = $this->customerRepository->getById($customer->getId());
        
        if ($originalCustomer->getCreatedAt() === $originalCustomer->getUpdatedAt()) {
            return true; // if customer is created in admin there are no differences in $customer and $originalCustomer
        }
        
        return $customer->getEmail() != $originalCustomer->getEmail() ||
                $customer->getFirstname() != $originalCustomer->getFirstname() ||
                $customer->getLastname() != $originalCustomer->getLastname() ||
                $customer->getGroupId() != $originalCustomer->getGroupId();
    }
    
    private function getCustomerSubscriberStatus($customerId)
    {
        $this->subscriberModel->unsetData();
        return $this->subscriberModel->loadByCustomerId($customerId)->isSubscribed();
    }
    
    private function getCustomerGroup($groupId)
    {
        $group       = $this->groupRepository->getById($groupId);
        $groupName[] = $group->getCode();
        return $groupName;
    }
    
    private function metriloCustomer($customer)
    {
        return new MetriloCustomer(
            $customer->getStoreId(),
            $customer->getEmail(),
            strtotime($customer->getCreatedAt()) * 1000,
            $customer->getFirstName(),
            $customer->getLastName(),
            $this->getCustomerSubscriberStatus($customer->getId()),
            $this->getCustomerGroup($customer->getGroupId())
        );
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
