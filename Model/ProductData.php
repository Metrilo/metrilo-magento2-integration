<?php

namespace Metrilo\Analytics\Model;

class ProductData
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productCollection = $productCollection;
        $this->storeManager      = $storeManager;
    }

    public function getProducts($storeId)
    {
        $productsArray = [];
        $productOptions = '';
        $products = $this->productCollection->create()->addAttributeToSelect('*')->addUrlRewrite()->addStoreFilter($storeId);

        foreach ($products as $product) {
            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped') {
                $productOptions = $this->getProductOptions($product, $storeId); // get product opitons for configurable/bundle/grouped product
            }

            if($product->isVisibleInSiteVisibility()) { // CHECK FOR PRODUCT CATALOG VISIBILITY
                $productsArray[] = [
                    'categories' => $product->getCategoryIds(),
                    'id'         => $product->getId(),
                    'sku'        => $product->getSku(),
                    'imageUrl'   => (!empty($product->getImage())) ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : '',
                    'name'       => $product->getName(),
                    'price'      => (!empty($product->getPrice())) ? $product->getPrice() : 0, // Does not return grouped/bundled parent price
                    'url'        => $this->storeManager->getStore($storeId)->getBaseUrl() . $product->getRequestPath(),
                    'options'    => $productOptions
                ];
            }
        }
        
        return $productsArray;
    }

    protected function getProductOptions($product, $storeId)
    {
        $productOptions = [];
        $childrenProducts = '';

        if ($product->getTypeId() == 'configurable') {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($product->getTypeId() == 'bundle') {
            $childrenProducts = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product), $product);
        } elseif ($product->getTypeId() == 'grouped') {
            $childrenProducts = $product->getTypeInstance()->getAssociatedProductCollection($product)->addAttributeToSelect(['name', 'price', 'image']);
        }

        foreach ($childrenProducts as $childProduct) {
            $productOptions[] = [
                'productId' => $childProduct->getId(),
                'sku'       => $childProduct->getSku(),
                'name'      => $childProduct->getName(),
                'price'     => $childProduct->getPrice(),
                'imageUrl'  => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $childProduct->getImage()
            ];
        }

        return $productOptions;
    }
}
