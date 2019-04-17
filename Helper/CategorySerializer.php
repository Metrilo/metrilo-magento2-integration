<?php

namespace Metrilo\Analytics\Helper;

class CategorySerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    
    public function serialize($category) {
        $categoryId   = $category->getId();
        $storeId      = $category->getStoreId();
        $storeBaseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(); // Used for multiwebsite configuration base url
        
        return array(
            'id'   => $categoryId,
            'name' => $category->getName(),
            'url'  => $storeBaseUrl . $category->getRequestPath()
        );
    }
}

