<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveFromCart implements ObserverInterface {

    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Track remove quote item
     * and send to Metrilo
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        try {
            $item = $observer->getEvent()->getQuoteItem();
            $product = $item->getProduct();

            $this->helper->addSessionEvent('track', 'remove_from_cart', ['id' => $product->getId()]);
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }
}