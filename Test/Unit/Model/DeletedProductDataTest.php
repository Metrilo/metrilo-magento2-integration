<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;
use Metrilo\Analytics\Model\DeletedProductData;

class DeletedProductDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeletedProductData
     */
    private $deletedProductData;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $orderCollection;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    private $orderItemCollection;
    
    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private $storeId = 1;
    
    public function setUp()
    {
        $this->orderCollection = $this->getMockBuilder(OrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['create','addFieldToFilter'])
            ->getMock();
        
        $this->orderItemCollection = $this->getMockBuilder(OrderItemCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(OrderItemCollection::class), [
                'reset',
                'columns',
                'joinLeft',
                'where',
                'fetchAll']))
            ->getMock();
       
        $this->deletedProductData = new DeletedProductData($this->orderCollection, $this->orderItemCollection);
    }
    
    public function testGetDeletedProductOrders()
    {
        $this->orderItemCollection->expects($this->any())->method('getSelect')
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('distinct')
            ->with($this->isType('bool'))
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('reset')
            ->with($this->isType('string'))
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('columns')
            ->with($this->isType('array'))
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('joinLeft')
            ->with($this->isType('array'), $this->isType('string'), $this->isType('array'))
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('where')
            ->withConsecutive(
                [$this->isType('string')],
                [$this->isType('string'), $this->isType('int')]
            )
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('getConnection')
            ->will($this->returnSelf());
        $this->orderItemCollection->expects($this->any())->method('fetchAll')
            ->with($this->isInstanceOf(OrderItemCollection::class))
            ->will($this->returnValue([1,2,3]));
    
        $deletedProductOrderIds = $this->orderItemCollection->getConnection()->fetchAll($this->orderItemCollection);
        $this->assertEquals([1,2,3], $deletedProductOrderIds);
        
        $this->orderCollection->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('addFieldToFilter')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnSelf());
        
        $this->assertInstanceOf(
            OrderCollection::class,
            $this->deletedProductData->getDeletedProductOrders($this->storeId)
        );
    }
}
