<?php

namespace Metrilo\Analytics\Model\Events;

use Magento\Framework\Event;

class RemoveFromCart
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

        return "window.metrilo.removeFromCart('" .
            $item->getProductId() . "', " .
            (int)$item->getQty() . ");";
    }
}
