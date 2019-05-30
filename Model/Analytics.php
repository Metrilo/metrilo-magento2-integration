<?php

namespace Metrilo\Analytics\Model;

use Magento\Framework\DataObject;

/**
 * Model object holding events data
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Analytics extends DataObject
{

    /**
     * @var array
     */
    protected $events = [];
    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    private $imageHelperFactory;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\Registry                        $registry
     * @param \Magento\Search\Helper\Data                        $searchHelper
     * @param \Magento\Framework\View\Page\Config                $pageConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\View\Page\Title $pageTitle
    ) {
        $this->_context = $context;
        $this->_coreRegistry = $registry;
        $this->_searchHelper = $searchHelper;
        $this->_pageTitle = $pageTitle;
        $this->fullActionName = $this->_context->getRequest()->getFullActionName();

        $this->addPageEvents();

    }

    /**
     * Track page views
     *
     * @return mixed
     */
    public function addPageEvents()
    {
        if (!$this->fullActionName || $this->_isRejected($this->fullActionName)) {
            return;
        }
        
        switch($this->fullActionName) {
            // Catalog search pages
            case 'catalogsearch_result_index':
                $query = $this->_searchHelper->getEscapedQueryText();
                if ($query) {
                    $params = ['query' => $query];
                    $this->addEvent('track', 'search', $params);
                    return;
                }
            // category view pages
            case 'catalog_category_view':
                $category = $this->_coreRegistry->registry('current_category');
                $data =  [
                    'id'    =>  $category->getId(),
                    'name'  =>  $category->getName()
                ];
                $this->addEvent('track', 'view_category', $data);
                return;
            // product view pages
            case 'catalog_product_view':
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_coreRegistry->registry('current_product');
                $data =  [
                    'productId' => $product->getId()
                ];
    
                $this->addEvent('track', 'view_product', $data);
                return;
            // cart view
            case 'checkout_cart_index':
                $this->addEvent('track', 'view_cart', array());
                return;
            // checkout
            case 'checkout_index_index':
                $this->addEvent('track', 'checkout_start', array());
                return;
            default:
                // CMS and any other pages
                $title = $this->_pageTitle->getShort();
                $this->addEvent('track', 'pageview', $title, array('backend_hook' => $this->fullActionName));
                break;
        }
    }

    /**
    * Events that we don't want to track
    *
    * @param string full action name
    */
    protected function _isRejected($action)
    {
        $rejected = [
            'catalogsearch_advanced_index',
            'catalogsearch_advanced_result'
        ];
        return in_array($action, $rejected);
    }

    /**
     * Add event to queue
     *
     * @param string $method Can be identiy|track
     * @param string $type
     * @param array $data
     * @param mixed $metaData
     */
    public function addEvent($method, $type, $data, $metaData = false)
    {
        $eventToAdd = [
            'method' => $method,
            'type' => $type,
            'data' => $data,
            'metaData' => false
        ];
        if ($metaData) {
            $eventToAdd['metaData'] = $metaData;
        }
        array_push($this->events, $eventToAdd);
    }

    /**
     * Return events for tracking
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }
}
