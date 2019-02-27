<?php

namespace Metrilo\Analytics\Helper;

class CustomerSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Newsletter\Model\Subscriber $subscriberModel
    ) {
        $this->subscriberModel = $subscriberModel;
    }
    
    public function serialize($customer) {
        $this->subscriberModel->unsetData();
        
        $serializedCustomer = [
            'email'       => $customer->getEmail(),
            'createdAt'   => strtotime($customer->getCreatedAt()) * 1000,
            'firstName'   => $customer->getFirstname(),
            'lastName'    => $customer->getLastname(),
            'subscribed'  => $this->subscriberModel->loadByCustomerId($customer->getId())->isSubscribed()
        ];
        
        return $serializedCustomer;
    }
}
