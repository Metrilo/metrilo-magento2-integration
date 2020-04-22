<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\ProductImageUrl;

class ProductImageUrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Metrilo\Analytics\Helper\ProductImageUrl
     */
    private $productImageUrl;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;
    
    public function setUp()
    {
        $this->storeManagerInterface = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(StoreManagerInterface::class), ['getBaseUrl']))
            ->getMock();
        
        $this->productImageUrl = new ProductImageUrl($this->storeManagerInterface);
    }
    
    public function testGetProductImageUrl()
    {
        $baseUrlString       = 'base/url/string/';
        $imageUrlRequestPath = '/image/url/request/path.jpg';
        
        $this->storeManagerInterface->expects($this->any())->method('getStore')->will($this->returnSelf());
        $this->storeManagerInterface->expects($this->any())->method('getBaseUrl')
            ->with($this->equalTo('media'))->will($this->returnValue($baseUrlString));
        
        $expected = $baseUrlString . 'catalog/product' . $imageUrlRequestPath;
        $result = $this->productImageUrl->getProductImageUrl($imageUrlRequestPath);
        
        $this->assertEquals($expected, $result);
    }
}