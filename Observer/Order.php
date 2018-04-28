<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface
{

    private $_helper;

    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Trigger on save Order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_helper->callBatchApi($order->getStoreId(), [$order]);

        // If order is made from the FrontEnd
        if ($order->getRemoteIp()) {
            $identify = array(
                'id' => $order->getCustomerEmail(),
                'params' => array(
                    'email'      => $order->getCustomerEmail(),
                    'first_name' => $order->getBillingAddress()->getFirstname(),
                    'last_name'  => $order->getBillingAddress()->getLastname(),
                    'name'       => $order->getBillingAddress()->getName()
                )
            );
            $this->_helper->addSessionEvent('identify', 'identify', $identify);
        }
    }
}
