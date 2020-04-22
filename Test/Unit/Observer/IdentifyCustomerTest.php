<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Metrilo\Analytics\Model\Events\IdentifyCustomer as IdentifyCustomerEvent;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Observer\IdentifyCustomer;

class IdentifyCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $observer;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\IdentifyCustomer
     */
    private $identifyCustomerEvent;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\SessionEvents
     */
    private $sessionEvents;
    
    /**
     * @var \Metrilo\Analytics\Observer\Customer
     */
    private $identifyCustomerObserver;
    
    public function setUp()
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent', 'getCustomer', 'getName', 'getEmail', 'getOrder', 'getCustomerEmail'])
            ->getMock();
    
        $this->identifyCustomerEvent = $this->getMockBuilder(IdentifyCustomer::class)
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
        
        $this->identifyCustomerObserver = new IdentifyCustomer(
            $this->dataHelper,
            $this->sessionEvents
        );
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new IdentifyCustomer(
                $this->dataHelper,
                $this->sessionEvents
            )
        );
    }
    
    public function testExecute()
    {
        $email = 'test@email.com';
    
        $this->observer->expects($this->any())->method('getEvent')->will($this->returnSelf());
        $this->observer->expects($this->at(0))->method('getName')->will($this->returnValue('customer_login'));
        $this->observer->expects($this->at(1))->method('getName')->will($this->returnValue('customer_account_edited'));
        $this->observer->expects($this->at(2))->method('getName')->will($this->returnValue('sales_order_save_after'));
    
        $this->dataHelper->expects($this->any())->method('isEnabled')->with($this->isType('int'))
            ->will($this->returnValue(true));
        
        $this->identifyCustomerObserver->execute($this->observer);
    }
}