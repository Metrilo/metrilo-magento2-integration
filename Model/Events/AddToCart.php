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
        return "window.metrilo.addToCart('" . $this->event->getQuoteItem()->getChildren()[0]->getProductId() . "', " . $this->event->getQuoteItem()->getData('qty_to_add') . ");";
    }
}
