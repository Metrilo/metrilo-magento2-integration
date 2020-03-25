<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Metrilo\Analytics\Model\CategoryData;

class CategoryDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryData
     */
    private $categoryData;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;
    
    /**
     * @var \Magento\Catalog\Model\Category->getId()
     */
    private $categoryId = 1;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data::chunkItems
     */
    private $chunkItems = 50;
    
    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private $storeId = 1;
    
    /**
     * @var \Magento\Framework\App\Request\Http->getParam('chunkId')
     */
    private $chunkId = 1;
    
    public function setUp()
    {
        $this->categoryCollection = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CollectionFactory::class), [
                'addAttributeToSelect',
                'joinTable',
                'setPageSize',
                'setCurPage',
                'getSize',
                'addAttributeToFilter',
                'setStore',
                'addUrlRewriteToResult',
                'getFirstItem',
                'setStoreId']))
            ->getMock();
    
        $this->categoryCollection->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('addAttributeToSelect')
            ->with($this->isType('string'))
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('joinTable')
            ->with($this->isType('array'), $this->isType('string'), $this->isType('array'), $this->isType('array'))
            ->will($this->returnSelf());
        
        $this->categoryData = new CategoryData($this->categoryCollection);
    }
    
    public function testGetCategories()
    {
        $this->categoryCollection->expects($this->any())->method('setPageSize')
            ->with($this->equalTo($this->chunkItems))
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('setCurPage')
            ->with($this->equalTo($this->chunkId + 1))
            ->will($this->returnSelf());
        
        $this->assertInstanceOf(CollectionFactory::class, $this->categoryData->getCategories($this->storeId, $this->chunkId));
    }
    
    public function testGetCategoryChunks()
    {
        $this->categoryCollection->expects($this->any())->method('getSize')->willReturn(1000);
        
        $this->assertEquals(20, $this->categoryData->getCategoryChunks($this->storeId));
    }
    
    public function testGetCategoryWithRequestPath()
    {
        $this->categoryCollection->expects($this->any())->method('addAttributeToFilter')
            ->with($this->isType('string'), $this->equalTo($this->categoryId))
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('setStore')
            ->with($this->equalTo($this->storeId))
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('addUrlRewriteToResult')
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('getFirstItem')
            ->will($this->returnSelf());
        $this->categoryCollection->expects($this->any())->method('setStoreId')
            ->with($this->equalTo($this->storeId))
            ->will($this->returnSelf());
        
        $this->assertInstanceOf(CollectionFactory::class, $this->categoryData->getCategoryWithRequestPath($this->categoryId, $this->storeId));
    }
}