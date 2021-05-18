<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Metrilo\Analytics\Model\Events\CartView;

class CartViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Metrilo\Analytics\Model\Events\CartView
     */
    private $cartViewEvent;
    
    public function setUp(): void
    {
        $this->cartViewEvent = new CartView();
    }
    
    public function testCallJs()
    {
        $expected = "window.metrilo.customEvent('view_cart');";
        
        $result = $this->cartViewEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}
