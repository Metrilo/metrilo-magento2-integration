<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Product implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data              $helper
     * @param \Metrilo\Analytics\Helper\ApiClient         $apiClient
     * @param \Metrilo\Analytics\Helper\ProductSerializer $productSerializer,
     * @param \Metrilo\Analytics\Model\ProductData        $productData
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data              $helper,
        \Metrilo\Analytics\Helper\ApiClient         $apiClient,
        \Metrilo\Analytics\Helper\ProductSerializer $productSerializer,
        \Metrilo\Analytics\Model\ProductData        $productData
    ) {
        $this->helper            = $helper;
        $this->apiClient         = $apiClient;
        $this->productSerializer = $productSerializer;
        $this->productData       = $productData;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($product->getStoreId() == 0) {
                $productStoreIds = $this->helper->getStoreIdsPerProject($product->getStoreIds());
            } else {
                $productStoreIds[] = $product->getStoreId();
            }
    
            foreach ($productStoreIds as $storeId) {
                $productId = $this->productSerializer->productOptions->checkForParentId($product->getId());
                $product   = $this->productData->getProductWithRequestPath($productId, $storeId);
                $client    = $this->apiClient->getClient($storeId);
                $client->product($this->productSerializer->serialize($product));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
