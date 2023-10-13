<?php

namespace Metrilo\Analytics\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\AddToCartFactory;

class AddToCart implements ObserverInterface
{

    private Data $helper;

    private SessionEvents $sessionEvents;

    private AddToCartFactory $addToCartFactory;

    public function __construct(
        Data $helper,
        SessionEvents $sessionEvents,
        AddToCartFactory $addToCartFactory
    ) {
        $this->helper = $helper;
        $this->sessionEvents = $sessionEvents;
        $this->addToCartFactory = $addToCartFactory;
    }

    public function execute(Observer $observer): void
    {
        try {
            if (!$this->helper->isEnabled($observer->getEvent()->getProduct()->getStoreId())) {
                return;
            }
            $addToCartEvent = $this->addToCartFactory->create(['event' => $observer->getEvent()]);
            $this->sessionEvents->addSessionEvent($addToCartEvent->callJs());
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }
}
