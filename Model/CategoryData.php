<?php
/**
 * @author Nedelin Slavov <ned@metrilo.com>
 */

namespace Metrilo\Analytics\Model;

/**
 * Model getting orders by chunks for Metrilo import
 *
 * @author Nedelin Slavov <ned@metrilo.com>
 */
class CategoryData
{
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * Get chunk category data for import
     *
     * @return json array
     */
    public function getCategories()
    {
        $categoriesArray = [];
        $categories = $this->categoryCollection->create()->addAttributeToSelect('name')
                    ->joinTable(
                        ['url' => 'url_rewrite'],
                        'entity_id = entity_id',
                        ['request_path', 'store_id'],
                        ['entity_type' => 'category']
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