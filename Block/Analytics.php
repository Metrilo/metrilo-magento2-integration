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

    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    public $helper;

    /**
     * @param Context                                           $context
     * @param \Magento\Framework\App\Action\Context             $actionContext
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Metrilo\Analytics\Model\Analytics                $dataModel
     * @param \Metrilo\Analytics\Helper\Events\ProductViewEvent $productViewEvent,
     * @param array                                             $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Action\Context             $actionContext,
        \Metrilo\Analytics\Helper\Data                    $helper,
        \Metrilo\Analytics\Helper\Events\ProductViewEvent $productViewEvent,
        array $data = []
    ) {
        $this->actionContext    = $actionContext;
        $this->helper           = $helper;
        $this->productViewEvent = $productViewEvent;
        $this->fullActionName   = $this->actionContext->getRequest()->getFullActionName();
        parent::__construct($context, $data);
    }

    /**
     * Get API Token
     *
     * @return bool|null|string
     */
    public function getApiToken()
    {
        return $this->helper->getApiToken($this->helper->getStoreId());
    }

    /**
     * Get API Endpoint
     *
     * @return bool|null|string
     */
    public function getApiEndpoint()
    {
        return $this->helper->getApiEndpoint();
    }

    /**
     * Get events to track them to metrilo js api
     *
     * @return array
     */
    public function getEvents()
    {
        return array_merge(
            $this->helper->getSessionEvents(),
            $this->dataModel->getEvents()
        );
    }

    /**
     * Render metrilo js if module is enabled
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function _toHtml()
    {
        if (!$this->helper->isEnabled($this->helper->getStoreId())) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Track page views
     *
     * @return mixed
     */
    public function getEvent()
    {
        if (!$this->fullActionName || $this->isRejected($this->fullActionName)) {
            return;
        }
        
        switch($this->fullActionName) {
            // product view pages
            case 'catalog_product_view':
                return $this->productViewEvent;
            default:
                break;
        }
    }

    /**
     * Events that we don't want to track
     *
     * @param string full action name
     */
    protected function isRejected($action)
    {
        $rejected = [
            'catalogsearch_advanced_index',
            'catalogsearch_advanced_result'
        ];
        return in_array($action, $rejected);
    }
}
