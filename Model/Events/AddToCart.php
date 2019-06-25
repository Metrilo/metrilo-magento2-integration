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
        return "window.metrilo.addToCart('" . $this->event->getProduct()->getId() . "', " . $this->event->getQuoteItem()->getQty() . ");";
    }
}
