<?php

namespace Metrilo\Analytics\Test\Unit\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\CustomerData\PrivateData;

class PrivateDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Metrilo\Analytics\Helper\SessionEvents
     */
    private $sessionEventsHelper;
    
    /**
     * @var \Metrilo\Analytics\CustomerData\PrivateData
     */
    private $privateData;
    
    public function setUp(): void
    {
        $this->sessionEventsHelper = $this->getMockBuilder(SessionEvents::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionEvents'])
            ->getMock();
        
        $this->privateData = new PrivateData($this->sessionEventsHelper);
    }
    
    public function testImplementsTheSectionSourceInterface()
    {
        $this->assertInstanceOf(
            SectionSourceInterface::class,
            new PrivateData($this->sessionEventsHelper)
        );
    }
    
    public function testGetSectionData()
    {
        $this->sessionEventsHelper->expects($this->any())->method('getSessionEvents')
            ->will($this->returnValue(['currentSessionEvents']));
        
        $expected = ['events' => ['currentSessionEvents']];
        
        $result = $this->privateData->getSectionData();
        
        $this->assertSame($expected, $result);
    }
}
