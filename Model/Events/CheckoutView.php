<?php

namespace Metrilo\Analytics\Model\Events;

class CheckoutView
{
    public function callJS() {
        return "window.metrilo.checkout();";
    }
}