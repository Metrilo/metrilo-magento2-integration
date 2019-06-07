<?php

namespace Metrilo\Analytics\Model\Events;

class PageViewEvent
{
    public function __construct(
        $pageTitle,
        $urlInterface
    ) {
        $this->pageTitle    = $pageTitle;
        $this->urlInterface = $urlInterface;
    }
    public function callJS() {
        return "window.metrilo.viewPage('" . $this->urlInterface->getCurrentUrl() . "', " . json_encode(array('name' => $this->pageTitle->getShort())) . ");";
    }
}