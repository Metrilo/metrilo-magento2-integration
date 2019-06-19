<?php

namespace Metrilo\Analytics\Helper;

class SessionEvents extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $metriloSessionEvents = Data::METRILO_SESSION_EVENTS;
    
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
    }
    
    public function getSessionEvents()
    {
        $sessionEvents = $this->catalogSession->getData($this->metriloSessionEvents, true);
        
        if ($sessionEvents === null) {
            $sessionEvents = [];
        }
        
        return $sessionEvents;
    }
    
    public function addSessionEvent($data)
    {
        $events = [];
        $sessionData = $this->catalogSession->getData($this->metriloSessionEvents, true);
        if ($sessionData !== null) {
            $events = $sessionData;
        }
        
        $events[] = $data;
        $this->catalogSession->setData($this->metriloSessionEvents, $events);
    }
}