<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Metrilo\Analytics\Model\CustomerData;

class CustomerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerData
     */
    private $customerData;
    
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customerCollection;
    
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
        $this->customerCollection = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CollectionFactory::class), [
                'addAttributeToFilter',
                'setPageSize',
                'setCurPage',
                'getSize']))
            ->getMock();
    
        $this->customerCollection->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->customerCollection->expects($this->any())->method('addAttributeToFilter')
            ->with($this->isType('string'), $this->isType('int'))
            ->will($this->returnSelf());
        
        $this->customerData = new CustomerData($this->customerCollection);
    }
    
    public function testGetCustomers()
    {
        $this->customerCollection->expects($this->any())->method('setPageSize')
            ->with($this->isType('int'))
            ->will($this->returnSelf());
        $this->customerCollection->expects($this->any())->method('setCurPage')
            ->with($this->greaterThan($this->chunkId))
            ->will($this->returnSelf());
    
        $this->assertInstanceOf(CollectionFactory::class, $this->customerData->getCustomers($this->storeId, $this->chunkId));
    }
    
    public function testGetCustomerChunks()
    {
        $this->customerCollection->expects($this->any())->method('getSize')->willReturn(1000);
        
        $this->assertEquals(20, $this->customerData->getCustomerChunks($this->storeId));
    }
}