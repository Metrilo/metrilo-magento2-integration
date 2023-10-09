<?php

namespace Metrilo\Analytics\Model\Events;

use Magento\Framework\Event;

class AddToCart
{
    private Event $event;

    public function __construct(
        Event $event
    ) {
        $this->event = $event;
    }
    public function callJS(): string
    {
        $item = $this->event->getQuoteItem();

        return "window.metrilo.addToCart('" .
            $item->getProductId() . "', " .
            (int)$item->getData('qty_to_add') . ");";
    }
}
