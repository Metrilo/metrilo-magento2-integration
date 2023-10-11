<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Catalog\Model\Session;
use Magento\Framework\App\Helper\Context;
use Metrilo\Analytics\Helper\SessionEvents;
use PHPUnit\Framework\TestCase;

class SessionEventsTest extends TestCase
{
    private Session $session;
    private SessionEvents $sessionEvents;

    private string $metriloSessionEvents = 'metrilo_session_key';

    public function setUp(): void
    {

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionEvents = new SessionEvents($this->session, $context);
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
