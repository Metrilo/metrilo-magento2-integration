<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface
{

    private $helper;

    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Trigger on save Order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $storeId = $order->getStoreId();

            if (!$this->helper->isEnabled($storeId)) {
                return;
            }

            $this->helper->callBatchApi($storeId, [$order]);

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
                $this->helper->addSessionEvent('identify', 'identify', $identify);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
