<?php

namespace Metrilo\Analytics\Model;

class ProductData
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Bundle\Model\Product\Type $bundleType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedType,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType


    ) {
        $this->productCollection     = $productCollection;
        $this->storeManager          = $storeManager;
        $this->bundleType            = $bundleType;
        $this->groupedType           = $groupedType;
        $this->configurableType      = $configurableType;
    }

    public function getProducts($storeId)
    {
        $productTypesWithOption = ['configurable', 'bundle', 'grouped'];
        $products = $this->productCollection->create()->addAttributeToSelect('*')->addUrlRewrite()->addStoreFilter($storeId);

        foreach ($products as $product) {
            $productId      = $product->getId();
            $productType    = $product->getTypeId();
            $parentId = '';
            if ($productType == "simple" || $productType == "virtual") {
                $parentId = $this->getParentId($productId, $productType);
            }
            $productOptions = '';
            if (in_array($productType, $productTypesWithOption)) {
                $productOptions = $this->getProductOptions($product); // get product opitons for configurable/bundle/grouped product
            }


            if (!$parentId) {
                $productsArray[] = [
                    'categories' => $product->getCategoryIds(),
                    'id'         => $productId,
                    'sku'        => $product->getSku(),
                    'imageUrl'   => (!empty($product->getImage())) ? $this->getProductImageUrl($product->getImage()) : '',
                    'name'       => $product->getName(),
                    'price'      => (!empty($product->getPrice())) ? $product->getPrice() : 0, // Does not return grouped/bundled parent price
                    'url'        => $this->storeManager->getStore($storeId)->getBaseUrl() . $product->getRequestPath(),
                    'options'    => $productOptions
                ];
            }
        }
        
        return $productsArray;
    }

    protected function getProductOptions($product)
    {
        $productType = $product->getTypeId();

        if ($productType == 'configurable') {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($productType == 'bundle') {
            $childrenProducts = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product), $product);
        } elseif ($productType == 'grouped') {
            $childrenProducts = $product->getTypeInstance()->getAssociatedProductCollection($product)->addAttributeToSelect(['name', 'price', 'image']);
        }

        foreach ($childrenProducts as $childProduct) {
            $productOptions[] = [
                'productId' => $childProduct->getId(),
                'sku'       => $childProduct->getSku(),
                'name'      => $childProduct->getName(),
                'price'     => $childProduct->getPrice(),
                'imageUrl'  => $this->getProductImageUrl($childProduct->getImage())
            ];
        }

        return $productOptions;
    }

    protected function getProductImageUrl($imageUrlRequestPath) {

        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageUrlRequestPath;
    }

    protected function getParentId($productId, $productType) {
        $parentId = $this->groupedType->getParentIdsByChild($productId);
        if (!$parentId) {
            $parentId = $this->bundleType->getParentIdsByChild($productId);
            if (!$parentId) {
                $parentId = $this->configurableType->getParentIdsByChild($productId);
            }
        }

        return $parentId;
    }
}
