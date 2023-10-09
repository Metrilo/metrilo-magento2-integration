<?php

namespace Metrilo\Analytics\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class PrivateData implements SectionSourceInterface
{
    public function __construct(
        \Metrilo\Analytics\Helper\SessionEvents $sessionEvents
    ) {
        $this->sessionEvents = $sessionEvents;
    }
    
    public function getSectionData()
    {
        $events = $this->sessionEvents->getSessionEvents();
        return ['events' => $events];
    }
}
