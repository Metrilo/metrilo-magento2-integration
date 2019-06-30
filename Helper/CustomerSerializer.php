<?php

namespace Metrilo\Analytics\Helper;

class CustomerSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Newsletter\Model\Subscriber           $subscriberModel,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->subscriberModel = $subscriberModel;
        $this->groupRepository = $groupRepository;
    }
    
    public function serialize($customer) {
        $this->subscriberModel->unsetData();
        $group       = $this->groupRepository->getById($customer->getGroupId());
        $groupName[] = $group->getCode();
        
        $serializedCustomer = [
            'email'       => $customer->getEmail(),
            'createdAt'   => strtotime($customer->getCreatedAt()) * 1000,
            'firstName'   => $customer->getFirstname(),
            'lastName'    => $customer->getLastname(),
            'subscribed'  => $this->subscriberModel->loadByCustomerId($customer->getId())->isSubscribed(),
            'tags'        => $groupName
        ];
        
        return $serializedCustomer;
    }
}
