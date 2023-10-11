<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\ProductImageUrl;
use PHPUnit\Framework\TestCase;

class ProductImageUrlTest extends TestCase
{
    private ProductImageUrl $productImageUrl;

    private StoreManagerInterface $storeManagerInterface;

    public function setUp(): void
    {
        $this->storeManagerInterface = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(StoreManagerInterface::class), ['getBaseUrl']))
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->productImageUrl = new ProductImageUrl($this->storeManagerInterface, $context);
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
