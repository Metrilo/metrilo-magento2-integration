<?php

namespace Metrilo\Analytics\Model\Events;

class AddToCart
{
    public function __construct(
        $event
    ) {
        $this->event = $event;
    }
    public function callJS()
    {
        $item = $this->event->getQuoteItem();
        
        return "window.metrilo.addToCart('" .
            $item->getProductId() . "', " .
            (int)$item->getData('qty_to_add') . ");";
    }
}
