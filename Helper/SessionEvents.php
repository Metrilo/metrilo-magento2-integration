<?php

namespace Metrilo\Analytics\Helper;

class SessionEvents extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $metriloSessionEvents = 'metrilo_session_key';
    
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
        $events   = $this->getSessionEvents();
        $events[] = $data;
        $this->catalogSession->setData($this->metriloSessionEvents, $events);
    }
}
