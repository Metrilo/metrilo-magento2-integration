<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\Event;
use Magento\Quote\Model\Quote\Item;
use Metrilo\Analytics\Model\Events\AddToCart;

class AddToCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event
     */
    private $event;
    
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $quoteItem;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\AddToCart
     */
    private $addToCartEvent;
    
    private $simpleProductId       = 333;
    private $configurableProductId = 444;
    private $productQuantity       = 3;
    private $getDataParam          = 'qty_to_add';
    
    public function setUp(): void
    {
        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteItem'])
            ->getMock();
    
        $this->quoteItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductId', 'getData'])
            ->getMock();
        
        $this->event->expects($this->any())->method('getQuoteItem')
            ->will($this->returnValue($this->quoteItem));
        
        $this->addToCartEvent = new AddToCart($this->event);
    }
    
    public function testCallJsWithSimpleProduct()
    {
        $this->quoteItem->expects($this->any())->method('getProductId')
            ->will($this->returnValue($this->simpleProductId));
        $this->quoteItem->expects($this->any())->method('getData')
            ->with($this->equalTo($this->getDataParam))
            ->will($this->returnValue($this->productQuantity));
        
        $expected = "window.metrilo.addToCart('" . $this->simpleProductId . "', " . $this->productQuantity . ");";
        
        $result = $this->addToCartEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
    
    public function testCallJsWithConfigurableProduct()
    {
        $this->quoteItem->expects($this->any())->method('getProductId')
            ->will($this->returnValue($this->configurableProductId));
        $this->quoteItem->expects($this->any())->method('getData')
            ->with($this->equalTo($this->getDataParam))
            ->will($this->returnValue($this->productQuantity));
    
        $expected = "window.metrilo.addToCart('" . $this->configurableProductId . "', " . $this->productQuantity . ");";
        
        $result = $this->addToCartEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}
