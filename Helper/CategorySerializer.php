<?php

namespace Metrilo\Analytics\Helper;

class CategorySerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Metrilo\Analytics\Model\CategoryData      $categoryData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http        $request
    ) {
        $this->categoryData = $categoryData;
        $this->storeManager = $storeManager;
        $this->request      = $request;
    }
    
    public function serialize($category) {
        $categoryId   = $category->getId();
        $storeId      = $category->getStoreId();
        $storeBaseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(); // Used for multiwebsite configuration base url
    
        if ($this->request->getActionName() == 'save') {
            $categoryUrl = $storeBaseUrl . $this->categoryData->getCategoryRequestPath($categoryId, $storeId);
        } else {
            $categoryUrl = $storeBaseUrl . $category->getRequestPath();
        }
        
        $serializedCategory = [
            'id'   => $categoryId,
            'name' => $category->getName(),
            'url'  => $categoryUrl
        ];
        
        return $serializedCategory;
    }
}

