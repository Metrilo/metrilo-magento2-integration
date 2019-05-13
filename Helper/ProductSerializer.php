<?php

namespace Metrilo\Analytics\Helper;

class ProductSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Metrilo\Analytics\Helper\ProductOptions   $productOptions
    ) {
        $this->storeManager   = $storeManager;
        $this->productOptions = $productOptions;
    }
    
    public function serialize($product)
    {
        $storeId     = $product->getStoreId();
        $productId   = $product->getId();
        $productType = $product->getTypeId();
        
        if ($productType == 'simple' && $this->productOptions->checkForParentId($productId)) {
            return;
        }
        
        $imageUrl = (!empty($product->getImage())) ? $this->productOptions->productImageUrl->getProductImageUrl($product->getImage()) : '';
        $price    = (!empty($product->getPrice())) ? $product->getPrice() : 0; // Does not return grouped/bundled parent price
        $url      = $this->storeManager->getStore($storeId)->getBaseUrl() . $product->getRequestPath();
        
        $productOptions = $this->productOptions->getProductOptions($product);
        
        return [
            'categories' => $product->getCategoryIds(),
            'id'         => $productId,
            'sku'        => $product->getSku(),
            'imageUrl'   => $imageUrl,
            'name'       => $product->getName(),
            'price'      => $price,
            'url'        => $url,
            'options'    => $productOptions
        ];
    }
}

