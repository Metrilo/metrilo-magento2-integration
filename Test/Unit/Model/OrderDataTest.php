<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Metrilo\Analytics\Model\OrderData;

class OrderDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $orderCollection;

    /**
     * @var OrderData
     */
    private $orderData;

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
        $this->orderCollection = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CollectionFactory::class), [
                'addAttributeToFilter',
                'addAttributeToSelect',
                'setOrder',
                'setPageSize',
                'setCurPage',
                'getSize']))
            ->getMock();

        $this->orderCollection->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('addAttributeToFilter')
            ->with($this->isType('string'), $this->isType('int'))
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('addAttributeToSelect')
            ->with($this->isType('string'))
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('setOrder')
            ->with($this->isType('string'), $this->isType('string'))
            ->will($this->returnSelf());

        $this->orderData = new OrderData($this->orderCollection);
    }

    public function testGetOrders()
    {
        $this->orderCollection->expects($this->any())->method('setPageSize')
            ->with($this->isType('int'))
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('setCurPage')
            ->with($this->greaterThan($this->chunkId))
            ->will($this->returnSelf());

        $this->assertInstanceOf(CollectionFactory::class, $this->orderData->getOrders($this->storeId, $this->chunkId));
    }

    public function testGetOrderChunks()
    {
        $this->orderCollection->expects($this->any())->method('getSize')->willReturn(1000);

        $this->assertEquals(20, $this->orderData->getOrderChunks($this->storeId));
    }
}