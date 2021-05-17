<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Api\Client;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\CategorySerializer;
use Metrilo\Analytics\Model\CategoryData;
use Metrilo\Analytics\Observer\Category;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $observer;
    
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
     * @var \Metrilo\Analytics\Helper\CategorySerializer
     */
    private $categorySerializer;
    
    /**
     * @var \Metrilo\Analytics\Model\CategoryData
     */
    private $categoryModel;
    
    /**
     * @var \Metrilo\Analytics\Observer\Category
     */
    private $categoryObserver;
    
    public function setUp(): void
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getCategory', 'getStoreId', 'getStoreIds', 'getId'])
            ->getMock();
    
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreIdsPerProject', 'isEnabled', 'logError'])
            ->getMock();
    
        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();
    
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['category'])
            ->getMock();
    
        $this->categorySerializer = $this->getMockBuilder(CategorySerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
    
        $this->categoryModel = $this->getMockBuilder(CategoryData::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategoryWithRequestPath'])
            ->getMock();
        
        $this->categoryObserver = new Category(
            $this->dataHelper,
            $this->apiClientHelper,
            $this->categorySerializer,
            $this->categoryModel
        );
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Category($this->dataHelper, $this->apiClientHelper, $this->categorySerializer, $this->categoryModel)
        );
    }
    
    public function testExecute()
    {
        $storeId = 1;
        
        $this->observer->expects($this->any())->method('getEvent')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getCategory')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->observer->expects($this->any())->method('getStoreIds')->will($this->returnValue([1,2,3]));
        $this->observer->expects($this->any())->method('getId')->will($this->returnValue(1));
        
        $this->dataHelper->expects($this->any())->method('isEnabled')->with($this->isType('int'))
            ->will($this->returnValue(true));
        $this->dataHelper->expects($this->any())->method('getStoreIdsPerProject')->with($this->isType('int'));
        $this->dataHelper->expects($this->any())->method('logError')->with($this->isType('object'));
    
        $this->apiClientHelper->expects($this->any())->method('getClient')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($this->client));
    
        $this->categorySerializer->expects($this->any())->method('serialize')
            ->with($this->isInstanceOf(CategoryData::class))
            ->will($this->returnValue([]));
    
        $this->categoryModel->expects($this->any())->method('getCategoryWithRequestPath')
            ->with($this->isType('int'), $this->isType('int'));
    
        $this->client->expects($this->any())->method('category')
            ->with($this->isInstanceOf(CategorySerializer::class));
    
        $this->categoryObserver->execute($this->observer);
    }
}
