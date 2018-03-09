<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Config implements ObserverInterface
{

    private $_helper;

    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->_helper = $helper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_helper->checkCredentials()) {
            $this->messageManager->addError('The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.');
        }
    }
}
