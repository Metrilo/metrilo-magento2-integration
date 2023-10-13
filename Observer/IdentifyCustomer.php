<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\IdentifyCustomerFactory;

class IdentifyCustomer implements ObserverInterface
{
    private Data $helper;

    private SessionEvents $sessionEvents;

    private IdentifyCustomerFactory $identifyCustomerFactory;

    public function __construct(
        Data $helper,
        SessionEvents $sessionEvents,
        IdentifyCustomerFactory $identifyCustomerFactory
    ) {
        $this->helper = $helper;
        $this->sessionEvents = $sessionEvents;
        $this->identifyCustomerFactory = $identifyCustomerFactory;
    }

    private function getEventEmail($observer)
    {
        switch ($observer->getEvent()->getName()) {
            // identify on customer login action
            case 'customer_login':
                return $observer->getEvent()->getCustomer()->getEmail();
            // identify on customer account edit action
            case 'customer_account_edited':
                return $observer->getEvent()->getEmail();
            // identify on customer place order action
            case 'sales_order_save_after':
                return $observer->getEvent()->getOrder()->getCustomerEmail();
            default:
                break;
        }

        return false;
    }

    public function execute(Observer $observer): void
    {
        try {
            $identifyEmail = $this->getEventEmail($observer);

            if ($identifyEmail && $this->helper->isEnabled($observer->getEvent()->getStoreId())) {
                $identifyCustomerEvent = $this->identifyCustomerFactory->create(['email' => $identifyEmail]);
                $this->sessionEvents->addSessionEvent($identifyCustomerEvent->callJs());
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
