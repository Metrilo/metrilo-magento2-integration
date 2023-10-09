<?php

namespace Metrilo\Analytics\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\OrderSerializer;

class Order implements ObserverInterface
{
    private Data $helper;

    private ApiClient $apiClient;

    private OrderSerializer $orderSerializer;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper,
        ApiClient $apiClient,
        OrderSerializer $orderSerializer
    ) {
        $this->helper = $helper;
        $this->apiClient = $apiClient;
        $this->orderSerializer = $orderSerializer;
    }

    /**
     * Trigger on save Order
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $storeId = $order->getStoreId();

            if (!$this->helper->isEnabled($storeId)) {
                return;
            }

            $client = $this->apiClient->getClient($storeId);
            $serializedOrder = $this->orderSerializer->serialize($order);

            if ($serializedOrder) {
                $client->order($serializedOrder);
            }
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }
}
