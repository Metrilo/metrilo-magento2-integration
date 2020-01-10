<?php

namespace Metrilo\Analytics\Helper;

class CustomerSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function serialize($customer) {
        $serializedCustomer = [
            'email'       => $customer->getEmail(),
            'createdAt'   => $customer->getCreatedAt(),
            'firstName'   => $customer->getFirstName(),
            'lastName'    => $customer->getLastName(),
            'subscribed'  => $customer->getSubscriberStatus(),
            'tags'        => $customer->getTags()
        ];
        
        return $serializedCustomer;
    }
}
