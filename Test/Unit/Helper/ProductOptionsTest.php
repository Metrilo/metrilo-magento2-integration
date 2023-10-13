<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\App\Helper\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Grouped;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Metrilo\Analytics\Helper\ProductImageUrl;
use Metrilo\Analytics\Helper\ProductOptions;
use PHPUnit\Framework\TestCase;

class ProductOptionsTest extends TestCase
{
    private Configurable $configurableType;

    private Bundle $bundleType;

    private Grouped $groupedType;

    private ProductImageUrl $productImageUrlHelper;
    private Collection $productCollection;
    private ProductOptions $productOptions;

    public function setUp(): void
    {
        $this->configurableType = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();

        $this->bundleType = $this->getMockBuilder(Bundle::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();

        $this->groupedType = $this->getMockBuilder(Grouped::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();

        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(Collection::class), [
                'getId',
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
            ->setMethods(['getParentOptions', 'getProductImageUrl'])
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->productOptions = new ProductOptions(
            $this->configurableType,
            $this->bundleType,
            $this->groupedType,
            $this->productImageUrlHelper,
            $context
        );
    }

    public function testGetParentOptions()
    {
        $imageUrl = '/product/image/url.jpg';

        $this->productCollection->expects($this->any())->method('getTypeInstance')
            ->will($this->returnSelf());
        $this->productCollection->expects($this->any())->method('getUsedProducts')
            ->will($this->returnValue([$this->productCollection]));
        $this->productCollection->expects($this->any())->method('getId')
            ->will($this->returnValue((int)'1'));
        $this->productCollection->expects($this->any())->method('getTypeId')
            ->will($this->returnValue('configurable'));
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
            'id'       => 1,
            'sku'      => 'productSku',
            'name'     => 'productName',
            'price'    => 'productSpecialPrice',
            'imageUrl' => 'base/url/string/catalog/product/product/image/url.jpg'
        ];

        $result = $this->productOptions->getParentOptions($this->productCollection);

        $this->assertSame($expected, $result);
    }

    public function testGetParentIds()
    {
        $productId   = 1;
        $productType = 'configurable';

        $this->configurableType->expects($this->any())->method('getParentIdsByChild')
            ->with($this->equalTo($productId))
            ->will($this->returnValue([1,3,4]));

        $this->bundleType->expects($this->any())->method('getParentIdsByChild')
            ->with($this->equalTo($productId))
            ->will($this->returnValue([1,3,4]));

        $this->groupedType->expects($this->any())->method('getParentIdsByChild')
            ->with($this->equalTo($productId))
            ->will($this->returnValue([1,3,4]));

        $this->assertEquals([1,3,4], $this->productOptions->getParentIds($productId, $productType));
    }
}
