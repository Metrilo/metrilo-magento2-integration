<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class CustomerSerializer extends AbstractHelper
{
    public function serialize($customer)
    {
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
