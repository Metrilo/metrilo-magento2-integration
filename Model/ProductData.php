<?php

namespace Metrilo\Analytics\Model;

class ProductData
{
    /**
     * Product parent types
     */
    const PARENT_TYPES = [\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE, \Magento\Bundle\Model\Product\Type::TYPE_CODE, \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE];

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Bundle\Model\Product\Type $bundleType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedType,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType
    )
    {
        $this->productCollection = $productCollection;
        $this->storeManager      = $storeManager;
        $this->bundleType        = $bundleType;
        $this->groupedType       = $groupedType;
        $this->configurableType  = $configurableType;
    }

    public function getProductQuery($storeId)
    {
        return $this->productCollection->create()->addAttributeToSelect('*')->addUrlRewrite()->addStoreFilter($storeId);
    }

    public function getProducts($storeId, $chunkId)
    {
        $productsArray = [];
        $products = $this->getProductQuery($storeId)
                         ->setPageSize(\Metrilo\Analytics\Model\Import::chunkItems)
                         ->setCurPage($chunkId + 1);
        
        foreach ($products as $product) {
            $productId = $product->getId();
            $productType = $product->getTypeId();

            if ($productType == "simple" || $productType == "virtual") { //standard simple and virtual products (if product has no weight the system will consider it as virtual) can have parents (be part of configurable/bundle/grouped product).
                if ($this->getParentId($productId, $productType)) { //check if the product is part of configurable/bundle/grouped product
                    continue;
                }
            }

            $imageUrl       = (!empty($product->getImage())) ? $this->getProductImageUrl($product->getImage()) : '';
            $price          = (!empty($product->getPrice())) ? $product->getPrice() : 0; // Does not return grouped/bundled parent price
            $url            = $this->storeManager->getStore($storeId)->getBaseUrl() . $product->getRequestPath();
            $productOptions = (in_array($productType, self::PARENT_TYPES)) ? $this->getProductOptions($product) : [];


            $productsArray[] = [
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

        return $productsArray;
    }

    protected function getProductOptions($product)
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
            $imageUrl = (!empty($childProduct->getImage())) ? $this->getProductImageUrl($childProduct->getImage()) : '';

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

    protected function getProductImageUrl($imageUrlRequestPath)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageUrlRequestPath;
    }

    protected function getParentId($productId, $productType)
    {
        return $this->configurableType->getParentIdsByChild($productId) || $this->bundleType->getParentIdsByChild($productId) || $this->groupedType->getParentIdsByChild($productId);
    }
}
