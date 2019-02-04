<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Metrilo\Analytics\Api\Client;

class Order implements ObserverInterface
{

    private $helper;

    /**
     * @param \Metrilo\Analytics\Helper\Data $helper
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Trigger on save Order
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $storeId = $order->getStoreId();
    
            if (!$this->helper->isEnabled($storeId)) {
                return;
            }
    
            $token         = $this->helper->getApiToken($storeId);
            $platform      = 'Magento ' . $this->helper->metaData->getEdition() . ' ' . $this->helper->metaData->getVersion();
            $pluginVersion = $this->helper->moduleList->getOne($this->helper::MODULE_NAME)['setup_version'];
    
            $client        = new Client($token, $platform, $pluginVersion);
    
            if (!trim($order->getCustomerEmail())) {
                return;
            }
            
            $serializedOrder = $this->helper->orderSerializer->serializeOrder($order);
    
            $client->order($serializedOrder);
            $this->helper->requestLogger(__DIR__ . 'OrderRequest.log', json_encode(array('ObserverOrder' => $serializedOrder)));
            $this->helper->requestLogger(__DIR__ . 'OrderRequest.log', $client->order($serializedOrder));
            $this->helper->requestLogger(__DIR__ . 'OrderRequest.log', '--------------------------------------');
    
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
