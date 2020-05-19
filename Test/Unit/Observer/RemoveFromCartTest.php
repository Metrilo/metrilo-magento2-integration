<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Error\Error;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Model\Events\RemoveFromCart as RemoveFromCartEvent;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Observer\RemoveFromCart;

class RemoveFromCartTest extends \PHPUnit\Framework\TestCase
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
     * @var \Metrilo\Analytics\Model\Events\RemoveFromCart
     */
    private $removeFromCartEvent;
    
    /**
     * @var \Metrilo\Analytics\Helper\SessionEvents
     */
    private $sessionEvents;
    
    /**
     * @var \Metrilo\Analytics\Observer\RemoveFromCart
     */
    private $removeFromCartObserver;
    
    private $storeId   = 1;
    private $productId = 2;
    
    public function setUp()
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getQuoteItem', 'getStoreId'])
            ->getMock();
    
        $this->observer->expects($this->any())->method('getEvent')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getQuoteItem')->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getStoreId')->will($this->returnValue($this->storeId));
        
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'logError'])
            ->getMock();
    
        $this->removeFromCartEvent = $this->getMockBuilder(RemoveFromCartEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['callJs'])
            ->getMock();
        
        $this->sessionEvents = $this->getMockBuilder(SessionEvents::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSessionEvent'])
            ->getMock();
    
        $this->removeFromCartObserver = new RemoveFromCart($this->dataHelper, $this->sessionEvents);
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new RemoveFromCart($this->dataHelper, $this->sessionEvents)
        );
    }
    
    public function testExecute()
    {
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->with($this->isType('int'))
            ->will($this->returnValue(true));
        
        $this->removeFromCartEvent->expects($this->any())->method('callJs')
            ->will($this->returnValue("window.metrilo.removeFromCart('', );"));
        
        $this->sessionEvents->expects($this->any())->method('addSessionEvent')
            ->with($this->equalTo($this->removeFromCartEvent->callJs()));
    
        $this->removeFromCartObserver->execute($this->observer);
    }
    
    public function testException()
    {
        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->with($this->isType('string')) // simulation for exception, type should be int
            ->will($this->returnValue(true));
    
        $this->dataHelper->expects($this->any())->method('logError')
            ->with($this->isType('object'))
            ->will($this->returnCallback(function ($error) {
                $this->assertInstanceOf(ExpectationFailedException::class, $error);
            }));
        
        $this->removeFromCartObserver->execute($this->observer);
    }
}
