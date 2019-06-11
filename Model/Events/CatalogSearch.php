<?php

namespace Metrilo\Analytics\Model\Events;

class CatalogSearch
{
    public function __construct(
        \Magento\Search\Helper\Data     $searchHelper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->searchHelper = $searchHelper;
        $this->urlInterface = $urlInterface;
    }
    public function callJS() {
        return "window.metrilo.search('" . $this->searchHelper->getEscapedQueryText() . "', '" . $this->urlInterface->getCurrentUrl() . "');";
    }
}