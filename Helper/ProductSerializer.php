<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class ProductSerializer extends AbstractHelper
{
    private ProductOptions $productOptions;

    private StoreManagerInterface $storeManager;

    private ProductImageUrl $productImageUrl;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductOptions $productOptions
     * @param ProductImageUrl $productImageUrl
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductOptions $productOptions,
        ProductImageUrl $productImageUrl,
        Context $context
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->productOptions = $productOptions;
        $this->productImageUrl = $productImageUrl;
    }

    public function serialize($product)
    {
        $storeId = $product->getStoreId();
        $productId = $product->getId();
        $productType = $product->getTypeId();

        if ($productType === 'simple' && $this->productOptions->getParentIds($productId, $productType) != []) {
            return false;
        }

        $productImage = $product->getImage();
        $productPrice = $product->getPrice();
        $imageUrl = (!empty($productImage)) ? $this->productImageUrl->getProductImageUrl($productImage) : '';
        $price = (!empty($productPrice)) ? $productPrice : 0; // Does not return grouped/bundled parent price
        $specialPrice = $product->getSpecialPrice();
        $url = $this->storeManager->getStore($storeId)->getBaseUrl() . $product->getRequestPath();

        if ($productType === 'configurable' || $productType === 'bundle' || $productType === 'grouped') {
            $productOptions = $this->productOptions->getParentOptions($product);
        } else {
            $productOptions = [];
        }

        return [
            'categories' => $product->getCategoryIds(),
            'id' => $productId,
            'sku' => $product->getSku(),
            'imageUrl' => $imageUrl,
            'name' => $product->getName(),
            'price' => $specialPrice ? $specialPrice : $price,
            'url' => $url,
            'options' => $productOptions
        ];
    }
}
