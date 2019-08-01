<?php
    
namespace Metrilo\Analytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Analytics extends Template
{
    public $helper;
    
    public function __construct(
        Context $context,
        \Magento\Framework\App\Action\Context         $actionContext,
        \Metrilo\Analytics\Helper\Data                $helper,
        \Metrilo\Analytics\Helper\SessionEvents       $sessionEvents,
        \Metrilo\Analytics\Model\Events\ProductView   $productViewEvent,
        \Metrilo\Analytics\Model\Events\PageView      $pageViewEvent,
        \Metrilo\Analytics\Model\Events\CategoryView  $categoryViewEvent,
        \Metrilo\Analytics\Model\Events\CatalogSearch $catalogSearchEvent,
        \Metrilo\Analytics\Model\Events\CartView      $cartViewEvent,
        \Metrilo\Analytics\Model\Events\CheckoutView  $checkoutViewEvent,
        array $data = []
    ) {
        $this->actionContext      = $actionContext;
        $this->helper             = $helper;
        $this->sessionEvents      = $sessionEvents;
        $this->productViewEvent   = $productViewEvent;
        $this->pageViewEvent      = $pageViewEvent;
        $this->categoryViewEvent  = $categoryViewEvent;
        $this->catalogSearchEvent = $catalogSearchEvent;
        $this->cartViewEvent      = $cartViewEvent;
        $this->checkoutViewEvent  = $checkoutViewEvent;
        $this->fullActionName     = $this->actionContext->getRequest()->getFullActionName();
        parent::__construct($context, $data);
    }
    
    public function getLibraryUrl() {
        return $this->helper->getApiEndpoint() . '/tracking.js?token=' . $this->helper->getApiToken($this->helper->getStoreId());
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
        switch($this->fullActionName) {
            // product view pages
            case 'catalog_product_view':
                return $this->productViewEvent->callJS();
            // category view pages
            case 'catalog_category_view':
                return $this->categoryViewEvent->callJS();
            // catalog search pages
            case 'catalogsearch_result_index':
                return $this->catalogSearchEvent->callJS();
            // catalog advanced result page
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
    
    public function getEvents() {
        $sessionEvents   = $this->sessionEvents->getSessionEvents();
        $sessionEvents[] = $this->getEvent();
        return $sessionEvents;
    }
}
