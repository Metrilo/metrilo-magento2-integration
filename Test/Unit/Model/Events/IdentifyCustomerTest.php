<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Metrilo\Analytics\Model\Events\IdentifyCustomer;

class IdentifyCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Metrilo\Analytics\Model\Events\IdentifyCustomer
     */
    private $identifyCustomerEvent;
    
    private $email = 'test@test.com';
    
    public function setUp(): void
    {
        $this->identifyCustomerEvent = new IdentifyCustomer($this->email);
    }
    
    public function testCallJs()
    {
        $expected = 'window.metrilo.identify("' . $this->email . '");';
        
        $result = $this->identifyCustomerEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}
