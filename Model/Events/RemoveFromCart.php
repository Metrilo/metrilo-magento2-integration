<?php

namespace Metrilo\Analytics\Model\Events;

class RemoveFromCart
{
    public function __construct(
        $event
    ) {
        $this->event = $event;
    }
    public function callJS()
    {
        $item = $this->event->getQuoteItem();
        
        return "window.metrilo.removeFromCart('" .
            $item->getProductId() . "', " .
            (int)$item->getQty() . ");";
    }
}
