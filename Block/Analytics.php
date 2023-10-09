<?php

namespace Metrilo\Analytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\CartView;
use Metrilo\Analytics\Model\Events\CatalogSearch;
use Metrilo\Analytics\Model\Events\CategoryView;
use Metrilo\Analytics\Model\Events\CheckoutView;
use Metrilo\Analytics\Model\Events\PageView;
use Metrilo\Analytics\Model\Events\ProductView;

class Analytics extends Template
{
    private Data $helper;

    private SessionEvents $sessionEvents;

    private ProductView $productViewEvent;

    private PageView $pageViewEvent;

    private CategoryView $categoryViewEvent;

    private CatalogSearch $catalogSearchEvent;

    private CartView $cartViewEvent;

    private CheckoutView $checkoutViewEvent;

    public function __construct(
        Context $context,
        Data $helper,
        SessionEvents $sessionEvents,
        ProductView $productViewEvent,
        PageView $pageViewEvent,
        CategoryView $categoryViewEvent,
        CatalogSearch $catalogSearchEvent,
        CartView $cartViewEvent,
        CheckoutView $checkoutViewEvent,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->sessionEvents = $sessionEvents;
        $this->productViewEvent = $productViewEvent;
        $this->pageViewEvent = $pageViewEvent;
        $this->categoryViewEvent = $categoryViewEvent;
        $this->catalogSearchEvent = $catalogSearchEvent;
        $this->cartViewEvent = $cartViewEvent;
        $this->checkoutViewEvent = $checkoutViewEvent;
        parent::__construct($context, $data);
    }

    public function getLibraryUrl(): string
    {
        return $this->helper->getApiEndpoint() . '/tracking.js?token=' .
            $this->helper->getApiToken($this->helper->getStoreId());
    }

    protected function _toHtml()
    {
        if (!$this->helper->isEnabled($this->helper->getStoreId())) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getEvent()
    {
        switch ($this->_request->getFullActionName()) {
            // product view pages
            case 'catalog_product_view':
                return $this->productViewEvent->callJS();
            // category view pages
            case 'catalog_category_view':
                return $this->categoryViewEvent->callJS();
            // catalog search pages
            // catalog advanced result page
            case 'catalogsearch_result_index':
            case 'catalogsearch_advanced_result':
                return $this->catalogSearchEvent->callJS();
            // cart view pages
            case 'checkout_cart_index':
                return $this->cartViewEvent->callJS();
            // checkout view page
            case 'checkout_index_index':
                return $this->checkoutViewEvent->callJs();
            // CMS and any other pages
            default:
                return $this->pageViewEvent->callJS();
        }
    }

    public function getEvents()
    {
        $sessionEvents = $this->sessionEvents->getSessionEvents();
        $sessionEvents[] = $this->getEvent();

        return $sessionEvents;
    }
}
