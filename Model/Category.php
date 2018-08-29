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
class Category
{
    private $ordersTotal     = 0;
    private $totalChunks     = 0;
    private $chunkItems      = 15;
    private $categoriesArray = [];

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * Get chunk category data for import
     *
     * @param int
     *
     * @return string array
     */
    public function getCategories($storeId, $chunkId)
    {
        $categories = $this->getCategoryQuery();

        foreach ($categories as $category) {
            $categoriesArray[] = [
                'id'   => $category->getId(),
                'name' => $category->getName(),
                'url'  => $category->getUrl()
            ];
        }

        return $categoriesArray;
    }

    /**
     * Get category collection
     *
     * @return 
     */
    protected function getCategoryQuery()
    {
        return $this->categoryCollection
                    ->create()
                    ->addAttributeToSelect('name')
                    ->joinTable(
                        ['url' => 'url_rewrite'],
                        'entity_id = entity_id',
                        ['request_path', 'store_id'],
                        ['entity_type' => 'category']
                    );
    }
}
