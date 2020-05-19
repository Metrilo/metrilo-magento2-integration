<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\Activity;
use Metrilo\Analytics\Observer\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $observer;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $managerInterface;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\Activity
     */
    private $activityHelper;
    
    /**
     * @var \Metrilo\Analytics\Observer\Config
     */
    private $configObserver;
    
    public function setUp()
    {
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();
    
        $this->managerInterface = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(get_class_methods(ManagerInterface::class))
            ->getMock();
    
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['logError'])
            ->getMock();
    
        $this->activityHelper = $this->getMockBuilder(Activity::class)
            ->disableOriginalConstructor()
            ->setMethods(['createActivity'])
            ->getMock();
        
        
        $this->configObserver = new Config($this->managerInterface, $this->dataHelper, $this->activityHelper);
    }
    
    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Config($this->managerInterface, $this->dataHelper, $this->activityHelper)
        );
    }
    
    public function testExecute()
    {
        $storeId = 1;
    
        $this->observer->expects($this->any())->method('getStore')->will($this->returnValue($storeId));
        
        $this->managerInterface->expects($this->any())->method('addError')->with($this->isType('string'));
        
        $this->activityHelper->expects($this->any())->method('createActivity')
            ->with($this->isType('int'), $this->isType('string'))
            ->will($this->returnValue(false));
        
        $this->configObserver->execute($this->observer);
    }
}
