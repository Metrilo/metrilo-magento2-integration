<?php

namespace Metrilo\Analytics\Model\Events;

class IdentifyCustomer
{
    private string $email;

    public function __construct(
        string $email
    ) {
        $this->email = $email;
    }
    public function callJS(): string
    {
        return 'window.metrilo.identify("' . $this->email . '");';
    }
}
