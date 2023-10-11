<?php

namespace Metrilo\Analytics\Model\Events;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Title;

class PageView
{
    private Title $pageTitle;

    private UrlInterface $urlInterface;

    private Json $json;

    public function __construct(
        Title $pageTitle,
        UrlInterface $urlInterface,
        Json $json
    ) {
        $this->pageTitle = $pageTitle;
        $this->urlInterface = $urlInterface;
        $this->json = $json;
    }

    public function callJS(): string
    {
        return "window.metrilo.viewPage('" .
            $this->urlInterface->getCurrentUrl() . "', " .
            $this->json->serialize(['name' => $this->pageTitle->getShort()]) . ");";
    }
}
