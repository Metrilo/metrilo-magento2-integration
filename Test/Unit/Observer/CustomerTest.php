<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Newsletter\Model\Subscriber;
use Metrilo\Analytics\Api\Client;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\CustomerSerializer;
use Metrilo\Analytics\Helper\MetriloCustomer;
use Metrilo\Analytics\Observer\Customer;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $observer;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;
    
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepositoryInterface;
    
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriberModel;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\ApiClient
     */
    private $apiClientHelper;
    
    /**
     * @var \Metrilo\Analytics\Api\Client
     */
    private $client;
    
    /**
     * @var \Metrilo\Analytics\Helper\CustomerSerializer
     */
    private $customerSerializerHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\MetriloCustomer
     */
    private $customerHelper;
    
    /**
     * @var \Metrilo\Analytics\Observer\Customer
     */
    private $customerObserver;
    
    public function setUp()
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getCustomer', 'getName', 'getSubscriber'])
            ->getMock();
    
        $this->customerRepositoryInterface = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList', 'delete', 'deleteById', 'getById', 'get'])
            ->getMock();
    
        $this->groupRepositoryInterface = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList', 'delete', 'deleteById', 'getById'])
            ->getMock();
    
        $this->subscriberModel = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['unsetData', 'loadByCustomerId', 'isSubscribed', ])
            ->getMock();
        
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'logError'])
            ->getMock();
        
        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();
    
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['customer'])
            ->getMock();
    
        $this->customerSerializerHelper = $this->getMockBuilder(CustomerSerializer::class)
            ->disableOriginalConstructor()
//            ->setMethods(['logError'])
            ->getMock();
        
        $this->customerHelper = $this->getMockBuilder(MetriloCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStoreId',
                'getEmail',
                'getCreatedAt',
                'getFirstName',
                'getLastName',
                'getSubscriberStatus',
                'getTags'])
            ->getMock();
        
        $this->customerObserver = new Customer(
            $this->dataHelper,
            $this->apiClientHelper,
            $this->customerSerializerHelper,
            $this->customerRepositoryInterface,
            $this->subscriberModel,
            $this->groupRepositoryInterface
        );
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Customer(
                $this->dataHelper,
                $this->apiClientHelper,
                $this->customerSerializerHelper,
                $this->customerRepositoryInterface,
                $this->subscriberModel,
                $this->groupRepositoryInterface
            )
        );
    }
    
    public function testExecute()
    {
        $storeId = 1;
        
        $this->observer->expects($this->any())->method('getEvent')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getSubscriber')->will($this->returnValue($this->subscriberModel));
        $this->observer->expects($this->at(0))->method('getName')->will($this->returnValue('customer_save_after'));
        $this->observer->expects($this->at(1))->method('getName')->will($this->returnValue('newsletter_subscriber_save_after'));
        $this->observer->expects($this->at(2))->method('getName')->will($this->returnValue('customer_account_edited'));
        $this->observer->expects($this->at(3))->method('getName')->will($this->returnValue('customer_register_success'));
//        $this->observer->expects($this->at(4))->method('getName')->will($this->returnValue('sales_order_save_after')); // not working on index 4, no idea why
        
        $this->dataHelper->expects($this->any())->method('isEnabled')->with($this->isType('int'))
            ->will($this->returnValue(true));
        $this->dataHelper->expects($this->any())->method('logError')->with($this->isType('object'));
    
        $this->apiClientHelper->expects($this->any())->method('getClient')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($this->client));
    
        $this->client->expects($this->any())->method('customer')
            ->with($this->isInstanceOf(CustomerSerializer::class));
    
        $this->customerSerializerHelper->expects($this->any())->method('serialize')
            ->with($this->isInstanceOf(CustomerSerializer::class))
            ->will($this->returnValue([]));
        
        $this->customerObserver->execute($this->observer);
    }
}