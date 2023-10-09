<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Session;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\AddToCart as AddToCartEvent;
use Metrilo\Analytics\Observer\AddToCart;

class AddToCartTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;
    
    /**
     * @var \Metrilo\Analytics\Helper\SessionEvents
     */
    private $sessionEvents;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\AddToCart
     */
    private $addToCartEvent;
    
    /**
     * @var \Metrilo\Analytics\Observer\AddToCart
     */
    private $addToCartObserver;
    
    public function setUp(): void
    {
        
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getProduct', 'getStoreId', 'getQuoteItem'])
            ->getMock();
    
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'logError'])
            ->getMock();
    
        $this->catalogSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->sessionEvents = $this->getMockBuilder(SessionEvents::class)
            ->setConstructorArgs([$this->catalogSession])
            ->setMethods(['getData', 'setData'])
            ->getMock();
    
        $this->addToCartEvent = $this->getMockBuilder(AddToCart::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
    
        $this->addToCartObserver = new AddToCart($this->dataHelper, $this->sessionEvents);
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new AddToCart($this->dataHelper, $this->sessionEvents)
        );
    }
    
    public function testExecute()
    {
        $storeId = 1;
        
        $this->observer->expects($this->any())->method('getEvent')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getProduct')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->observer->expects($this->any())->method('getQuoteItem')->will($this->returnSelf());
    
        $this->dataHelper->expects($this->any())->method('isEnabled')->with($this->isType('int'))
            ->will($this->returnValue(true));
        $this->dataHelper->expects($this->any())->method('logError')->with($this->isType('object'));
    
        $this->addToCartObserver->execute($this->observer);
        
        $this->assertEquals([], $this->sessionEvents->getSessionEvents());
    }
}
