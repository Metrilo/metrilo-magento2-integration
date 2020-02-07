<?php

namespace Metrilo\Analytics\Model\Events;

class AddToCart
{
    public function __construct(
        $event
    ) {
        $this->event = $event;
    }
    public function callJS() {
        return "window.metrilo.addToCart('" . $this->getItemIdentifier() . "', " . $this->event->getQuoteItem()->getData('qty_to_add') . ");";
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
