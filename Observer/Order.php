<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface
{
    
    private $helper;
    
    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data            $helper,
        \Metrilo\Analytics\Helper\ApiClient       $apiClient,
        \Metrilo\Analytics\Helper\OrderSerializer $orderSerializer
    ) {
        $this->helper          = $helper;
        $this->apiClient       = $apiClient;
        $this->orderSerializer = $orderSerializer;
    }
    
    /**
     * Trigger on save Order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $storeId = $order->getStoreId();
            
            if (!$this->helper->isEnabled($storeId)) {
                return;
            }
            
            $client          = $this->apiClient->getClient($storeId);
            $serializedOrder = $this->orderSerializer->serialize($order);
            
            if ($serializedOrder) {
                $client->order($serializedOrder);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
