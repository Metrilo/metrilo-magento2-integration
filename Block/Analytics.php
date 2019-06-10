<?php

namespace Metrilo\Analytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Analytics extends Template
{
    public $helper;
    
    public function __construct(
        Context $context,
        \Magento\Framework\App\Action\Context            $actionContext,
        \Metrilo\Analytics\Helper\Data                   $helper,
        \Magento\Framework\Registry                      $registry,
        \Metrilo\Analytics\Model\Events\PageViewEvent    $pageViewEvent,
        array $data = []
    ) {
        $this->actionContext    = $actionContext;
        $this->helper           = $helper;
        $this->registry         = $registry;
        $this->pageViewEvent    = $pageViewEvent;
        $this->fullActionName   = $this->actionContext->getRequest()->getFullActionName();
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
                return new \Metrilo\Analytics\Model\Events\ProductViewEvent($this->coreRegistry);
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
}
