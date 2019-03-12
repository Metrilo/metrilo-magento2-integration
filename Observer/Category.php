<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Category implements ObserverInterface
{
    /**
     * @param \Metrilo\Analytics\Helper\Data                    $helper
     * @param \Metrilo\Analytics\Helper\ApiClient               $apiClient
     * @param \Metrilo\Analytics\Helper\CustomerSerializer      $customerSerializer
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data               $helper,
        \Metrilo\Analytics\Helper\ApiClient          $apiClient,
        \Metrilo\Analytics\Helper\CategorySerializer $categorySerializer
    ) {
        $this->helper             = $helper;
        $this->apiClient          = $apiClient;
        $this->categorySerializer = $categorySerializer;
    }
    
    public function execute(Observer $observer)
    {
        try {
            $category = $observer->getEvent()->getCategory();
            if($category->getStoreId() != 0) {
                $client             = $this->apiClient->getClient($category->getStoreId());
                $serializedCategory = $this->categorySerializer->serialize($category);
                
                $client->category($serializedCategory);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }
}
