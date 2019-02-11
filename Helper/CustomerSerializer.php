<?php
    
    namespace Metrilo\Analytics\Helper;
    
    class CustomerSerializer extends \Magento\Framework\App\Helper\AbstractHelper
    {
        public function __construct(
            \Magento\Newsletter\Model\Subscriber $subscriber
        ) {
            $this->subscriber = $subscriber;
        }
        
        public function serializeCustomer($customer) {
            
            $subscriberStatus = $this->subscriber->loadByEmail($customer['email'])->isSubscribed();
            $this->subscriber->unsetData();
            $serializedCustomer = [
                'email'       => $customer->getEmail(),
                'createdAt'   => strtotime($customer->getCreatedAt()) * 1000,
                'firstName'   => $customer->getFirstname(),
                'lastName'    => $customer->getLastname(),
                'subscribed'  => $subscriberStatus
            ];
            
            return $serializedCustomer;
        }
    }
