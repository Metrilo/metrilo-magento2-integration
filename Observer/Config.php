<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Metrilo\Analytics\Helper\Activity;
use Metrilo\Analytics\Helper\Data;

class Config implements ObserverInterface
{
    private ManagerInterface $messageManager;

    private Data $dataHelper;

    private Activity $activityHelper;

    public function __construct(
        ManagerInterface $messageManager,
        Data              $dataHelper,
        Activity          $activityHelper
    ) {
        $this->messageManager = $messageManager;
        $this->dataHelper     = $dataHelper;
        $this->activityHelper = $activityHelper;
    }

    public function execute(Observer $observer): void
    {
        try {
            $storeId = $observer->getStore();
            if (!$this->activityHelper->createActivity($storeId, 'integrated')) {
                if ((int)$storeId === 0) {
                    $this->messageManager->addError(
                        'You\'ve just entered the API token and API Secret to the default configuration scope.
                        This means that the Metrilo module will be added to all your store views.
                        If you want to connect only a specific store view, please remove it form the default scope and
                        add it only to the specific store view configuration scope.
                        You can find the "Import" button by opening any specific configuration scope.'
                    );
                } else {
                    $this->messageManager->addError(
                        'The API Token and/or API Secret you have entered are invalid.
                    You can find the correct ones in Settings -> Installation in your Metrilo account.'
                    );
                }
            }
        } catch (\Exception $e) {
            $this->dataHelper->logError($e);
        }
    }
}
