<?php

namespace Metrilo\Analytics\Helper;

class MetriloCustomer extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $storeId;
    private $email;
    private $createdAt;
    private $firstName;
    private $lastName;
    private $subscribed;
    private $tags;
    
    public function __construct(
        $storeId,
        $email,
        $createdAt,
        $firstName,
        $lastName,
        $subscribed,
        $tags
    ) {
        $this->storeId    = $storeId;
        $this->email      = $email;
        $this->createdAt  = $createdAt;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->subscribed = $subscribed;
        $this->tags       = $tags;
    }
    
    public function getStoreId()
    {
        return $this->storeId;
    }
    
    public function getEmail()
    {
        return $this->email;
    }
    
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    public function getFirstName()
    {
        return $this->firstName;
    }
    
    public function getLastName()
    {
        return $this->lastName;
    }
    
    public function getSubscriberStatus()
    {
        return $this->subscribed;
    }
    
    public function getTags()
    {
        return $this->tags;
    }
}