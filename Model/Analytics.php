<?php

namespace Metrilo\Analytics\Model;

use Magento\Framework\DataObject;

class Analytics extends DataObject {

    protected $events = [];

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->_context = $context;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_coreRegistry = $registry;
        $this->_searchHelper = $searchHelper;
        $this->_pageConfig = $pageConfig;
        $this->fullActionName = $this->_context->getRequest()->getFullActionName();

        $this->addPageEvents();
    }

    /**
     * Track page views
     *
     * @return mixed
     */
    public function addPageEvents() {
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
        if($this->fullActionName == 'catalog_category_view') {
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
            $product = $this->_coreRegistry->registry('current_product');
            $data =  [
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => $product->getFinalPrice(),
                'url'   => $product->getProductUrl()
            ];
            // Additional information ( image and categories )
            /*if($product->getImage())
                $data['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'image');
            if(count($product->getCategoryIds())) {
                $categories = array();
                $collection = $product->getCategoryCollection()->addAttributeToSelect('*');
                foreach ($collection as $category) {
                    $categories[] = array(
                        'id' => $category->getId(),
                        'name' => $category->getName()
                    );
                }
                $data['categories'] = $categories;
            }*/
            $this->addEvent('track', 'view_product', $data);
            return;
        }

        // cart view
        if($this->fullActionName == 'checkout_cart_index') {
            $this->addEvent('track', 'view_cart', array());
            return;
        }

        // checkout
        if ($this->fullActionName == 'checkout_index_index') {
            $this->addEvent('track', 'checkout_start', array());
            return;
        }

        // CMS and any other pages
        $title = $this->_pageConfig->getTitle();
        $this->addEvent('track', 'pageview', $title->get(), array('backend_hook' => $this->fullActionName));
    }

    /**
    * Events that we don't want to track
    *
    * @param string full action name
    */
    private function _isRejected($action)
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
    public function addEvent($method, $type, $data, $metaData = false) {
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
    public function getEvents() {
        return $this->events;
    }
}