<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface
{
    protected $_helper;
    protected $_registry = null;

    /**
     * Automatic dependency
     * @param \Metrilo\Analytics\Helper\Data      $helper
     * @param \Magento\Framework\Registry         $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Search\Helper\Data         $searchHelper
     * @param \Magento\Customer\Model\Session     $customerSession
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->request = $request;
        $this->searchHelper = $searchHelper;
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
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
            $pageId = $this->request->getParam('page_id', $this->request->getParam('id', false));
            // var_dump(get_class_methods($this->_objectManager)); exit;
            $resultPage = $this->_objectManager->get('Magento\Cms\Model\Page')->load($pageId);
            var_dump(get_class_methods($resultPage));
            exit;
            $title = Mage::getSingleton('cms/page')->getTitle();
            $this->_helper->addEvent('track', 'pageview', $title, array('backend_hook' => $action));
            return;
        }
        // category view pages
        if($action == 'catalog_category_view') {
            $category = Mage::registry('current_category');
            $data =  array(
                'id'    =>  $category->getId(),
                'name'  =>  $category->getName()
            );
            $helper->addEvent('track', 'view_category', $data);
            return;
        }
        // product view pages
        if ($action == 'catalog_product_view') {
            $product = Mage::registry('current_product');
            $data =  array(
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => $product->getFinalPrice(),
                'url'   => $product->getProductUrl()
            );
            // Additional information ( image and categories )
            if($product->getImage())
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
            }
            $helper->addEvent('track', 'view_product', $data);
            return;
        }
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
