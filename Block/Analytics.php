<?php

namespace Metrilo\Analytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block rendering events to frontend
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Analytics extends Template
{
    public $helper;
    
    public function __construct(
        Context $context,
        \Magento\Framework\App\Action\Context $actionContext,
        \Metrilo\Analytics\Helper\Data        $helper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->actionContext  = $actionContext;
        $this->helper         = $helper;
        $this->coreRegistry   = $registry;
        $this->fullActionName = $this->actionContext->getRequest()->getFullActionName();
        parent::__construct($context, $data);
    }

    public function getLibraryUrl() {
        return $this->helper->getApiEndpoint() . '/tracking.js?token=' . $this->helper->getApiToken($this->helper->getStoreId());
    }

    public function getEvents()
    {
        return array_merge(
            $this->helper->getSessionEvents(),
            $this->dataModel->getEvents()
        );
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
                return new \Metrilo\Analytics\Model\Events\ProductViewEvent($this->coreRegistry->registry('current_product')->getId());
            default:
                break;
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
