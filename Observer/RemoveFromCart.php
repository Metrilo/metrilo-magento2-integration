<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\RemoveFromCartFactory;

class RemoveFromCart implements ObserverInterface
{
    private Data $helper;

    private SessionEvents $sessionEvents;

    private RemoveFromCartFactory $removeFromCartFactory;

    public function __construct(
        Data $helper,
        SessionEvents $sessionEvents,
        RemoveFromCartFactory $removeFromCartFactory
    ) {
        $this->helper = $helper;
        $this->sessionEvents = $sessionEvents;
        $this->removeFromCartFactory = $removeFromCartFactory;
    }

    public function execute(Observer $observer)
    {
        try {
            if (!$this->helper->isEnabled($observer->getEvent()->getQuoteItem()->getStoreId())) {
                return;
            }
            $removeFromCartEvent = $this->removeFromCartFactory->create(['event' => $observer->getEvent()]);
            $this->sessionEvents->addSessionEvent($removeFromCartEvent->callJs());
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
