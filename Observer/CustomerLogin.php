<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{

    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Metrilo\Analytics\Helper\ApiClient $apiClient
    ) {
        $this->helper    = $helper;
        $this->apiClient = $apiClient;
    }

    /**
     * Track customer login and trigger "identify" to Metrilo
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();
            $storeId  = $this->helper->getStoreId();
            
            if (empty($customer) || !$customer) {
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
