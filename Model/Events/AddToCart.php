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
        $item = $this->event->getQuoteItem();
        $itemOptions = $item->getChildren();
        
        if ($itemOptions) {
            $itemSku = $itemOptions[0]->getSku();
            
            if ($itemSku) {
                return $itemSku;
            } else {
                return $itemOptions[0]->getProductId();
            }
        }
        
        return $item->getProductId();
    }
}
