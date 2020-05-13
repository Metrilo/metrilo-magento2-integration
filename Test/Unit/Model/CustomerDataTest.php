<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Api\GroupRepositoryInterface;
use Metrilo\Analytics\Model\CustomerData;
use Metrilo\Analytics\Helper\MetriloCustomer;

class CustomerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customerCollection;
    
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriberModel;
    
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepositoryInterface;
    
    /**
     * @var \Metrilo\Analytics\Model\CustomerData
     */
    private $customerData;
    
    /**
     * @var \Metrilo\Analytics\Helper\MetriloCustomer
     */
    private $metriloCustomer;
    
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
    
        $this->subscriberModel = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $this->groupRepositoryInterface = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $this->metriloCustomer = $this->getMockBuilder(MetriloCustomer::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->customerData = new CustomerData($this->customerCollection, $this->subscriberModel, $this->groupRepositoryInterface);
    }
    
    public function testGetCustomers()
    {
        $this->customerCollection->expects($this->any())->method('setPageSize')
            ->with($this->isType('int'))
            ->will($this->returnSelf());
        $this->customerCollection->expects($this->any())->method('setCurPage')
            ->with($this->greaterThan($this->chunkId))
            ->will($this->returnSelf());
        
        $customers = $this->customerData->getCustomers($this->storeId, $this->chunkId);
        $this->assertContainsOnlyInstancesOf(MetriloCustomer::class, $customers);
    }
    
    public function testGetCustomerChunks()
    {
        $this->customerCollection->expects($this->any())->method('getSize')->willReturn(1000);
        
        $this->assertEquals(20, $this->customerData->getCustomerChunks($this->storeId));
    }
}