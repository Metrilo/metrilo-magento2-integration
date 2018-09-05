<?php

namespace Metrilo\Analytics\Model;

class CategoryData
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->categoryCollection = $categoryCollection;
    }

    public function getCategories($storeId)
    {
        $categoriesArray = [];
        $categories = $this->categoryCollection->create()->addAttributeToSelect('name')
                    ->joinTable(
                        ['url' => 'url_rewrite'],
                        'entity_id = entity_id',
                        ['request_path', 'store_id'],
                        ['entity_type' => 'category', 'store_id' => $storeId]
                    );

        foreach ($categories as $category) {
            $categoriesArray[] = [
                'id'   => $category->getId(),
                'name' => $category->getName(),
                'url'  => $category->getUrl()
            ];
        }

        return $categoriesArray;
    }
}