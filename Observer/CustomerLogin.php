<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface {

    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Track customer login and trigger "identify" to Metrilo
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        try {
            $customer = $observer->getEvent()->getCustomer();
            if (empty($customer) || !$customer) {
                return;
            }

            $data = [
                'id' => $customer->getEmail(),
                'params' => [
                    'email'         => $customer->getEmail(),
                    'name'          => $customer->getName(),
                    'first_name'    => $customer->getFirstname(),
                    'last_name'     => $customer->getLastname(),
                ]
            ];
            $this->helper->addSessionEvent('identify', 'identify', $data);
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }
}