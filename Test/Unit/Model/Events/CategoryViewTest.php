<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;
use Metrilo\Analytics\Model\Events\CategoryView;

class CategoryViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $categoryModel;

    /**
     * @var \Metrilo\Analytics\Model\Events\CategoryView
     */
    private $categoryViewEvent;
    
    private $categoryKey = 'current_category';
    private $categoryId  = 444;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
    
        $this->categoryModel = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->categoryViewEvent = new CategoryView($this->registry);
    }

    public function testCallJs()
    {
        $this->registry->expects($this->any())->method('registry')
            ->with($this->equalTo($this->categoryKey))
            ->will($this->returnValue($this->categoryModel));
        
        $this->categoryModel->expects($this->any())->method('getId')
            ->will($this->returnValue($this->categoryId));
        
        $expected = "window.metrilo.viewCategory('" . $this->categoryId . "');";

        $result = $this->categoryViewEvent->callJS();

        $this->assertSame($expected, $result);
    }
}
