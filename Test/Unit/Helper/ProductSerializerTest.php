<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\ProductOptions;
use Metrilo\Analytics\Helper\ProductSerializer;

class ProductSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
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
    
    /**
     * @var \Metrilo\Analytics\Helper\ProductSerializer
     */
    private $productSerializer;
    
    public function setUp(): void
    {
        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(Collection::class), [
                'getId',
                'getTypeId',
                'getImage',
                'getPrice',
                'getSpecialPrice',
                'getRequestPath',
                'getCategoryIds',
                'getSku',
                'getName'
            ]))
            ->getMock();
        
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(StoreManagerInterface::class), ['getBaseUrl']))
            ->getMock();
        
        $this->configurableType = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();
        
        $this->productImageUrlHelper = $this->getMockBuilder(ProductImageUrl::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentOptions', 'getProductImageUrl'])
            ->getMock();
        
        $this->productOptions = $this->getMockBuilder(ProductOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIds', 'getParentOptions'])
            ->getMock();
        
        $this->productSerializer = new ProductSerializer($this->storeManager, $this->productOptions);
    }
    
    public function testSerialize()
    {
        $storeId        = 1;
        $productId      = 1;
        $productSku     = 'productSku';
        $productName    = 'productName';
        $productOptions = [];
        
        $baseUrl        = 'base/url/string/';
        $imageUrl       = '/product/image/url.jpg';
        $requestPath    = 'product/request/path.html';
        
        $this->productCollection->expects($this->any())->method('getStoreId')
            ->will($this->returnValue($storeId));
        $this->productCollection->expects($this->any())->method('getId')
            ->will($this->returnValue($productId));
        $this->productCollection->expects($this->at(0))->method('getTypeId')
            ->will($this->returnValue('simple'));
        $this->productCollection->expects($this->at(1))->method('getTypeId')
            ->will($this->returnValue('configurable'));
        $this->productCollection->expects($this->any())->method('getImage')
            ->will($this->returnValue($imageUrl));
        $this->productCollection->expects($this->any())->method('getPrice')
            ->will($this->returnValue('productPrice'));
        $this->productCollection->expects($this->any())->method('getSpecialPrice')
            ->will($this->returnValue('productSpecialPrice'));
        $this->productCollection->expects($this->any())->method('getRequestPath')
            ->will($this->returnValue($requestPath));
        $this->productCollection->expects($this->any())->method('getCategoryIds')
            ->will($this->returnValue([1,3,4]));
        $this->productCollection->expects($this->any())->method('getSku')
            ->will($this->returnValue($productSku));
        $this->productCollection->expects($this->any())->method('getName')
            ->will($this->returnValue($productName));
        
        $this->productOptions->expects($this->any())->method('getParentOptions')
            ->with($this->isInstanceOf(Collection::class))
            ->will($this->returnValue($productOptions));
        $this->productOptions->expects($this->any())->method('getParentIds')
            ->with($this->equalTo($productId))
            ->will($this->returnValue($productOptions));
        $this->productOptions->productImageUrl = $this->productImageUrlHelper;
        
        $this->productImageUrlHelper->expects($this->any())->method('getProductImageUrl')
            ->with($this->equalTo($imageUrl))
            ->will($this->returnValue($baseUrl . 'catalog/product' . $imageUrl));
        
        $this->storeManager->expects($this->any())->method('getStore')
            ->with($this->equalTo($storeId))
            ->will($this->returnSelf());
        $this->storeManager->expects($this->any())->method('getBaseUrl')
            ->will($this->returnValue($baseUrl));
        
        $expected = [
            'categories' => [1,3,4],
            'id'         => 1,
            'sku'        => $productSku,
            'imageUrl'   => $baseUrl . 'catalog/product' . $imageUrl,
            'name'       => $productName,
            'price'      => 'productSpecialPrice',
            'url'        => $baseUrl . $requestPath,
            'options'    => $productOptions
        ];
        
        $result = $this->productSerializer->serialize($this->productCollection);
        
        $this->assertEquals($expected, $result);
    }
}
