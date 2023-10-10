<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\IdentifyCustomer as IdentifyCustomerEvent;
use Metrilo\Analytics\Model\Events\IdentifyCustomerFactory;
use Metrilo\Analytics\Observer\IdentifyCustomer;
use PHPUnit\Framework\TestCase;

class IdentifyCustomerTest extends TestCase
{
    private Observer $observer;

    private Data $dataHelper;

    private SessionEvents $sessionEvents;

    private IdentifyCustomer $identifyCustomerObserver;

    private IdentifyCustomerFactory $identifyCustomerFactory;

    public function setUp(): void
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getCustomer', 'getName', 'getEmail', 'getOrder', 'getCustomerEmail'])
            ->getMock();

        $identifyCustomerEvent = $this->getMockBuilder(IdentifyCustomerEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['callJs'])
            ->getMock();

        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'logError'])
            ->getMock();

        $this->sessionEvents = $this->getMockBuilder(SessionEvents::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSessionEvent'])
            ->getMock();

        $this->identifyCustomerFactory = $this->getMockBuilder(IdentifyCustomerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->identifyCustomerFactory->method('create')
                                      ->will($this->returnValue($identifyCustomerEvent));

        $this->identifyCustomerObserver = new IdentifyCustomer(
            $this->dataHelper,
            $this->sessionEvents,
            $this->identifyCustomerFactory
        );
    }

    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new IdentifyCustomer(
                $this->dataHelper,
                $this->sessionEvents,
                $this->identifyCustomerFactory
            )
        );
    }

    public function testExecute()
    {
        $email = 'test@email.com';

        $this->observer->expects($this->any())->method('getEvent')
            ->will($this->returnSelf());
        $this->observer->expects($this->exactly(3))->method('getName')->willReturnOnConsecutiveCalls(
            ['customer_login'],
            ['customer_account_edited'],
            ['sales_order_save_after']
        );

        $this->dataHelper->expects($this->any())->method('isEnabled')
            ->with($this->isType('int'))
            ->will($this->returnValue(true));

        $this->identifyCustomerObserver->execute($this->observer);
        $this->identifyCustomerObserver->execute($this->observer);
        $this->identifyCustomerObserver->execute($this->observer);
    }
}
