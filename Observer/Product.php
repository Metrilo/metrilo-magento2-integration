<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Product implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                                 $helper
     * @param \Metrilo\Analytics\Helper\ApiClient                            $apiClient
     * @param \Metrilo\Analytics\Helper\ProductSerializer                    $productSerializer,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface             $scopeConfig,
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
     * @param \Magento\Bundle\Model\Product\Type                             $bundleType,
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped             $groupedType,
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable   $configurableType
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data                                 $helper,
        \Metrilo\Analytics\Helper\ApiClient                            $apiClient,
        \Metrilo\Analytics\Helper\ProductSerializer                    $productSerializer,
        \Magento\Framework\App\Config\ScopeConfigInterface             $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Bundle\Model\Product\Type                             $bundleType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped             $groupedType,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable   $configurableType
    ) {
        $this->helper            = $helper;
        $this->apiClient         = $apiClient;
        $this->productSerializer = $productSerializer;
        $this->scopeConfig       = $scopeConfig;
        $this->productCollection = $productCollection;
        $this->bundleType        = $bundleType;
        $this->groupedType       = $groupedType;
        $this->configurableType  = $configurableType;
    }
    
    private function getStoreIdsPerProject($storeIds) {
        $storeIdConfigMap = [];
        foreach ($storeIds as $storeId) {
            if ($storeId == 0) { // store 0 is always admin
                continue;
            }
            $storeIdConfigMap[$storeId] = $this->scopeConfig
                ->getValue(
                    'metrilo_analytics/general/api_key',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
        }
        $storeIdConfigMap = array_unique($storeIdConfigMap);
        
        return array_keys($storeIdConfigMap);
    }
    
    private function getProductWithRequestPath($product, $storeId) {
        $productId = $this->checkForParentId($product->getId());
        $productObject = $this->productCollection
                                  ->create()
                                  ->addStoreFilter($storeId)
                                  ->addAttributeToSelect(['name','price'])
                                  ->joinTable(
                                      ['url' => 'url_rewrite'],
                                      'entity_id = entity_id',
                                      ['request_path', 'store_id', 'metadata'],
                                      ['entity_id' => $productId,
                                       'entity_type' => 'product',
                                       'store_id' => $storeId,
                                       'metadata' => array('null' => true)]
                                  )
                                  ->getFirstItem();
        
        $productObject->setStoreId($storeId);
        
        return $productObject;
    }
    
    private function checkForParentId($productId)
    {
        if ($this->configurableType->getParentIdsByChild($productId)) {
            $productId = $this->configurableType->getParentIdsByChild($productId);
        }
        
        if ($this->bundleType->getParentIdsByChild($productId)) {
            $productId = $this->bundleType->getParentIdsByChild($productId);
        }
        
        if ($this->groupedType->getParentIdsByChild($productId)) {
            $productId = $this->groupedType->getParentIdsByChild($productId);
        }
        
        if (is_array($productId)) {
            return $productId[0];
        } else {
            return $productId;
        }
    }
    
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($product->getStoreId() == 0) {
                $productStoreIds = $this->getStoreIdsPerProject($product->getStoreIds());
            } else {
                $productStoreIds[] = $product->getStoreId();
            }
    
            foreach ($productStoreIds as $storeId) {
                $productObject = $this->getProductWithRequestPath($product, $storeId);
                $client        = $this->apiClient->getClient($storeId);
                $client->product($this->productSerializer->serialize($productObject));
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
