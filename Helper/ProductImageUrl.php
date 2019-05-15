<?php

namespace Metrilo\Analytics\Helper;

class ProductImageUrl extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    
    public function getProductImageUrl($imageUrlRequestPath)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imageUrlRequestPath;
    }
}