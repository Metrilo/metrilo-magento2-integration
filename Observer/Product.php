<?php

namespace Metrilo\Analytics\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ProductOptions;
use Metrilo\Analytics\Helper\ProductSerializer;
use Metrilo\Analytics\Model\ProductData;

class Product implements ObserverInterface
{
    private Data $helper;

    private ApiClient $apiClient;

    private ProductSerializer $productSerializer;

    private ProductData $productData;

    private ProductOptions $productOptions;

    /**
     * @param Data $helper
     * @param ApiClient $apiClient
     * @param ProductSerializer $productSerializer
     * @param ProductData $productData
     * @param ProductOptions $productOptions
     */
    public function __construct(
        Data $helper,
        ApiClient $apiClient,
        ProductSerializer $productSerializer,
        ProductData $productData,
        ProductOptions $productOptions
    ) {
        $this->helper = $helper;
        $this->apiClient = $apiClient;
        $this->productSerializer = $productSerializer;
        $this->productData = $productData;
        $this->productOptions = $productOptions;
    }

    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $productStoreId = $product->getStoreId();

            if ($productStoreId == 0) {
                $productStoreIds = $this->helper->getStoreIdsPerProject($product->getStoreIds());
            } else {
                if (!$this->helper->isEnabled($productStoreId)) {
                    return;
                }
                $productStoreIds[] = $productStoreId;
            }

            foreach ($productStoreIds as $storeId) {
                $client = $this->apiClient->getClient($storeId);
                $productParents = $this->productOptions->getParentIds($product->getId(), $product->getTypeId());
                $productsToSync = ($productParents) ?: [$product->getId()];

                foreach ($productsToSync as $productId) {
                    $productWithRequestPath = $this->productData->getProductWithRequestPath($productId, $storeId);
                    $client->product($this->productSerializer->serialize($productWithRequestPath));
                }
            }
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }
}
