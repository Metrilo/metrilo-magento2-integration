<?php

namespace Metrilo\Analytics\Helper;

class DeletedProductSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function serialize($deletedProductOrders) {
        $productBatch = [];
        foreach ($deletedProductOrders as $order) {
            $items = $order->getAllItems();
            
            foreach ($items as $item) {
                $parentProduct = '';
                $productOptions = [];
    
                if ($item->getProductType() == 'configurable') {
                    continue;
                } else {
                    if ($this->checkForProductIdIndex($item->getProductId(), $productBatch) !== false) {
                        continue;
                    }
                }
    
                if ($item->getParentItemId()) {
                    $parentProduct = $order->getItemById($item->getParentItemId());
    
                    if ($parentProduct) {
                        $productOptions[] = [
                            'id'       => $item->getProductId(),
                            'sku'      => $item->getSku(),
                            'name'     => $item->getName(),
                            'price'    => $parentProduct->getPrice(),
                            'imageUrl' => ''
                        ];
                        
                        $parentIndex = $this->checkForProductIdIndex($parentProduct->getProductId(), $productBatch);
                        if ($parentIndex != false) {
                            if ($this->checkForProductIdIndex($item->getProductId(), $productBatch[$parentIndex]['options']) !== false) {
                                continue;
                            }
                            $productBatch[$parentIndex]['options'] = array_merge($productBatch[$parentIndex]['options'], $productOptions);
                            continue;
                        }
                    }
                }
                
                $productBatch[] = [
                    'categories' => [],
                    'id'         => ($parentProduct) ? $parentProduct->getProductId() : $item->getProductId(),
                    'sku'        => $item->getSku(),
                    'imageUrl'   => '',
                    'name'       => ($parentProduct) ? $parentProduct->getName() : $item->getName(),
                    'price'      => ($parentProduct) ? 0 : $item->getPrice(),
                    'url'        => '',
                    'options'    => $productOptions
                ];
            }
        }
        
        return $productBatch;
    }
    
    private function checkForProductIdIndex($productId, $array)
    {
        return array_search($productId, array_column($array, 'id'));
    }
}
