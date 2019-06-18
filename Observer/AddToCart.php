<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddToCart implements ObserverInterface
{

    public function __construct(
        \Metrilo\Analytics\Helper\Data          $helper,
        \Metrilo\Analytics\Helper\SessionEvents $sessionEvents
    ) {
        $this->helper        = $helper;
        $this->sessionEvents = $sessionEvents;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $data['quantity']  = $observer->getEvent()->getQuoteItem()->getQty();
            $data['productId'] = $observer->getEvent()->getProduct()->getId();
            
            $this->sessionEvents->addSessionEvent('metrilo_add_to_cart', $data);
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
