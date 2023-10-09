<?php

namespace Metrilo\Analytics\Model\Events;

use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;

class CatalogSearch
{
    private UrlInterface $urlInterface;

    private Http $request;

    public function __construct(
        UrlInterface $urlInterface,
        Http $request
    ) {
        $this->urlInterface = $urlInterface;
        $this->request = $request;
    }

    public function callJS(): string
    {
        $searchQuery = $this->request->getParam('q');

        if (!empty($searchQuery)) {
            $searchText = $searchQuery;
        } else {
            $searchText = $this->request->getParam('name');
        }

        return "window.metrilo.search('" . $searchText . "', '" . $this->urlInterface->getCurrentUrl() . "');";
    }
}
