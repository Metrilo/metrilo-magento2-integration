<?php

namespace Metrilo\Analytics\Helper;

class ProductOptions extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Metrilo\Analytics\Helper\ProductImageUrl                    $productImageUrl
    ) {
        $this->configurableType = $configurableType;
        $this->productImageUrl  = $productImageUrl;
    }
    
    public function getConfigurableOptions($product)
    {
        $productOptions = [];
        $productType    = $product->getTypeId();
        
        $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        
        foreach ($childrenProducts as $childProduct) {
            $imageUrl = (!empty($childProduct->getImage())) ? $this->productImageUrl->getProductImageUrl($childProduct->getImage()) : '';
            
            $childSku = $childProduct->getSku();
            $productOptions[] = [
                'id'       => ($childSku) ? $childSku : $childProduct->getId(),
                'sku'      => $childSku,
                'name'     => $childProduct->getName(),
                'price'    => $childProduct->getPrice(),
                'imageUrl' => $imageUrl
            ];
        }
        
        return $productOptions;
    }
    
    public function getParentIds($productId)
    {
        return $this->configurableType->getParentIdsByChild($productId);
    }
}

