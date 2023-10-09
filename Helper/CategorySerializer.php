<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class CategorySerializer extends AbstractHelper
{
    private StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function serialize($category)
    {
        $categoryId   = $category->getId();
        $storeId      = $category->getStoreId();
        $storeBaseUrl = $this->storeManager->getStore($storeId)->getBaseUrl(); // Used for multiwebsite config base url

        return array(
            'id'   => $categoryId,
            'name' => $category->getName(),
            'url'  => $storeBaseUrl . $category->getRequestPath()
        );
    }
}
