<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderModel;
use Metrilo\Analytics\Api\Client;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\OrderSerializer;
use Metrilo\Analytics\Observer\Order;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $observer;
    
    /**
     * @var \Metrilo\Analytics\Api\Client
     */
    private $client;
    
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderModel;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\ApiClient
     */
    private $apiClientHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\OrderSerializer
     */
    private $orderSerializer;
    
    /**
     * @var \Metrilo\Analytics\Observer\Order
     */
    private $orderObserver;
    
    public function setUp()
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getOrder'])
            ->getMock();
    
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['order'])
            ->getMock();
    
        $this->orderModel = $this->getMockBuilder(OrderModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();
        
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'logError'])
            ->getMock();
        
        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();
    
        $this->orderSerializer = $this->getMockBuilder(OrderSerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
        
        $this->orderObserver = new Order(
            $this->dataHelper,
            $this->apiClientHelper,
            $this->orderSerializer
        );
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Order(
                $this->dataHelper,
                $this->apiClientHelper,
                $this->orderSerializer
            )
        );
    }
    
    public function testExecute()
    {
        $storeId = 1;
        
        $this->observer->expects($this->any())->method('getEvent')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getOrder')->will($this->returnValue($this->orderModel));
        
        $this->orderModel->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        
        $this->dataHelper->expects($this->any())->method('isEnabled')->with($this->isType('int'))
            ->will($this->returnValue(true));
    
        $this->apiClientHelper->expects($this->any())->method('getClient')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($this->client));
    
        $this->client->expects($this->any())->method('order')
            ->with($this->isInstanceOf(OrderSerializer::class));
    
        $this->orderSerializer->expects($this->any())->method('serialize')
            ->with($this->isInstanceOf(OrderSerializer::class))
            ->will($this->returnValue([]));
        
        $this->orderObserver->execute($this->observer);
    }
}