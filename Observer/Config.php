<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Config implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http         $request,
        \Metrilo\Analytics\Helper\Data              $helper,
        \Metrilo\Analytics\Helper\ApiClient         $apiClient
    ) {
        $this->messageManager = $messageManager;
        $this->request        = $request;
        $this->helper         = $helper;
        $this->apiClient      = $apiClient;
    }

    public function execute(Observer $observer)
    {
        try {
            $storeId  = (int)$this->request->getParam('store', 0);
            $activity = $this->helper->createActivity($storeId, 'integrated');
            $client   = $this->apiClient->getClient($storeId);
    
            if (!$client->createActivity($activity['url'], $activity['data'])) {
                $this->messageManager->addError('The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.');
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
