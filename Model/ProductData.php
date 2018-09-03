<?php
/**
 * @author Nedelin Slavov <ned@metrilo.com>
 */

namespace Metrilo\Analytics\Model;

/**
 * Model getting products data for Metrilo import
 *
 * @author Nedelin Slavov <ned@metrilo.com>
 */
class ProductData
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productCollection = $productCollection;
        $this->storeManager      = $storeManager;
    }

    /**
     * Get products data for import
     *
     * @return array
     */
    public function getProducts()
    {
        $productsArray = [];
        $products = $this->productCollection->create()->addAttributeToSelect('*');

        foreach ($products as $product) {
            $productOptions = [];

            if($product->isVisibleInSiteVisibility()) { // CHECK FOR PRODUCT CATALOG VISIBILITY
                $productOptions  = $this->getProductOptions($product); // get product opitons for configurable/bundle/grouped product
                $productsArray[] = [
                    'categories' => $product->getCategoryIds(),
                    'id'         => $product->getId(),
                    'sku'        => $product->getSku(),
                    'imageUrl'   => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
                    'name'       => $product->getName(),
                    'price'      => ($product->getTypeId() == 'configurable' || 'bundle') ? number_format($product->getFinalPrice(), 4) : $product->getPrice(), // Does not return grouped/bundled parent price
                    'url'        => $product->getProductUrl(),
                    'options'    => $productOptions
                ];
            }     
        }
        
        return $productsArray;
    }

    /**
     * Get product options if it's configurable/bundle/grouped product
     *
     * @param object $product
     *
     * @return array
     */
    protected function getProductOptions($product)
    {
        $productOptions = [];

        if ($product->getTypeId() == 'configurable') {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
            $productOptions   = $this->getChildProducts($childrenProducts);
        } elseif ($product->getTypeId() == 'bundle') {
            $childrenProducts = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product), $product);
            $productOptions   = $this->getChildProducts($childrenProducts);
        } elseif ($product->getTypeId() == 'grouped') {
            $childrenProducts = $product->getTypeInstance()->getAssociatedProductCollection($product)->addAttributeToSelect(['name', 'price', 'image']);
            $productOptions   = $this->getChildProducts($childrenProducts);
        }

        return $productOptions;
    }

    /**
     * Get child products of configurable/bundle/grouped product
     *
     * @param object $childrenProducts
     *
     * @return array
     */
    protected function getChildProducts($childrenProducts) {
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
