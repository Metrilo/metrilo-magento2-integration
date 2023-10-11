<?php

namespace Metrilo\Analytics\Helper;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class DeletedProductSerializer extends AbstractHelper
{

    public function serialize(Collection $deletedProductOrders): array
    {
        $batch = [];

        /** @var Order $order */
        foreach ($deletedProductOrders as $order) {
            $this->serializeOrder($order, $batch);
        }

        return $batch;
    }

    private function serializeOrder(Order $order, array &$batch): void
    {
        foreach ($order->getAllItems() as $item) {
            if (isset($batch[$item->getProductId()]) && ($this->hasChildren($item) || !$item->getParentItemId())) {
                continue;
            }

            if ($this->hasChildren($item) || !$item->getParentItemId()) {
                $batch[$item->getProductId()] = $this->getRootProductData($item);
            } elseif ($item->getParentItemId()) {
                $parentItem = $order->getItemById($item->getParentItemId());
                if (isset($batch[$parentItem->getProductId()])) {
                    $batch[$parentItem->getProductId()]['options'][$item->getProductId()] =
                        $this->getSimpleOptionData($item, $parentItem);
                } else {
                    $batch[$parentItem->getProductId()] = $this->serializeSimpleWithParent($item, $parentItem);
                }
            }
        }
    }

    private function getSimpleOptionData(Item $item, Item $parentItem): array
    {
        return [
            'id' => $item->getProductId(),
            'sku' => $item->getSku(),
            'name' => $item->getName(),
            'price' => $parentItem->getPrice(),
            'imageUrl' => '',
        ];
    }

    private function getRootProductData(Item $item): array
    {
        return [
            'categories' => [],
            'id' => $item->getProductId(),
            'sku' => $item->getSku(),
            'imageUrl' => '',
            'name' => $item->getName(),
            'price' => $this->hasChildren($item) ? 0 : $item->getPrice(),
            'url' => '',
            'options' => []
        ];
    }

    private function serializeSimpleWithParent(Item $item, Item $parentItem): array
    {
        $data = $this->getRootProductData($parentItem);
        $data['options'][$item->getProductId()] = $this->getSimpleOptionData($item, $parentItem);

        return $data;
    }

    private function hasChildren(Item $item): bool
    {
        return $item->getProductType() === Configurable::TYPE_CODE ||
            $item->getProductType() === Type::TYPE_BUNDLE;
    }
}
