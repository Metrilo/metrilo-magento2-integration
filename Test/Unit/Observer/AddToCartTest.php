<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Session;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\AddToCart as AddToCartEvent;
use Metrilo\Analytics\Model\Events\AddToCartFactory;
use Metrilo\Analytics\Observer\AddToCart;
use PHPUnit\Framework\TestCase;

class AddToCartTest extends TestCase
{
    private Observer $observer;

    private Data $dataHelper;

    private SessionEvents $sessionEvents;

    private AddToCart $addToCartObserver;

    private AddToCartFactory $addToCartFactory;

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

        $catalogSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->sessionEvents = $this->getMockBuilder(SessionEvents::class)
            ->setConstructorArgs([$catalogSession, $context])
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $addToCartEvent = $this->getMockBuilder(AddToCartEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['callJS'])
            ->getMock();

        $this->addToCartFactory = $this->getMockBuilder(AddToCartFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addToCartFactory->method('create')->will($this->returnValue($addToCartEvent));

        $this->addToCartObserver = new AddToCart($this->dataHelper, $this->sessionEvents, $this->addToCartFactory);
    }

    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new AddToCart($this->dataHelper, $this->sessionEvents, $this->addToCartFactory)
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
