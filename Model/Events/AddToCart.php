<?php

namespace Metrilo\Analytics\Model\Events;

class AddToCart
{
    public function __construct(
        \Metrilo\Analytics\Helper\SessionEvents $sessionEvents
    ) {
        $this->sessionEvents = $sessionEvents;
    }
    public function callJS() {
        $eventCall = [];
        $events = $this->sessionEvents->getSessionEvents('metrilo_add_to_cart');
        foreach ($events as $event) {
            $eventCall[] = "window.metrilo.addToCart('" . $event['productId'] . "', " . $event['quantity'] . ");";
        }
        
        return $eventCall;
    }
}