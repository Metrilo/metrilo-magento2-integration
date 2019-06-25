<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Model\Events\RemoveFromCart as RemoveFromCartEvent;

class RemoveFromCart implements ObserverInterface
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
            $removeFromCartEvent = new RemoveFromCartEvent($observer->getEvent());
            $this->sessionEvents->addSessionEvent($removeFromCartEvent->callJs());
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
