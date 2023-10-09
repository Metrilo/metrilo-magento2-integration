<?php

namespace Metrilo\Analytics\Model\Events;

class CustomEvent
{
    private string $customEvent;

    public function __construct(
        string $customEvent
    ) {
        $this->customEvent = $customEvent;
    }
    public function callJS(): string
    {
        return 'window.metrilo.customEvent("' . $this->customEvent . '");';
    }
}
