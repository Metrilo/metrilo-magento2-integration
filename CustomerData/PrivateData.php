<?php

namespace Metrilo\Analytics\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Metrilo\Analytics\Helper\SessionEvents;

class PrivateData implements SectionSourceInterface
{
    private SessionEvents $sessionEvents;

    public function __construct(
        SessionEvents $sessionEvents
    ) {
        $this->sessionEvents = $sessionEvents;
    }

    public function getSectionData(): array
    {
        $events = $this->sessionEvents->getSessionEvents();
        return ['events' => $events];
    }
}
