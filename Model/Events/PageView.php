<?php

namespace Metrilo\Analytics\Model\Events;

class PageView
{
    public function __construct(
        \Magento\Framework\View\Page\Title $pageTitle,
        \Magento\Framework\UrlInterface    $urlInterface
    ) {
        $this->pageTitle    = $pageTitle;
        $this->urlInterface = $urlInterface;
    }
    public function callJS()
    {
        return "window.metrilo.viewPage('" .
            $this->urlInterface->getCurrentUrl() . "', " .
            json_encode(array('name' => $this->pageTitle->getShort())) . ");";
    }
}
