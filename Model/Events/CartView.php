<?php

namespace Metrilo\Analytics\Model\Events;

class CartView
{
    public function callJS(): string
    {
        return "window.metrilo.customEvent('view_cart');";
    }
}
