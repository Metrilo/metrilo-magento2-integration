<?php

namespace Metrilo\Analytics\Helper;

class CustomerSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Newsletter\Model\Subscriber $subscriberModel
    ) {
        $this->subscriberModel = $subscriberModel;
    }
    
    public function serializeCustomer($customer) {
        $this->subscriberModel->unsetData();
        $subscriberStatus   = $this->subscriberModel->loadByEmail($customer->getEmail())->isSubscribed();
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
