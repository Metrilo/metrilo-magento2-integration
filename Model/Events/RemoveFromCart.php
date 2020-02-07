<?php

namespace Metrilo\Analytics\Model\Events;

class RemoveFromCart
{
    public function __construct(
        $event
    ) {
        $this->event = $event;
    }
    public function callJS() {
        return "window.metrilo.removeFromCart('" . $this->getItemIdentifier() . "', " . $this->event->getQuoteItem()->getQty() . ");";
    }
    
    private function getItemIdentifier() {
        $itemId = '';
        $itemHaveOptions = $this->event->getQuoteItem()->getChildren();
        
        if ($itemHaveOptions) {
            $itemSku = $this->event->getQuoteItem()->getChildren()[0]->getSku();
            if ($itemSku) {
                $itemId = $itemSku;
            } else {
                $itemId = $this->event->getQuoteItem()->getChildren()[0]->getProductId();
            }
        } else {
            $itemId = $this->event->getQuoteItem()->getProductId();
        }
        
        return $itemId;
    }
}
