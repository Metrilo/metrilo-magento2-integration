<?php

namespace Metrilo\Analytics\Model\Events;

class IdentifyUser
{
    public function __construct(
        $email
    ) {
        $this->email = $email;
    }
    public function callJS() {
        return 'window.metrilo.identify("' . $this->email . '");';
    }
}