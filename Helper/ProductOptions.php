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
        $productOptions   = [];
        $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        
        foreach ($childrenProducts as $childProduct) {
            $childImage = $childProduct->getImage();
            $imageUrl = (!empty($childImage)) ? $this->productImageUrl->getProductImageUrl($childImage) : '';
            
            $childProductSku          = $childProduct->getSku();
            $childProductSpecialPrice = $childProduct->getSpecialPrice();
            $productOptions[] = [
                'id'       => $childProductSku ? $childProductSku : $childProduct->getId(),
                'sku'      => $childProductSku,
                'name'     => $childProduct->getName(),
                'price'    => $childProductSpecialPrice ? $childProductSpecialPrice : $childProduct->getPrice(),
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
