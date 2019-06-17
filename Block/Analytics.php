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
        \Metrilo\Analytics\Model\Events\ProductView   $productViewEvent,
        \Metrilo\Analytics\Model\Events\PageView      $pageViewEvent,
        \Metrilo\Analytics\Model\Events\CategoryView  $categoryViewEvent,
        \Metrilo\Analytics\Model\Events\CatalogSearch $catalogSearchEvent,
        \Metrilo\Analytics\Model\Events\CartView      $cartViewEvent,
        array $data = []
    ) {
        $this->actionContext      = $actionContext;
        $this->helper             = $helper;
        $this->productViewEvent   = $productViewEvent;
        $this->pageViewEvent      = $pageViewEvent;
        $this->categoryViewEvent  = $categoryViewEvent;
        $this->catalogSearchEvent = $catalogSearchEvent;
        $this->cartViewEvent      = $cartViewEvent;
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
        if (!$this->fullActionName || $this->isRejected($this->fullActionName)) {
            return;
        }
        
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
            // cart view pages
            case 'checkout_cart_index':
                return $this->cartViewEvent->callJS();
            // CMS and any other pages
            default:
                return $this->pageViewEvent->callJS();
        }
    }
    
    protected function isRejected($action)
    {
        $rejected = [
            'catalogsearch_advanced_index',
            'catalogsearch_advanced_result'
        ];
        return in_array($action, $rejected);
    }
    
    public function getCartEvents() {
        return $this->helper->cartEvents;
    }
}
