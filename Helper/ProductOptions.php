<?php

namespace Metrilo\Analytics\Helper;

class ProductOptions extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Product parent types
     */
    const PARENT_TYPES = [\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE, \Magento\Bundle\Model\Product\Type::TYPE_CODE, \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE];
    
    public function __construct(
        \Magento\Bundle\Model\Product\Type                           $bundleType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped           $groupedType,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Metrilo\Analytics\Helper\ProductImageUrl                    $productImageUrl
    ) {
        $this->bundleType       = $bundleType;
        $this->groupedType      = $groupedType;
        $this->configurableType = $configurableType;
        $this->productImageUrl  = $productImageUrl;
    }
    
    public function getProductOptions($product)
    {
        $storeId     = $product->getStoreId();
        $productId   = $product->getId();
        $productType = $product->getTypeId();
        
        return (in_array($productType, self::PARENT_TYPES)) ? $this->getOptions($product) : [];
    }
    
    public function getOptions($product)
    {
        $productOptions = [];
        $productType = $product->getTypeId();
        
        if ($productType == 'configurable') {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($productType == 'bundle') {
            $childrenProducts = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product), $product);
        } elseif ($productType == 'grouped') {
            $childrenProducts = $product->getTypeInstance()->getAssociatedProductCollection($product)->addAttributeToSelect(['name', 'price', 'image']);
        }
        
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
    
    public function getParentId($productId)
    {
        return $this->configurableType->getParentIdsByChild($productId) || $this->bundleType->getParentIdsByChild($productId) || $this->groupedType->getParentIdsByChild($productId);
    }
    
    public function checkForParentId($productId)
    {
        $configurableParentId = $this->configurableType->getParentIdsByChild($productId);
        $bundleParentId       = $this->bundleType->getParentIdsByChild($productId);
        $groupedParentId      = $this->groupedType->getParentIdsByChild($productId);
        
        if ($configurableParentId) {
            $productId = $configurableParentId;
        }
        
        if ($bundleParentId) {
            $productId = $bundleParentId;
        }
        
        if ($groupedParentId) {
            $productId = $groupedParentId;
        }
        
        if (is_array($productId)) {
            return $productId[0];
        } else {
            return $productId;
        }
    }
}

