<?php

namespace Metrilo\Analytics\Helper;

class DeletedProductSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function serialize($deletedProductOrders)
    {
        $productBatch = [];
        foreach ($deletedProductOrders as $order) {
            foreach ($order->getAllItems() as $item) {
                $parentProduct  = '';
                $productOptions = [];
                
                $parentItemId   = $item->getParentItemId();
                $itemId         = $item->getProductId();
                $itemSku        = $item->getSku();
                $itemName       = $item->getname();
                
                if ($item->getProductType() == 'configurable' || $this->presentInBatch($itemId, $productBatch)) {
                    continue;
                }
                
                if ($parentItemId) {
                    $parentProduct = $order->getItemById($parentItemId);
                    
                    if ($parentProduct) {
                        $productOptions[] = [
                            'id'       => ($itemSku) ? $itemSku : $itemId,
                            'sku'      => $itemSku,
                            'name'     => $itemName,
                            'price'    => $parentProduct->getPrice(),
                            'imageUrl' => ''
                        ];
                        
                        $parentIndex = $this->getProductBatchIndexById($parentProduct->getProductId(), $productBatch);
                        if ($parentIndex !== false) {
                            if ($this->presentInBatchProductOptions($itemId, $productBatch[$parentIndex]['options'])) {
                                continue;
                            }
                            $productBatch[$parentIndex]['options'] = array_merge($productBatch[$parentIndex]['options'], $productOptions);
                            continue;
                        }
                    }
                }
                
                $productBatch[] = [
                    'categories' => [],
                    'id'         => ($parentProduct) ? $parentProduct->getProductId() : $itemId,
                    'sku'        => $itemSku,
                    'imageUrl'   => '',
                    'name'       => ($parentProduct) ? $parentProduct->getName() : $itemName,
                    'price'      => ($parentProduct) ? 0 : $item->getPrice(),
                    'url'        => '',
                    'options'    => $productOptions
                ];
            }
        }
        
        return $productBatch;
    }
    
    private function getProductBatchIndexById($productId, $productBatch)
    {
        return array_search($productId, array_column($productBatch, 'id'));
    }
    
    private function presentInBatch($id, $batch)
    {
        $productIndex = $this->getProductBatchIndexById($id, $batch);
        
        return ($productIndex) ? true : false;
    }
    
    private function presentInBatchProductOptions($id, $batchOptions)
    {
        $productOptionIndex = $this->getProductBatchIndexById($id, $batchOptions);
        
        return ($productOptionIndex) ? true : false;
    }
}
