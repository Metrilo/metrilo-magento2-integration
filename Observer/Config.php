<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Config implements ObserverInterface
{

    private $_helper;

    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Metrilo\Analytics\Helper\AdminStoreResolver $resolver
    ) {
        $this->messageManager = $messageManager;
        $this->resolver = $resolver;
        $this->_helper = $helper;
    }

    public function execute(Observer $observer)
    {
        $storeId = $this->resolver->getAdminStoreId();
        if (!$this->_helper->createActivity($storeId, 'integrated')) {
            $this->messageManager->addError('The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.');
        }
    }
}
