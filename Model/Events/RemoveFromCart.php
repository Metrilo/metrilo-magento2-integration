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
        return "window.metrilo.removeFromCart('" . $this->event->getQuoteItem()->getProductId() . "', " . $this->event->getQuoteItem()->getQty() . ");";
    }
}
