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
    private $chunkItems  = 15;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Metrilo\Analytics\Helper\AdminStoreResolver $resolver
    ) {
        $this->orderCollection = $orderCollection;
        $this->resolver = $resolver;
    }

    /**
     * @return int
     */
    public function getTotalChunks()
    {
        return $this->totalChunks;
    }

    /**
     * Get chunk orders
     *
     * @param  int
     * @return
     */
    public function getOrders($storeId, $chunkId)
    {
        return $this->getOrderQuery($storeId)
            ->setPageSize($this->chunkItems)
            ->setCurPage($chunkId + 1);
    }

    /**
     * Chunks array
     *
     * @return int
     */
    public function getChunks($storeId = 0)
    {
        $storeTotal = $this->getOrderQuery($storeId)->getSize();
        return (int) ceil($storeTotal / $this->chunkItems);
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

    /**
     * @param int $storeId
     *
     * @return mixed
     */
    protected function getOrderQuery($storeId = 0)
    {
        return $this->orderCollection->create()->addAttributeToFilter('store_id', $storeId)->setOrder('entity_id', 'asc');
    }
}
