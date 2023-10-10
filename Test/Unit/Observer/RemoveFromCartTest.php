<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Metrilo\Analytics\Model\Events\RemoveFromCartFactory;
use PHPUnit\Framework\ExpectationFailedException;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Model\Events\RemoveFromCart as RemoveFromCartEvent;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Observer\RemoveFromCart;
use PHPUnit\Framework\TestCase;

class RemoveFromCartTest extends TestCase
{

    private Observer $observer;

    private Data $dataHelper;

    private RemoveFromCartEvent $removeFromCartEvent;

    private SessionEvents $sessionEvents;

    private RemoveFromCartFactory $removeFromCartEventFactory;

    private RemoveFromCart $removeFromCartObserver;

    private $storeId = 1;

    public function setUp(): void
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

        $this->removeFromCartEventFactory = $this->getMockBuilder(RemoveFromCartFactory::class)
                                                 ->disableOriginalConstructor()
                                                 ->getMock();
        $this->removeFromCartEventFactory
            ->method('create')
            ->will($this->returnValue($this->removeFromCartEvent));

        $this->sessionEvents = $this->getMockBuilder(SessionEvents::class)
                                    ->disableOriginalConstructor()
                                    ->setMethods(['addSessionEvent'])
                                    ->getMock();

        $this->removeFromCartObserver = new RemoveFromCart(
            $this->dataHelper,
            $this->sessionEvents,
            $this->removeFromCartEventFactory
        );
    }

    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new RemoveFromCart($this->dataHelper, $this->sessionEvents, $this->removeFromCartEventFactory)
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
                         ->will(
                             $this->returnCallback(function ($error) {
                                 $this->assertInstanceOf(ExpectationFailedException::class, $error);
                             })
                         );

        $this->removeFromCartObserver->execute($this->observer);
    }
}
