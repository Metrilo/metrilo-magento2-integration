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
        
        if ($productType === 'simple' && $this->productOptions->getParentIds($productId, $productType) != []) {
            return false;
        }
        
        $productImage = $product->getImage();
        $productPrice = $product->getPrice();
        $imageUrl     = (!empty($productImage)) ? $this->productOptions
            ->productImageUrl
            ->getProductImageUrl($productImage) : '';
        $price        = (!empty($productPrice)) ? $productPrice : 0; // Does not return grouped/bundled parent price
        $specialPrice = $product->getSpecialPrice();
        $url          = $this->storeManager->getStore($storeId)->getBaseUrl() . $product->getRequestPath();
        
        if ($productType === 'configurable' || $productType === 'bundle' || $productType === 'grouped') {
            $productOptions = $this->productOptions->getParentOptions($product);
        } else {
            $productOptions = [];
        }
        
        return [
            'categories' => $product->getCategoryIds(),
            'id'         => $productId,
            'sku'        => $product->getSku(),
            'imageUrl'   => $imageUrl,
            'name'       => $product->getName(),
            'price'      => $specialPrice ? $specialPrice : $price,
            'url'        => $url,
            'options'    => $productOptions
        ];
    }
}
