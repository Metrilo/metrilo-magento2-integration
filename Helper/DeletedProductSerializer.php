<?php

namespace Metrilo\Analytics\Helper;

class DeletedProductSerializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function serialize($deletedProducts)
    {
        $productBatch = [];
        foreach ($deletedProducts as $product) {
            $productOptions = [];
            
            if ($product['product_type'] == 'configurable') {
                continue;
            }
            
            if ($product['parent_item_id']) {
                $parentProduct = $this->searchForParentById($product['parent_item_id'], $deletedProducts);
                
                if ($parentProduct) {
                    $productOptions[] = [
                        'id'       => $product['product_id'],
                        'sku'      => $product['sku'],
                        'name'     => $product['name'],
                        'price'    => $parentProduct['price'],
                        'imageUrl' => ''
                    ];
                    $product = $parentProduct;
                }
            }
            
            $productBatch[] = [
                'categories' => [],
                'id'         => $product['product_id'],
                'sku'        => $product['sku'],
                'imageUrl'   => '',
                'name'       => $product['name'],
                'price'      => ($product['product_type'] == 'configurable') ? 0 : $product['price'],
                'url'        => '',
                'options'    => $productOptions
            ];
        }
        
        return $productBatch;
    }
    
    private function searchForParentById($parentId, $deletedProducts)
    {
        foreach ($deletedProducts as $index => $product) {
            if ($product['item_id'] === $parentId) {
                return $deletedProducts[$index];
            }
        }
        return false;
    }
}
