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
        $url          = $this->urlInterface->getCurrentUrl();
        $name['name'] = $this->pageTitle->getShort();
        return "window.metrilo.viewPage('" . $url . "', " . json_encode($name) . ");";
    }
}