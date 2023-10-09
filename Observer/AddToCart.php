<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Model\Events\AddToCart as AddToCartEvent;

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
            if (!$this->helper->isEnabled($observer->getEvent()->getProduct()->getStoreId())) {
                return;
            }
            $addToCartEvent = new AddToCartEvent($observer->getEvent());
            $this->sessionEvents->addSessionEvent($addToCartEvent->callJs());
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
