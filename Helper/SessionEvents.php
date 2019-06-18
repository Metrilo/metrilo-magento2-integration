<?php

namespace Metrilo\Analytics\Helper;

class SessionEvents extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
    }
    
    public function getSessionEvents($type)
    {
        $sessionEvents = $this->catalogSession->getData($type, true);
        
        if ($sessionEvents === null) {
            $sessionEvents = [];
        }
        
        return $sessionEvents;
    }
    
    public function addSessionEvent($type, $data)
    {
        $events = [];
        $sessionData = $this->catalogSession->getData($type, true);
        if ($sessionData !== null) {
            $events = $sessionData;
        }
        
        $events[] = $data;
        $this->catalogSession->setData($type, $events);
    }
}