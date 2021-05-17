<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Metrilo\Analytics\Helper\MetriloCustomer;
use Metrilo\Analytics\Helper\CustomerSerializer;

class CustomerSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;
    
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customerCollection;
    
    /**
     * @var \Metrilo\Analytics\Helper\MetriloCustomer
     */
    private $metriloCustomer;
    
    /**
     * @var \Metrilo\Analytics\Helper\CustomerSerializer
     */
    private $customerSerializer;
    
    public function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->customerCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEmail', 'getCreatedAt', 'getFirstName', 'getLastName', 'getSubscriberStatus', 'getTags'])
            ->getMock();
        
        $this->metriloCustomer = $this->getMockBuilder(MetriloCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEmail', 'getCreatedAt', 'getFirstName', 'getLastName', 'getSubscriberStatus', 'getTags'])
            ->getMock();
        
        $this->customerSerializer = new CustomerSerializer($this->context);
    }
    
    public function testSerialize()
    {
        $this->customerCollection->expects($this->any())->method('getEmail')
            ->will($this->returnValue('customer@email.com'));
        $this->customerCollection->expects($this->any())->method('getCreatedAt')
            ->will($this->returnValue('date: 02.25.20'));
        $this->customerCollection->expects($this->any())->method('getFirstName')
            ->will($this->returnValue('firstName'));
        $this->customerCollection->expects($this->any())->method('getLastName')
            ->will($this->returnValue('lastName'));
        $this->customerCollection->expects($this->any())->method('getSubscriberStatus')
            ->will($this->returnValue(true));
        $this->customerCollection->expects($this->any())->method('getTags')
            ->will($this->returnValue('customerGroup'));
    
        $this->metriloCustomer->expects($this->any())->method('getEmail')
            ->will($this->returnValue('metriloCustomer@email.com'));
        $this->metriloCustomer->expects($this->any())->method('getCreatedAt')
            ->will($this->returnValue('metriloDate: 02.25.20'));
        $this->metriloCustomer->expects($this->any())->method('getFirstName')
            ->will($this->returnValue('metriloFirstName'));
        $this->metriloCustomer->expects($this->any())->method('getLastName')
            ->will($this->returnValue('metriloLastName'));
        $this->metriloCustomer->expects($this->any())->method('getSubscriberStatus')
            ->will($this->returnValue(false));
        $this->metriloCustomer->expects($this->any())->method('getTags')
            ->will($this->returnValue('metriloCustomerGroup'));
    
        $expectedCustomerCollection = [
            'email'       => 'customer@email.com',
            'createdAt'   => 'date: 02.25.20',
            'firstName'   => 'firstName',
            'lastName'    => 'lastName',
            'subscribed'  => true,
            'tags'        => 'customerGroup'
        ];
    
        $expectedMetriloCustomer = [
            'email'       => 'metriloCustomer@email.com',
            'createdAt'   => 'metriloDate: 02.25.20',
            'firstName'   => 'metriloFirstName',
            'lastName'    => 'metriloLastName',
            'subscribed'  => false,
            'tags'        => 'metriloCustomerGroup'
        ];
    
        $this->assertEquals(
            $expectedCustomerCollection,
            $this->customerSerializer->serialize($this->customerCollection)
        );
        $this->assertEquals(
            $expectedMetriloCustomer,
            $this->customerSerializer->serialize($this->metriloCustomer)
        );
    }
}
