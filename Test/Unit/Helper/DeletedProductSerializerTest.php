<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Metrilo\Analytics\Helper\DeletedProductSerializer;

class DeletedProductSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;
    
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderModel;
    
    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    private $itemModel;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollection;
    
    /**
     * @var \Metrilo\Analytics\Helper\DeletedProductSerializer
     */
    private $deletedProductSerializer;
    
    private $parentItemId     = 11;
    private $parentItemPrice  = 100;
    private $parentItemName   = 'parentName';
    private $itemId           = 13;
    private $itemPrice        = 101;
    private $itemSku          = 'itemSku';
    private $itemName         = 'itemName';
    private $simpleItem       = 'simple';
    private $configurableItem = 'configurable';

    public function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $this->orderModel = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemById', 'getProductId', 'getName', 'getPrice', 'getAllItems'])
            ->getMock();
    
        $this->orderModel->expects($this->any())->method('getAllItems')->will($this->returnValue([$this->itemModel]));
    
        $this->itemModel = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentItemId', 'getProductId', 'getSku', 'getName', 'getProductType', 'getPrice'])
            ->getMock();
    
        $this->itemModel->expects($this->any())->method('getProductId')->will($this->returnValue($this->itemId));
        $this->itemModel->expects($this->any())->method('getSku')->will($this->returnValue($this->itemSku));
        $this->itemModel->expects($this->any())->method('getName')->will($this->returnValue($this->itemName));
        $this->itemModel->expects($this->any())->method('getPrice')->will($this->returnValue($this->itemPrice));
    
        $this->orderCollection = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
//            ->setMethods(['getParentItemId', 'getProductId', 'getSku', 'getName', 'getProductType', 'getPrice'])
            ->getMock();

        $this->deletedProductSerializer = new DeletedProductSerializer($this->context);
    }

    public function testSerializeParentProduct()
    {
        $this->orderModel->expects($this->any())->method('getItemById')->with($this->equalTo($this->parentItemId))->will($this->returnValue($this->orderModel));
        $this->orderModel->expects($this->any())->method('getPrice')->will($this->returnValue($this->parentItemPrice));
        $this->orderModel->expects($this->any())->method('getProductId')->will($this->returnValue($this->parentItemId));
        $this->orderModel->expects($this->any())->method('getName')->will($this->returnValue($this->parentItemName));
    
        $this->itemModel->expects($this->any())->method('getParentItemId')->will($this->returnValue($this->parentItemId));
        $this->itemModel->expects($this->any())->method('getProductType')->will($this->returnValue($this->configurableItem));
        
        $productOptions[] = [
            'id'       => $this->itemSku,
            'sku'      => $this->itemSku,
            'name'     => $this->itemName,
            'price'    => $this->parentItemPrice,
            'imageUrl' => ''
        ];

        $expected[] = [
            'categories' => [],
            'id'         => $this->parentItemId,
            'sku'        => $this->itemSku,
            'imageUrl'   => '',
            'name'       => $this->parentItemName,
            'price'      => 0,
            'url'        => '',
            'options'    => $productOptions
        ];
        
        $result = $this->deletedProductSerializer->serialize($this->orderCollection);

//        $this->assertSame($expected, $result);
    }
    
    public function testSerializeChildProduct()
    {
        $this->orderModel->expects($this->any())->method('getProductId')->will($this->returnValue(''));
        $this->orderModel->expects($this->any())->method('getName')->will($this->returnValue(''));
        
        $this->itemModel->expects($this->any())->method('getParentItemId')->will($this->returnValue(''));
        $this->itemModel->expects($this->any())->method('getProductType')->will($this->returnValue($this->simpleItem));
        
        $expected[] = [
            'categories' => [],
            'id'         => $this->itemId,
            'sku'        => $this->itemSku,
            'imageUrl'   => '',
            'name'       => $this->itemName,
            'price'      => $this->itemPrice,
            'url'        => '',
            'options'    => []
        ];
        
        $result = $this->deletedProductSerializer->serialize($this->orderCollection);
        
//        $this->assertSame($expected, $result);
    }
}