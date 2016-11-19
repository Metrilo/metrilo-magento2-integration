<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface {

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->_layout = $layout;
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
        $block = $this->_layout->getBlock('metrilo_analytics');
        \Zend_Debug::dump($orderIds);
        \Zend_Debug::dump($block);
        exit;
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }

}
