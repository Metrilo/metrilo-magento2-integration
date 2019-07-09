<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Config implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Metrilo\Analytics\Helper\Data              $dataHelper,
        \Metrilo\Analytics\Helper\Activity          $activityHelper
    ) {
        $this->messageManager = $messageManager;
        $this->dataHelper     = $dataHelper;
        $this->activityHelper = $activityHelper;
    }

    public function execute(Observer $observer)
    {
        try {
            if (!$this->activityHelper->createActivity($observer->getStore(), 'integrated')) {
                $this->messageManager->addError('The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.');
            }
        } catch (\Exception $e) {
            $this->dataHelper->logError($e);
        }
    }
}
