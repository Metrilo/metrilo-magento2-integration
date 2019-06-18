<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Model\Events\CartAdd;

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
            $addToCartEvent = new CartAdd($observer->getEvent());
            $this->sessionEvents->addSessionEvent($this->helper::ADD_TO_CART, $addToCartEvent->callJs());
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
