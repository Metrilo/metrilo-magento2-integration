<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\Event;
use Magento\Quote\Model\Quote\Item;
use Metrilo\Analytics\Model\Events\RemoveFromCart;

class RemoveFromCartTest extends \PHPUnit\Framework\TestCase
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
     * @var \Metrilo\Analytics\Model\Events\RemoveFromCart
     */
    private $removeFromCartEvent;
    
    private $productSku            = 'productSku';
    private $simpleProductId       = 123;
    private $configurableProductId = 321;
    private $productQuantity       = 2;

    public function setUp()
    {
        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteItem'])
            ->getMock();
        
        $this->quoteItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChildren', 'getProductId', 'getQty', 'getSku'])
            ->getMock();
    
        $this->event->expects($this->any())->method('getQuoteItem')
            ->will($this->returnValue($this->quoteItem));
        
        $this->removeFromCartEvent = new RemoveFromCart($this->event);
    }
    
    public function testCallJsWithSimpleProduct()
    {
        $this->quoteItem->expects($this->any())->method('getChildren')
            ->will($this->returnValue([]));
        $this->quoteItem->expects($this->any())->method('getProductId')
            ->will($this->returnValue($this->simpleProductId));
        $this->quoteItem->expects($this->any())->method('getQty')
            ->will($this->returnValue($this->productQuantity));
        
        $expected = "window.metrilo.removeFromCart('" . $this->simpleProductId . "', " . $this->productQuantity . ");";
        
        $result = $this->removeFromCartEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
    
    public function testCallJsWithConfigurableProductWithSku()
    {
        $this->quoteItem->expects($this->any())->method('getChildren')
            ->will($this->returnValue([$this->quoteItem]));
        $this->quoteItem->expects($this->any())->method('getQty')
            ->will($this->returnValue($this->productQuantity));
        $this->quoteItem->expects($this->any())->method('getSku')
            ->will($this->returnValue($this->productSku));
        $this->quoteItem->expects($this->any())->method('getProductId')
            ->will($this->returnValue($this->configurableProductId));
        
        $expected = "window.metrilo.removeFromCart('" . $this->productSku . "', " . $this->productQuantity . ");";
        
        $result = $this->removeFromCartEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
    
    public function testCallJsWithConfigurableProductWithoutSku()
    {
        $this->quoteItem->expects($this->any())->method('getChildren')
            ->will($this->returnValue([$this->quoteItem]));
        $this->quoteItem->expects($this->any())->method('getQty')
            ->will($this->returnValue($this->productQuantity));
        $this->quoteItem->expects($this->any())->method('getSku')
            ->will($this->returnValue(''));
        $this->quoteItem->expects($this->any())->method('getProductId')
            ->will($this->returnValue($this->configurableProductId));
        
        $expected = "window.metrilo.removeFromCart('" .
            $this->configurableProductId . "', " . $this->productQuantity . ");";
        
        $result = $this->removeFromCartEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}
