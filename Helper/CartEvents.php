<?php

namespace Metrilo\Analytics\Helper;

class CartEvents extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession
    )
    {
        $this->catalogSession = $catalogSession;
    }
    
    public function getSessionEvents($type)
    {
        $events = [];
        $data   = $this->catalogSession->getData($type, true);
        if ($data) {
            $events = $data;
        }
        return $events;
    }
    
    public function addSessionEvent($type, $data)
    {
        $events = [];
        if ($this->catalogSession->getData($type) != '') {
            $events = (array)$this->catalogSession->getData($type);
        }
        
        array_push($events, $data);
        $this->catalogSession->setData($type, $events);
    }
}