<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\Activity;
use Metrilo\Analytics\Observer\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private Observer $observer;

    private ManagerInterface $managerInterface;

    private Data $dataHelper;

    private Activity $activityHelper;

    private Config $configObserver;

    private StoreManagerInterface $storeManager;

    public function setUp(): void
    {
        $this->observer = $this->getMockBuilder(Observer::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getStore', 'getWebsite'])
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

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->configObserver = new Config(
            $this->managerInterface,
            $this->dataHelper,
            $this->activityHelper,
            $this->storeManager
        );
    }

    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Config($this->managerInterface, $this->dataHelper, $this->activityHelper, $this->storeManager)
        );
    }

    public function testExecute()
    {
        $storeId = 1;

        $this->observer->expects($this->once())->method('getStore')->will($this->returnValue(''));
        $this->observer->expects($this->exactly(2))->method('getWebsite')
                       ->will($this->returnValue(1));

        $website = $this->getMockBuilder(Website::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $store = $this->getMockBuilder(Store::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->storeManager->expects($this->once())
                           ->method('getWebsite')
                           ->with(1)
                           ->will($this->returnValue($website));

        $website->expects($this->once())
                ->method('getDefaultStore')
                ->will($this->returnValue($store));

        $store->expects($this->once())
              ->method('getId')
              ->will($this->returnValue($storeId));

        $this->managerInterface->expects($this->any())->method('addError')->with($this->isType('string'));

        $this->activityHelper->expects($this->once())->method('createActivity')
                             ->with($storeId, 'integrated')
                             ->will($this->returnValue(false));

        $this->configObserver->execute($this->observer);
    }
}
