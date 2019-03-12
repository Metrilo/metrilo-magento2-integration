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
    const chunkItems = 50;

    public function __construct(
        \Metrilo\Analytics\Model\ProductData $productData
    ) {
        $this->productData = $productData;
    }

    public function getProductChunks($storeId)
    {
        $totalProducts = $this->productData->getProductQuery($storeId)->getSize();
        return (int) ceil($totalProducts / self::chunkItems);
    }
}