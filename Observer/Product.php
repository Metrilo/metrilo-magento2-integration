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
            $product        = $observer->getEvent()->getProduct();
            $productStoreId = $product->getStoreId();
            
            if ($productStoreId == 0) {
                $productStoreIds = $this->helper->getStoreIdsPerProject($product->getStoreIds());
            } else {
                $productStoreIds[] = $productStoreId;
            }
    
            foreach ($productStoreIds as $storeId) {
                $client    = $this->apiClient->getClient($storeId);
                $productId = $this->productSerializer->productOptions->getParentId($product->getId(), $product->getAttributeText('visibility'));
                $productParent = [];
                
                if(is_array($productId)) { // Magento products can have more than 1 parent
                    $productParent = $productId;
                } else {
                    $productParent[] = $productId;
                }
                
                foreach ($productParent as $productParentId) {
                    $productWithRequestPath = $this->productData->getProductWithRequestPath($productParentId, $storeId);
                    $client->product($this->productSerializer->serialize($productWithRequestPath));
                }
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
