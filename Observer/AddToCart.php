<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddToCart implements ObserverInterface
{

    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $data['quantity']  = $observer->getEvent()->getQuoteItem()->getQty();
            $data['productId'] = $observer->getEvent()->getProduct()->getId();
            
            $this->helper->addSessionEvent('metrilo_add_to_cart', $data);
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
