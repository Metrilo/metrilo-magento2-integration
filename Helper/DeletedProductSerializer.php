<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class DeletedProductSerializer extends AbstractHelper
{
    public function serialize($deletedProductOrders)
    {
        $productBatch = [];

        foreach ($deletedProductOrders as $order) {
            foreach ($order->getAllItems() as $item) {
                if ($this->shouldSkipItem($item, $productBatch)) {
                    continue;
                }

                $productOptions = $this->getProductOptions($item, $order, $productBatch);

                $productBatch[] = [
                    'categories' => [],
                    'id'         => $this->getProductId($item, $order),
                    'sku'        => $item->getSku(),
                    'imageUrl'   => '',
                    'name'       => $this->getProductName($item, $order),
                    'price'      => $this->getProductPrice($item, $order),
                    'url'        => '',
                    'options'    => $productOptions,
                ];
            }
        }

        return $productBatch;
    }

    private function shouldSkipItem($item, $productBatch)
    {
        return $item->getProductType() === 'configurable' ||
            $this->isProductInBatch($item->getProductId(), $productBatch);
    }

    private function isProductInBatch($productId, $productBatch)
    {
        return $this->getProductBatchIndexById($productId, $productBatch) !== false;
    }

    private function getProductOptions($item, $order, &$productBatch)
    {
        $productOptions = [];
        $parentItemId = $item->getParentItemId();
        $itemId = $item->getProductId();

        if ($parentItemId) {
            $parentProduct = $order->getItemById($parentItemId);

            if ($parentProduct) {
                $productOptions[] = [
                    'id'       => $itemId,
                    'sku'      => $item->getSku(),
                    'name'     => $item->getName(),
                    'price'    => $parentProduct->getPrice(),
                    'imageUrl' => '',
                ];

                $parentIndex = $this->getProductBatchIndexById($parentProduct->getProductId(), $productBatch);
                if ($parentIndex !== false) {
                    if (!$this->isProductInBatchProductOptions($itemId, $productBatch[$parentIndex]['options'])) {
                        $productBatch[$parentIndex]['options'] = array_merge(
                            $productBatch[$parentIndex]['options'],
                            $productOptions
                        );
                    }
                }
            }
        }

        return $productOptions;
    }

    private function getProductId($item, $order)
    {
        $parentProduct = $order->getItemById($item->getParentItemId());
        return $parentProduct ? $parentProduct->getProductId() : $item->getProductId();
    }

    private function getProductName($item, $order)
    {
        $parentProduct = $order->getItemById($item->getParentItemId());
        return $parentProduct ? $parentProduct->getName() : $item->getName();
    }

    private function getProductPrice($item, $order)
    {
        return $order->getItemById($item->getParentItemId()) ? 0 : $item->getPrice();
    }

    private function getProductBatchIndexById($productId, $productBatch)
    {
        return array_search($productId, array_column($productBatch, 'id'));
    }

    private function isProductInBatchProductOptions($id, $batchOptions)
    {
        return $this->getProductBatchIndexById($id, $batchOptions) !== false;
    }
}
