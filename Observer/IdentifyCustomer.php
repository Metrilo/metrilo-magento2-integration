<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Model\Events\IdentifyCustomer as IdentifyCustomerEvent;

class IdentifyCustomer implements ObserverInterface
{
    public function __construct(
        \Metrilo\Analytics\Helper\Data          $helper,
        \Metrilo\Analytics\Helper\SessionEvents $sessionEvents
    ) {
        $this->helper        = $helper;
        $this->sessionEvents = $sessionEvents;
    }
    
    private function getEventEmail($observer) {
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
    
    public function execute(Observer $observer)
    {
        try {
            $identifyEmail = $this->getEventEmail($observer);
            
            if ($identifyEmail && $this->helper->isEnabled($observer->getEvent()->getStoreId())) {
                $identifyCustomerEvent = new IdentifyCustomerEvent($identifyEmail);
                $this->sessionEvents->addSessionEvent($identifyCustomerEvent->callJs());
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
