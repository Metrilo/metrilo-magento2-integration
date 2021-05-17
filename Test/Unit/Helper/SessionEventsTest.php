<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Catalog\Model\Session;
use Metrilo\Analytics\Helper\SessionEvents;

class SessionEventsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Session
     */
    private $session;
    
    /**
     * @var \Metrilo\Analytics\Helper\SessionEvents
     */
    private $sessionEvents;
    
    private $metriloSessionEvents = 'metrilo_session_key';
    
    public function setUp(): void
    {
        
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMock();
        
        $this->sessionEvents = new SessionEvents($this->session);
    }
    
    public function testGetSessionEvents()
    {
        $this->session->expects($this->any())->method('getData')
            ->with($this->equalTo($this->metriloSessionEvents), $this->equalTo(true));
        
        $this->assertEquals([], $this->sessionEvents->getSessionEvents());
    }
    
    public function testAddSessionEvent()
    {
        $this->session->expects($this->any())->method('setData')
            ->with($this->equalTo($this->metriloSessionEvents), $this->isType('array'));
        
        $this->assertEquals([], $this->sessionEvents->getSessionEvents());
    }
}
