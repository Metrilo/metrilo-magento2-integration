<?php
/**
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */

namespace Metrilo\Analytics\Model;

/**
 * Model getting orders by chunks for Metrilo import
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Import
{
    private $ordersTotal = 0;
    private $totalChunks = 0;
    public  $chunkItems  = 50;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Metrilo\Analytics\Model\CustomerData $customerData,
        \Metrilo\Analytics\Model\CategoryData $categoryData,
        \Metrilo\Analytics\Model\ProductData $productData,
        \Metrilo\Analytics\Model\OrderData $orderData,
        \Metrilo\Analytics\Helper\AdminStoreResolver $resolver
    ) {
        $this->orderCollection = $orderCollection;
        $this->customerData    = $customerData;
        $this->categoryData    = $categoryData;
        $this->productData     = $productData;
        $this->orderData       = $orderData;
        $this->resolver        = $resolver;
    }

    public function getCustomerChunks($storeId)
    {
        $totalCustomers = $this->customerData->getCustomerQuery($storeId)->getSize();
        return (int) ceil($totalCustomers /     $this->chunkItems);
    }

    public function getCategoryChunks($storeId)
    {
        $totalCategories = $this->categoryData->getCategoryQuery($storeId)->getSize();
        return (int) ceil($totalCategories / $this->chunkItems);
    }
    
    public function getProductChunks($storeId)
    {
        $totalProducts = $this->productData->getProductQuery($storeId)->getSize();
        return (int) ceil($totalProducts / $this->chunkItems);
    }

    public function getOrderChunks($storeId)
    {
        $totalOrders = $this->orderData->getOrderQuery($storeId)->getSize();
        return (int) ceil($totalOrders / $this->chunkItems);
    }

    /**
     * Get contextual store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return (int) $this->resolver->getAdminStoreId();
    }
}
