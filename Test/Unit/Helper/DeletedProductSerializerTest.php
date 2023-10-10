<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Metrilo\Analytics\Helper\DeletedProductSerializer;
use PHPUnit\Framework\TestCase;

class DeletedProductSerializerTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderModel;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollection;

    /**
     * @var \Metrilo\Analytics\Helper\DeletedProductSerializer
     */
    private $deletedProductSerializer;

    private int $parentItemId = 11;

    private int $parentItemPrice = 100;

    private string $parentItemName = 'parentName';

    private string $parentSku = 'parent-sku';

    private int $simpleProduct1Id = 1;

    private int $simpleProduct2Id = 2;

    private string $simpleProduct1Sku = 'simple-1';

    private string $simpleProduct2Sku = 'simple-2';

    private string $simpleProduct1Name = 'Simple 1';

    private string $simpleProduct2Name = 'Simple 2';

    private int $simpleProduct1Price = 50;

    private int $simpleProduct2Price = 60;

    public function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->orderModel = $this->getMockBuilder(Order::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->orderCollection = $this->getMockBuilder(Collection::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->orderCollection->expects($this->any())->method('getIterator')
                              ->will($this->returnValue(new \ArrayIterator([$this->orderModel])));

        $this->deletedProductSerializer = new DeletedProductSerializer($context);
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSerializeParentProduct()
    {
        $parentItem = $this->getMockBuilder(Item::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $childItem1 = $this->getMockBuilder(Item::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $childItem2 = $this->getMockBuilder(Item::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $parentItem->expects($this->any())
                   ->method('getProductId')
                   ->will($this->returnValue($this->parentItemId));

        $childItem1->expects($this->any())
                   ->method('getProductId')
                   ->will($this->returnValue($this->simpleProduct1Id));

        $childItem1->expects($this->any())
                   ->method('getParentItemId')
                   ->will($this->returnValue($this->parentItemId));

        $childItem1->expects($this->any())
                   ->method('getProductType')
                   ->will($this->returnValue(Type::TYPE_SIMPLE));

        $childItem1->expects($this->any())
                   ->method('getSku')
                   ->will($this->returnValue($this->simpleProduct1Sku));

        $childItem1->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($this->simpleProduct1Name));

        $childItem2->expects($this->any())
                   ->method('getProductId')
                   ->will($this->returnValue($this->simpleProduct2Id));

        $childItem2->expects($this->any())
                   ->method('getSku')
                   ->will($this->returnValue($this->simpleProduct2Sku));

        $childItem2->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($this->simpleProduct2Name));

        $childItem2->expects($this->any())
                   ->method('getParentItemId')
                   ->will($this->returnValue($this->parentItemId));

        $childItem2->expects($this->any())
                   ->method('getProductType')
                   ->will($this->returnValue(Type::TYPE_SIMPLE));

        $parentItem->expects($this->any())
                   ->method('getProductType')
                   ->will($this->returnValue(Configurable::TYPE_CODE));

        $parentItem->expects($this->once())
                   ->method('getSku')
                   ->will($this->returnValue($this->parentSku));
        $parentItem->expects($this->any())
                   ->method('getPrice')
                   ->will($this->returnValue($this->parentItemPrice));
        $parentItem->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($this->parentItemName));

        $this->orderModel->expects($this->any())
                         ->method('getItemById')
                         ->with($this->parentItemId)
                         ->will($this->returnValue($parentItem));

        $this->orderModel->expects($this->any())
                         ->method('getAllItems')
                         ->will(
                             $this->returnValue(
                                 new \ArrayIterator(
                                     [
                                         $childItem1,
                                         $parentItem,
                                         $childItem2
                                     ]
                                 )
                             )
                         );

        $expected[$this->parentItemId] = [
            'categories' => [],
            'id' => $this->parentItemId,
            'sku' => $this->parentSku,
            'imageUrl' => '',
            'name' => $this->parentItemName,
            'price' => 0,
            'url' => '',
            'options' => [
                $this->simpleProduct1Id => [
                    'id' => $this->simpleProduct1Id,
                    'sku' => $this->simpleProduct1Sku,
                    'name' => $this->simpleProduct1Name,
                    'price' => $this->parentItemPrice,
                    'imageUrl' => ''
                ],
                $this->simpleProduct2Id => [
                    'id' => $this->simpleProduct2Id,
                    'sku' => $this->simpleProduct2Sku,
                    'name' => $this->simpleProduct2Name,
                    'price' => $this->parentItemPrice,
                    'imageUrl' => ''
                ]
            ]
        ];

        $result = $this->deletedProductSerializer->serialize($this->orderCollection);

        $this->assertSame($expected, $result);
    }

    public function testSerializeChildProduct()
    {
        $childItem1 = $this->getMockBuilder(Item::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $childItem2 = $this->getMockBuilder(Item::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $childItem1->expects($this->any())
                   ->method('getProductId')
                   ->will($this->returnValue($this->simpleProduct1Id));

        $childItem1->expects($this->any())
                   ->method('getProductType')
                   ->will($this->returnValue(Type::TYPE_SIMPLE));

        $childItem1->expects($this->any())
                   ->method('getSku')
                   ->will($this->returnValue($this->simpleProduct1Sku));

        $childItem1->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($this->simpleProduct1Name));

        $childItem1->expects($this->once())
                   ->method('getPrice')
                   ->will($this->returnValue($this->simpleProduct1Price));

        $childItem2->expects($this->any())
                   ->method('getProductId')
                   ->will($this->returnValue($this->simpleProduct2Id));

        $childItem2->expects($this->any())
                   ->method('getSku')
                   ->will($this->returnValue($this->simpleProduct2Sku));

        $childItem2->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($this->simpleProduct2Name));

        $childItem2->expects($this->once())
                   ->method('getPrice')
                   ->will($this->returnValue($this->simpleProduct2Price));

        $childItem2->expects($this->any())
                   ->method('getProductType')
                   ->will($this->returnValue(Type::TYPE_SIMPLE));

        $this->orderModel->expects($this->any())
                         ->method('getAllItems')
                         ->will(
                             $this->returnValue(
                                 new \ArrayIterator(
                                     [
                                         $childItem1,
                                         $childItem2
                                     ]
                                 )
                             )
                         );

        $expected = [
            $this->simpleProduct1Id => [
                'categories' => [],
                'id' => $this->simpleProduct1Id,
                'sku' => $this->simpleProduct1Sku,
                'imageUrl' => '',
                'name' => $this->simpleProduct1Name,
                'price' => $this->simpleProduct1Price,
                'url' => '',
                'options' => []
            ],
            $this->simpleProduct2Id => [
                'categories' => [],
                'id' => $this->simpleProduct2Id,
                'sku' => $this->simpleProduct2Sku,
                'imageUrl' => '',
                'name' => $this->simpleProduct2Name,
                'price' => $this->simpleProduct2Price,
                'url' => '',
                'options' => []
            ]
        ];

        $result = $this->deletedProductSerializer->serialize($this->orderCollection);

        $this->assertSame($expected, $result);
    }
}
