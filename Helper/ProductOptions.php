<?php

namespace Metrilo\Analytics\Helper;

class ProductOptions extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Bundle\Model\Product\Type                           $bundleType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped           $groupedType,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Metrilo\Analytics\Helper\ProductImageUrl                    $productImageUrl
    ) {
        $this->bundleType       = $bundleType; // needed only for parent check on row 43
        $this->groupedType      = $groupedType; // needed only for parent check on row 43
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
            
            $productOptions[] = [
                'id'       => $childProduct->getId(),
                'sku'      => $childProduct->getSku(),
                'name'     => $childProduct->getName(),
                'price'    => $childProduct->getPrice(),
                'imageUrl' => $imageUrl
            ];
        }
        
        return $productOptions;
    }
    
    public function checkForParentId($productId)
    {
        return $this->configurableType->getParentIdsByChild($productId) || $this->bundleType->getParentIdsByChild($productId) || $this->groupedType->getParentIdsByChild($productId);
    }
    
    public function getParentIds($productId)
    {
        $configurableParentIds = $this->configurableType->getParentIdsByChild($productId);
        
            if ($configurableParentIds) {
                $parentIds = $configurableParentIds;
            } else {
                $parentIds[] = $productId; // Magento products can have multiple parents, if there is a single parent return it as array
            }
        
        return $parentIds;
    }
}

