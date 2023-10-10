<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Metrilo\Analytics\Model\ProductData;

class ProductDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * @var ProductData
     */
    private $productData;

    /**
     * @var \Magento\Catalog\Model\Product->getId()
     */
    private $productId = 1;

    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private $storeId = 1;

    /**
     * @var \Magento\Framework\App\Request\Http->getParam('chunkId')
     */
    private $chunkId = 1;

    public function setUp(): void
    {
        $this->productCollection = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CollectionFactory::class), [
                'addUrlRewrite',
                'addAttributeToSelect',
                'addAttributeToFilter',
                'addStoreFilter',
                'setPageSize',
                'setCurPage',
                'setDataToAll',
                'getSize',
                'joinTable',
                'setStoreId',
                'getFirstItem']))
            ->getMock();

        $this->productCollection->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('addUrlRewrite')
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('addAttributeToFilter')
            ->with($this->isType('string'), $this->isType('int'))
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('addStoreFilter')
            ->with($this->isType('int'))
            ->will($this->returnSelf());

        $this->productData = new ProductData($this->productCollection);
    }

    public function testGetProducts()
    {
        $this->productCollection->expects($this->any())->method('addAttributeToSelect')
            ->with([
                'entity_id',
                'type_id',
                'sku',
                'created_at',
                'updated_at',
                'name',
                'image',
                'price',
                'special_price',
                'request_path',
                'visibility'])
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('setPageSize')
            ->with($this->isType('int'))
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('setCurPage')
            ->with($this->greaterThan($this->chunkId))
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('setDataToAll')
            ->with($this->isType('string'), $this->isType('int'))
            ->will($this->returnSelf());

        $this->assertInstanceOf(
            CollectionFactory::class,
            $this->productData->getProducts($this->storeId, $this->chunkId)
        );
    }

    public function testGetProductChunk()
    {
        $this->productCollection->expects($this->any())->method('addAttributeToSelect')
            ->with([
                'entity_id',
                'type_id',
                'sku',
                'created_at',
                'updated_at',
                'name',
                'image',
                'price',
                'special_price',
                'request_path',
                'visibility'])
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('getSize')->willReturn(1000);

        $this->assertEquals(20, $this->productData->getProductChunks($this->storeId));
    }

    public function testGetProductWithRequestPath()
    {
        $this->productCollection->expects($this->any())->method('addAttributeToSelect')
            ->with([
                'name',
                'price',
                'image',
                'special_price'
            ])
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('joinTable')
            ->with(
                $this->isType('array'),
                $this->isType('string'),
                $this->isType('array'),
                $this->isType('array')
            )
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('getFirstItem')->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('setStoreId')
            ->with($this->isType('int'))
            ->will($this->returnSelf());

        $productObject = $this->productData->getProductWithRequestPath($this->productId, $this->storeId);

        $this->assertInstanceOf(CollectionFactory::class, $productObject);
    }
}
