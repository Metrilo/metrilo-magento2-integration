<?php

namespace Metrilo\Analytics\Model\Events;

class CheckoutView
{
    public function callJS(): string
    {
        return "window.metrilo.checkout();";
    }
}
