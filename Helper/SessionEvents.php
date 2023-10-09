<?php

namespace Metrilo\Analytics\Helper;

use Magento\Catalog\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class SessionEvents extends AbstractHelper
{
    private string $metriloSessionEvents = 'metrilo_session_key';

    private Session $catalogSession;

    public function __construct(
        Session $catalogSession,
        Context $context
    ) {
        parent::__construct($context);
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
