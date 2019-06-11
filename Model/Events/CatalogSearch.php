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
        file_put_contents('CatalogView.txt', 'Catalog Event!' . PHP_EOL, FILE_APPEND);
    }
    public function callJS() {
        return "window.metrilo.search('" . $this->searchHelper->getEscapedQueryText() . "', '" . $this->urlInterface->getCurrentUrl() . "');";
    }
}