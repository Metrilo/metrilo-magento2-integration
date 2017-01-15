<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface {

    protected $_orderCollection;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->_layout = $layout;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->_helper = $helper;
    }

    /**
     * Collect orders details
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        if(!$this->_orderCollection){
            $this->_orderCollection = $this->_salesOrderCollection->create();
            $this->_orderCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);
        }
        if (count($this->_orderCollection)) {
            foreach ($this->_orderCollection as $order) {
                $data = $this->_helper->prepareOrderDetails($order);
                if($order->getCustomerIsGuest()) {
                    $identify = array(
                        'id' => $order->getCustomerEmail(),
                        'params' => array(
                            'email'         => $order->getCustomerEmail(),
                            'name'          => $order->getCustomerFirstname(). ' '. $order->getCustomerLastname(),
                            'first_name'    => $order->getCustomerFirstname(),
                            'last_name'     => $order->getCustomerLastname(),
                        )
                    );
                    $this->_helper->addSessionEvent('identify', 'identify', $identify);
                }
                $this->_helper->addSessionEvent('track', 'order', $data);
            }
        }
    }
}
