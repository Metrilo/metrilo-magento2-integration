<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Metrilo\Analytics\Helper\ProductImageUrl;
use Metrilo\Analytics\Helper\ProductOptions;

class ProductOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableType;
    
    /**
     * @var \Metrilo\Analytics\Helper\ProductImageUrl
     */
    private $productImageUrlHelper;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;
    
    /**
     * @var \Metrilo\Analytics\Helper\ProductOptions
     */
    private $productOptions;
    
    public function setUp()
    {
        $this->configurableType = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();
    
        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(Collection::class), [
                'getTypeId',
                'getTypeInstance',
                'getUsedProducts',
                'getImage',
                'getSku',
                'getSpecialPrice',
                'getPrice',
                'getName']))
            ->getMock();
    
        $this->productImageUrlHelper = $this->getMockBuilder(ProductImageUrl::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableOptions', 'getProductImageUrl'])
            ->getMock();
        
        $this->productOptions = new ProductOptions($this->configurableType, $this->productImageUrlHelper);
    }
    
    public function testGetConfigurableOptions()
    {
        $imageUrl = '/product/image/url.jpg';
        
        $this->productCollection->expects($this->any())->method('getTypeInstance')
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('getUsedProducts')
            ->will($this->returnValue([$this->productCollection]));
        $this->productCollection->expects($this->any())->method('getImage')
            ->will($this->returnValue($imageUrl));
        $this->productCollection->expects($this->any())->method('getSku')
            ->will($this->returnValue('productSku'));
        $this->productCollection->expects($this->any())->method('getSpecialPrice')
            ->will($this->returnValue('productSpecialPrice'));
        $this->productCollection->expects($this->any())->method('getPrice')
            ->will($this->returnValue('productPrice'));
        $this->productCollection->expects($this->any())->method('getName')
            ->will($this->returnValue('productName'));
        
        $this->productImageUrlHelper->expects($this->any())->method('getProductImageUrl')
            ->with($this->equalTo($imageUrl))
            ->will($this->returnValue('base/url/string/' . 'catalog/product' . $imageUrl));
        
        $expected[] = [
                'id'       => 'productSku',
                'sku'      => 'productSku',
                'name'     => 'productName',
                'price'    => 'productSpecialPrice',
                'imageUrl' => 'base/url/string/catalog/product/product/image/url.jpg'
            ];
        
        $result = $this->productOptions->getConfigurableOptions($this->productCollection);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testGetParentIds()
    {
        $productId = 1;
        
        $this->configurableType->expects($this->any())->method('getParentIdsByChild')
            ->with($this->equalTo($productId))
            ->will($this->returnValue([1,3,4]));
        
        $this->assertEquals([1,3,4], $this->productOptions->getParentIds($productId));
    }
}
