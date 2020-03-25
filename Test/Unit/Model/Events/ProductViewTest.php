<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Metrilo\Analytics\Model\Events\ProductView;

class ProductViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Metrilo\Analytics\Model\Events\ProductView
     */
    private $productViewEvent;
    
    private $productId   = 11;
    private $registryKey = 'current_product';

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->productViewEvent = new ProductView($this->registry);
    }
    
    public function testCallJs()
    {
        $this->registry->expects($this->any())->method('registry')
            ->with($this->equalTo($this->registryKey))
            ->will($this->returnValue($this->product));
        
        $this->product->expects($this->any())->method('getId')->will($this->returnValue($this->productId));
        
        $expected = "window.metrilo.viewProduct(" . $this->productId . ");";
        
        $result = $this->productViewEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}