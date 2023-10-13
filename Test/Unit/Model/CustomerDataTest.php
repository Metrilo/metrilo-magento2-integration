<?php

namespace Metrilo\Analytics\Test\Unit\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Api\GroupRepositoryInterface;
use Metrilo\Analytics\Model\CustomerData;
use Metrilo\Analytics\Model\MetriloCustomer;
use Metrilo\Analytics\Model\MetriloCustomerFactory;
use PHPUnit\Framework\TestCase;

class CustomerDataTest extends TestCase
{
    private Collection $customerCollection;
    private CollectionFactory $customerCollectionFactory;

    private CustomerData $customerData;

    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private int $storeId = 1;

    /**
     * @var \Magento\Framework\App\Request\Http->getParam('chunkId')
     */
    private int $chunkId = 1;

    public function setUp(): void
    {
        $this->customerCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'addAttributeToFilter',
                'setPageSize',
                'setCurPage',
                'getSize',
                'getIterator'
            ])
            ->getMock();
        $this->customerCollectionFactory->method('create')->will($this->returnValue($this->customerCollection));

        $this->customerCollection->expects($this->any())->method('addAttributeToFilter')
            ->with($this->isType('string'), $this->isType('int'))
            ->will($this->returnSelf());

        $subscriberModel = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $groupRepositoryInterface = $this->getMockBuilder(GroupRepositoryInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $metriloCustomer = $this->getMockBuilder(MetriloCustomer::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $metriloCustomerFactory = $this->getMockBuilder(MetriloCustomerFactory::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $metriloCustomerFactory->method('create')->will($this->returnValue($metriloCustomer));

        $this->customerData = new CustomerData(
            $this->customerCollectionFactory,
            $subscriberModel,
            $groupRepositoryInterface,
            $metriloCustomerFactory
        );
    }

    public function testGetCustomers()
    {
        $this->customerCollection->expects($this->any())->method('setPageSize')
            ->with($this->isType('int'))
            ->will($this->returnSelf());
        $this->customerCollection->expects($this->any())->method('setCurPage')
            ->with($this->greaterThan($this->chunkId))
            ->will($this->returnSelf());

        $this->customerCollection->expects($this->any())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([])));

        $customers = $this->customerData->getCustomers($this->storeId, $this->chunkId);
        $this->assertContainsOnlyInstancesOf(MetriloCustomer::class, $customers);
    }

    public function testGetCustomerChunks()
    {
        $this->customerCollection->expects($this->any())->method('getSize')->willReturn(1000);

        $this->assertEquals(20, $this->customerData->getCustomerChunks($this->storeId));
    }
}
