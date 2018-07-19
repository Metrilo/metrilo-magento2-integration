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
        \Magento\Framework\View\Page\Title $pageTitle,
        \Metrilo\Analytics\Helper\ImagePathResolver $imagePathResolver
    ) {
        $this->_context = $context;
        $this->_coreRegistry = $registry;
        $this->_searchHelper = $searchHelper;
        $this->_pageTitle = $pageTitle;
        $this->fullActionName = $this->_context->getRequest()->getFullActionName();
        $this->imagePathResolver = $imagePathResolver;

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

        // Catalog search pages
        if ($this->fullActionName == 'catalogsearch_result_index') {
            $query = $this->_searchHelper->getEscapedQueryText();
            if ($query) {
                $params = ['query' => $query];
                $this->addEvent('track', 'search', $params);
                return;
            }
        }

        // category view pages
        if ($this->fullActionName == 'catalog_category_view') {
            $category = $this->_coreRegistry->registry('current_category');

            $data =  [
                'id'    =>  $category->getId(),
                'name'  =>  $category->getName()
            ];

            $this->addEvent('track', 'view_category', $data);
            return;
        }

        // product view pages
        if ($this->fullActionName == 'catalog_product_view') {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->_coreRegistry->registry('current_product');
            $data =  [
                'id'    => $product->getId(),
                'sku'   => $product->getSku(),
                'name'  => $product->getName(),
                'price' => $product->getPrice(),
                'url'   => $product->getProductUrl()
            ];
            // Additional information ( image and categories )

            if($product) {
                if(!empty($this->imagePathResolver->getBaseImage($product))) {
                    $data['image_url'] = $this->imagePathResolver->getBaseImage($product);
                }
            }

            if(count($product->getCategoryIds())) {
                $categories = array();
                $collection = $product->getCategoryCollection()
                    ->addAttributeToSelect('id')
                    ->addAttributeToSelect('name');

                foreach ($collection as $category) {
                    $categories[] = array(
                        'id' => $category->getId(),
                        'name' => $category->getName()
                    );
                }
                $data['categories'] = $categories;
            }

            $this->addEvent('track', 'view_product', $data);
            return;
        }

        // cart view
        if ($this->fullActionName == 'checkout_cart_index') {
            $this->addEvent('track', 'view_cart', array());
            return;
        }

        // checkout
        if ($this->fullActionName == 'checkout_index_index') {
            $this->addEvent('track', 'checkout_start', array());
            return;
        }

        // CMS and any other pages
        $title = $this->_pageTitle->getShort();
        $this->addEvent('track', 'pageview', $title, array('backend_hook' => $this->fullActionName));
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
