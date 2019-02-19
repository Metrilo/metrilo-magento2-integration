<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerCreate implements ObserverInterface
{
    
    /**
     * @param \Metrilo\Analytics\Helper\Data      $helper
     * @param \Metrilo\Analytics\Helper\ApiClient $apiClient
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Helper\ApiClient $apiClient
    ) {
        $this->helper    = $helper;
        $this->apiClient = $apiClient;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();
            $storeId  = $this->helper->getStoreId();
            
            if (!trim($customer->getEmail())) {
                $this->helper->logError('Customer with id = '. $customer->getId(). '  have no email address!');
                return;
            }
            
            $client             = $this->apiClient->getClient($storeId);
            $serializedCustomer = $this->helper->customerSerializer->serializeCustomer($customer);
            $client->customer($serializedCustomer);
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
