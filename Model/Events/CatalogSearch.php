<?php

namespace Metrilo\Analytics\Model\Events;

class CatalogSearch
{
    public function __construct(
        \Magento\Framework\UrlInterface     $urlInterface,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->urlInterface = $urlInterface;
        $this->request      = $request;
    }
    public function callJS() {
        $searchQuery = $this->request->getParam('q');
        
        if (!empty($searchQuery)) {
            $searchText = $searchQuery;
        } else {
            $searchText = $this->request->getParam('name');
        }
        
        return "window.metrilo.search('" . $searchText . "', '" . $this->urlInterface->getCurrentUrl() . "');";
    }
}