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
        \Magento\Framework\Registry $registry
    ) {
        $this->_context = $context;
        $this->_coreRegistry = $registry;
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
            // product view pages
            case 'catalog_product_view':
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_coreRegistry->registry('current_product');
                $data =  [
                'productId' => $product->getId()
            ];
    
                $this->addEvent('track', 'view_product', $data);
                return;
            default:
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
