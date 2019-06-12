<?php

namespace Metrilo\Analytics\Model\Events;

class CartView
{
    public function callJS() {
        return "window.metrilo.customEvent('view_cart');";
    }
}