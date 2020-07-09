<?php

namespace Metrilo\Analytics\Helper;

class ProductOptions extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Bundle\Model\Product\Type                           $bundleType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped           $groupedType,
        \Metrilo\Analytics\Helper\ProductImageUrl                    $productImageUrl
    ) {
        $this->configurableType = $configurableType;
        $this->bundleType       = $bundleType;
        $this->groupedType      = $groupedType;
        $this->productImageUrl  = $productImageUrl;
    }
    
    public function getParentOptions($product)
    {
        $productOptions = [];
        $productType    = $product->getTypeId();
        
        if ($productType == 'configurable') {
            $childrenProducts = $product->getTypeInstance()
                ->getUsedProducts($product);
        } elseif ($productType == 'bundle') {
            $childrenProducts = $product->getTypeInstance()
                ->getSelectionsCollection(
                    $product->getTypeInstance()->getOptionsIds($product),
                    $product
                );
        } elseif ($productType == 'grouped') {
            $childrenProducts = $product->getTypeInstance()
                ->getAssociatedProductCollection($product)
                ->addAttributeToSelect(['name', 'price', 'image']);
        }
        
        foreach ($childrenProducts as $childProduct) {
            $childImage = $childProduct->getImage();
            $imageUrl = (!empty($childImage)) ? $this->productImageUrl->getProductImageUrl($childImage) : '';
            
            $childProductSpecialPrice = $childProduct->getSpecialPrice();
            $productOptions[] = [
                'id'       => $childProduct->getId(),
                'sku'      => $childProduct->getSku(),
                'name'     => $childProduct->getName(),
                'price'    => $childProductSpecialPrice ? $childProductSpecialPrice : $childProduct->getPrice(),
                'imageUrl' => $imageUrl
            ];
        }
        
        return $productOptions;
    }
    
    public function getParentIds($productId)
    {
        return $this->configurableType->getParentIdsByChild($productId)
            || $this->bundleType->getParentIdsByChild($productId)
            || $this->groupedType->getParentIdsByChild($productId);
    }
}
