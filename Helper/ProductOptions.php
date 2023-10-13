<?php

namespace Metrilo\Analytics\Helper;

use Magento\Bundle\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class ProductOptions extends AbstractHelper
{
    private Configurable $configurableType;

    private Grouped $groupedType;

    private Type $bundleType;

    private ProductImageUrl $productImageUrl;

    public function __construct(
        Configurable $configurableType,
        Type $bundleType,
        Grouped $groupedType,
        ProductImageUrl $productImageUrl,
        Context $context
    ) {
        parent::__construct($context);
        $this->configurableType = $configurableType;
        $this->bundleType       = $bundleType;
        $this->groupedType      = $groupedType;
        $this->productImageUrl  = $productImageUrl;
    }

    public function getParentOptions($product)
    {
        $productOptions   = [];
        $productType      = $product->getTypeId();
        $childrenProducts = [];

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
                'price'    => $childProductSpecialPrice ?: $childProduct->getPrice(),
                'imageUrl' => $imageUrl
            ];
        }

        return $productOptions;
    }

    public function getParentIds($productId, $productType)
    {
        $parentIds = [];

        if ($productType === 'configurable') {
            $parentIds = $this->configurableType->getParentIdsByChild($productId);
        } elseif ($productType === 'bundle') {
            $parentIds = $this->bundleType->getParentIdsByChild($productId);
        } elseif ($productType === 'grouped') {
            $parentIds = $this->groupedType->getParentIdsByChild($productId);
        }

        return $parentIds;
    }
}
