<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface {

    /**
     * @param \Metrilo\Analytics\Helper\Data      $helper
     * @param \Magento\Framework\Registry         $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\Session     $customerSession
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_registry = $registry;
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Collect orders details
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {

    }

}
