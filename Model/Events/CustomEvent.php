<?php

namespace Metrilo\Analytics\Model\Events;

class CustomEvent
{
    public function __construct(
        $customEvent
    ) {
        $this->customEvent = $customEvent;
    }
    public function callJS()
    {
        return 'window.metrilo.customEvent("' . $this->customEvent . '");';
    }
}