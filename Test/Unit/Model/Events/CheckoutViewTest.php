<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Metrilo\Analytics\Model\Events\CheckoutView;

class CheckoutViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Metrilo\Analytics\Model\Events\CheckoutView
     */
    private $checkoutViewEvent;
    
    public function setUp()
    {
        $this->checkoutViewEvent = new CheckoutView();
    }
    
    public function testCallJs()
    {
        $expected = "window.metrilo.checkout();";
        
        $result = $this->checkoutViewEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}
