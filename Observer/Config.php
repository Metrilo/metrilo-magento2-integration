<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Config implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Metrilo\Analytics\Helper\Data              $dataHelper,
        \Metrilo\Analytics\Helper\Activity          $activityHelper,
        \Metrilo\Analytics\Helper\ApiClient         $apiClient
    ) {
        $this->messageManager = $messageManager;
        $this->dataHelper     = $dataHelper;
        $this->activityHelper = $activityHelper;
        $this->apiClient      = $apiClient;
    }

    public function execute(Observer $observer)
    {
        try {
            $storeId  = $observer->getStore();
            $activity = $this->activityHelper->createActivity($storeId, 'integrated');
            $client   = $this->apiClient->getClient($storeId);
    
            if (!$client->createActivity($activity['url'], $activity['data'])) {
                $this->messageManager->addError('The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.');
            }
        } catch (\Exception $e) {
            $this->dataHelper->logError($e);
        }
    }
}
