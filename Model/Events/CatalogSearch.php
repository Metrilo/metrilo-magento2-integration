<?php

namespace Metrilo\Analytics\Model\Events;

class CatalogSearch
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }
    public function callJS($searchText) {
        return "window.metrilo.search('" . $searchText . "', '" . $this->urlInterface->getCurrentUrl() . "');";
    }
}