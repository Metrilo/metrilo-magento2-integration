<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;

class Pageview implements ObserverInterface
{
    protected $_helper;
    protected $_registry = null;
    protected $pageFactory;

    /**
     * Automatic dependency
     * @param \Metrilo\Analytics\Helper\Data      $helper
     * @param \Magento\Framework\Registry         $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Search\Helper\Data         $searchHelper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->request = $request;
        $this->searchHelper = $searchHelper;
        $this->pageConfig = $pageConfig;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Executes on "controller_front_send_response_before" event
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = (string)$this->request->getFullActionName();
        if (!$action || $this->_isRejected($action)) {
            return;
        }

        // Catalog search pages
        if ($action == 'catalogsearch_result_index') {
            $query = $this->searchHelper->getEscapedQueryText();
            if ($query) {
                $params = array(
                    'query' => $query
                );
                $this->_helper->addEvent('track', 'search', $params);
                return;
            }
        }
        // homepage & CMS pages
        if ($action == 'cms_index_index' || $action == 'cms_page_view') {
            /** @var \Magento\Framework\View\Page\Title */
            $title = $this->pageConfig->getTitle();

            $this->_helper->addEvent('track', 'pageview', $title->get(), array('backend_hook' => $action));
            return;
        }
        // category view pages
        if($action == 'catalog_category_view') {
            $category = $this->_coreRegistry->registry('current_category');
            $data =  array(
                'id'    =>  $category->getId(),
                'name'  =>  $category->getName()
            );

            $this->_helper->addEvent('track', 'view_category', $data);
            return;
        }
        return;
        // product view pages
        if ($action == 'catalog_product_view') {
            $product = $this->_coreRegistry->registry('current_product');
            $data =  array(
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => $product->getFinalPrice(),
                'url'   => $product->getProductUrl()
            );
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
            $this->_helper->addEvent('track', 'view_product', $data);
            return;
        }
        // Done so far
        // cart view
        if($action == 'checkout_cart_index') {
            $helper->addEvent('track', 'view_cart', array());
            return;
        }
        // checkout
        if ($action != 'checkout_cart_index' && strpos($action, 'checkout') !== false && strpos($action, 'success') === false) {
            $helper->addEvent('track', 'checkout_start', array());
            return;
        }
        // Any other pages
        $title = $observer->getEvent()->getLayout()->getBlock('head')->getTitle();
        $helper->addEvent('track', 'pageview', $title, array('backend_hook' => $action));
    }

    /**
    * Events that we don't want to track
    *
    * @param string event
    */
    private function _isRejected($event)
    {
        return in_array(
            $event,
            array('catalogsearch_advanced_index', 'catalogsearch_advanced_result')
        );
    }
}
