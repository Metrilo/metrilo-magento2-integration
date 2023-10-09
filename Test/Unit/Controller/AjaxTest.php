<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Backend\App\Action\Context;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Model\CustomerData;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Metrilo\Analytics\Model\CategoryData;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Metrilo\Analytics\Model\DeletedProductData;
use Metrilo\Analytics\Model\ProductData;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Metrilo\Analytics\Model\OrderData;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Metrilo\Analytics\Helper\CustomerSerializer;
use Metrilo\Analytics\Helper\CategorySerializer;
use Metrilo\Analytics\Helper\ProductSerializer;
use Metrilo\Analytics\Helper\DeletedProductSerializer;
use Metrilo\Analytics\Helper\OrderSerializer;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\Activity;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Metrilo\Analytics\Api\Client;
use Metrilo\Analytics\Controller\Adminhtml\Import\Ajax;

class AjaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $context;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Model\CustomerData
     */
    private $customerModel;
    
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollection;
    
    /**
     * @var \Metrilo\Analytics\Model\CategoryData
     */
    private $categoryModel;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollection;
    
    /**
     * @var \Metrilo\Analytics\Model\ProductData
     */
    private $productModel;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollection;
    
    /**
     * @var \Metrilo\Analytics\Model\DeletedProductData
     */
    private $deletedProductModel;
    
    /**
     * @var \Metrilo\Analytics\Model\OrderData
     */
    private $orderModel;
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollection;
    
    /**
     * @var \Metrilo\Analytics\Helper\CustomerSerializer
     */
    private $customerSerializer;
    
    /**
     * @var \Metrilo\Analytics\Helper\CategorySerializer
     */
    private $categorySerializer;
    
    /**
     * @var \Metrilo\Analytics\Helper\DeletedProductSerializer
     */
    private $deletedProductSerializer;
    
    /**
     * @var \Metrilo\Analytics\Helper\ProductSerializer
     */
    private $productSerializer;
    
    /**
     * @var \Metrilo\Analytics\Helper\OrderSerializer
     */
    private $orderSerializer;
    
    /**
     * @var \Metrilo\Analytics\Helper\ApiClient
     */
    private $apiClientHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\Activity
     */
    private $activityHelper;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactoryController;
    
    /**
     * @var \Metrilo\Analytics\Api\Client
     */
    private $apiClient;
    
    /**
     * @var \Metrilo\Analytics\Controller\Adminhtml\Import\Ajax
     */
    private $ajaxController;
    
    private $chunkItems       = 50;
    private $storeIdKey       = 'storeId';
    private $chunkIdKey       = 'chunkId';
    private $importTypeKey    = 'importType';
    private $activityStartKey = 'import_start';
    private $activityEndKey   = 'import_end';
    private $result           = [];
    private $storeId          = 1;
    private $chunkId          = 11;
    private $jsonFactory;
    private $response = [
        'response' => 'apiCallResponse',
        'code' => 'responseCode'
    ];
    
    public function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['logError'])
            ->getMock();
        
        $this->customerModel = $this->getMockBuilder(CustomerData::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomers'])
            ->getMock();
    
        $this->customerCollection = $this->getMockBuilder(CustomerCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setPageSize',
                'setCurPage',
                'getEmail',
                'getCreatedAt',
                'getFirstName',
                'getLastName',
                'getSubscriberStatus',
                'getTags'
            ])
            ->getMock();
    
        $this->categoryModel = $this->getMockBuilder(CategoryData::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCategories'])
            ->getMock();
    
        $this->categoryCollection = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setPageSize',
                'setCurPage',
                'getId',
                'getStoreId',
                'getName',
                'getRequestPath'
            ])
            ->getMock();
    
        $this->deletedProductModel = $this->getMockBuilder(DeletedProductData::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDeletedProductOrders'])
            ->getMock();
    
        $this->productModel = $this->getMockBuilder(ProductData::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProducts'])
            ->getMock();
    
        $this->productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setPageSize',
                'setCurPage',
                'setDataToAll',
                'getStoreId',
                'getId',
                'getTypeId',
                'getImage',
                'getPrice',
                'getSpecialPrice',
                'getRequestPath',
                'getCategoryIds',
                'getSku',
                'getName'
            ])
            ->getMock();
    
        $this->orderModel = $this->getMockBuilder(OrderData::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrders'])
            ->getMock();
    
        $this->orderCollection = $this->getMockBuilder(OrderCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setPageSize',
                'setCurPage',
                'addFieldToFilter',
                'create'
            ])
            ->getMock();
    
        $this->customerSerializer = $this->getMockBuilder(CustomerSerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
    
        $this->categorySerializer = $this->getMockBuilder(CategorySerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
    
        $this->deletedProductSerializer = $this->getMockBuilder(DeletedProductSerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
    
        $this->productSerializer = $this->getMockBuilder(ProductSerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
    
        $this->orderSerializer = $this->getMockBuilder(OrderSerializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock();
    
        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();
    
        $this->activityHelper = $this->getMockBuilder(Activity::class)
            ->disableOriginalConstructor()
            ->setMethods(['createActivity'])
            ->getMock();
    
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();
    
        $this->jsonFactoryController = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'setData'])
            ->getMock();
    
        $this->jsonFactoryController->expects($this->any())->method('create')->will($this->returnSelf());
        $this->jsonFactoryController->expects($this->any())->method('setData')->with($this->isType('array'));
    
        $this->apiClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['customerBatch', 'categoryBatch', 'productBatch', 'orderBatch'])
            ->getMock();
    
        $this->apiClientHelper->expects($this->any())->method('getClient')
            ->with($this->equalTo($this->storeId))
            ->will($this->returnValue($this->apiClient));
        
        $this->ajaxController = new Ajax(
            $this->context,
            $this->dataHelper,
            $this->customerModel,
            $this->categoryModel,
            $this->productModel,
            $this->deletedProductModel,
            $this->orderModel,
            $this->customerSerializer,
            $this->categorySerializer,
            $this->productSerializer,
            $this->deletedProductSerializer,
            $this->orderSerializer,
            $this->apiClientHelper,
            $this->activityHelper,
            $this->request,
            $this->jsonFactoryController
        );
    }
    
    public function testExecuteCustomers()
    {
        $customerEmail        = 'customer@email.com';
        $customerCreatedAt    = '01.09.19';
        $customerFirstName    = 'customerFirstName';
        $customerLastName     = 'customerLastName';
        $customerSubscription = true;
        $customerGroup        = 'customerGroup';
        
        $expected = [
            'email'       => $customerEmail,
            'createdAt'   => $customerCreatedAt,
            'firstName'   => $customerFirstName,
            'lastName'    => $customerLastName,
            'subscribed'  => $customerSubscription,
            'tags'        => $customerGroup
        ];
        
        $this->request->expects($this->at(0))->method('getParam')
            ->with($this->equalTo($this->storeIdKey))
            ->will($this->returnValue($this->storeId));
        $this->request->expects($this->at(1))->method('getParam')
            ->with($this->equalTo($this->chunkIdKey))
            ->will($this->returnValue($this->chunkId));
        $this->request->expects($this->at(2))->method('getParam')
            ->with($this->equalTo($this->importTypeKey))
            ->will($this->returnValue('customers'));
        
        $this->activityHelper->expects($this->any())->method('createActivity')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->activityStartKey))
            ->will($this->returnValue(true));
    
        $this->customerModel->expects($this->any())->method('getCustomers')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->chunkId))
            ->will($this->returnValue($this->customerCollection));
        
        $this->customerCollection->expects($this->any())->method('setPageSize')
            ->with($this->equalTo($this->chunkItems));
        $this->customerCollection->expects($this->any())->method('setCurPage')
            ->with($this->equalTo($this->chunkId));
        $this->customerCollection->expects($this->any())->method('getEmail')
            ->will($this->returnValue($customerEmail));
        $this->customerCollection->expects($this->any())->method('getCreatedAt')
            ->will($this->returnValue($customerCreatedAt));
        $this->customerCollection->expects($this->any())->method('getFirstName')
            ->will($this->returnValue($customerFirstName));
        $this->customerCollection->expects($this->any())->method('getLastName')
            ->will($this->returnValue($customerLastName));
        $this->customerCollection->expects($this->any())->method('getSubscriberStatus')
            ->will($this->returnValue($customerSubscription));
        $this->customerCollection->expects($this->any())->method('getTags')
            ->will($this->returnValue($customerGroup));
    
        $this->customerSerializer->expects($this->any())->method('serialize')
            ->with($this->assertInstanceOf(CustomerCollection::class, $this->customerCollection))
            ->will($this->returnValue($expected));
        
        $this->apiClient->expects($this->any())->method('customerBatch')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->response));
        
        $this->ajaxController->execute();
    }
    
    public function testExecuteCategories()
    {
        $categoryId   = 123;
        $categoryName = 'categoryName';
        $categoryUrl  = 'categoryUrl';
    
        $expected = [
            'id'   => $categoryId,
            'name' => $categoryName,
            'url'  => $categoryUrl
        ];
        
        $this->request->expects($this->at(0))->method('getParam')
            ->with($this->equalTo($this->storeIdKey))
            ->will($this->returnValue($this->storeId));
        $this->request->expects($this->at(1))->method('getParam')
            ->with($this->equalTo($this->chunkIdKey))
            ->will($this->returnValue($this->chunkId));
        $this->request->expects($this->at(2))->method('getParam')
            ->with($this->equalTo($this->importTypeKey))
            ->will($this->returnValue('categories'));
    
        $this->categoryModel->expects($this->any())->method('getCategories')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->chunkId))
            ->will($this->returnValue($this->categoryCollection));
    
        $this->categoryCollection->expects($this->any())->method('setPageSize')
            ->with($this->equalTo($this->chunkItems));
        $this->categoryCollection->expects($this->any())->method('setCurPage')
            ->with($this->equalTo($this->chunkId));
        $this->categoryCollection->expects($this->any())->method('getId')
            ->will($this->returnValue($categoryId));
        $this->categoryCollection->expects($this->any())->method('getName')
            ->will($this->returnValue($categoryName));
        $this->categoryCollection->expects($this->any())->method('getRequestPath')
            ->will($this->returnValue($categoryUrl));
    
        $this->categorySerializer->expects($this->any())->method('serialize')
            ->with($this->assertInstanceOf(CategoryCollection::class, $this->categoryCollection))
            ->will($this->returnValue($expected));
    
        $this->apiClient->expects($this->any())->method('categoryBatch')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->response));
    
        $this->ajaxController->execute();
    }
    
    public function testExecuteDeletedProducts()
    {
        $delProdCategories = [];
        $delProdId         = 333;
        $delProdSku        = 'delProdSku';
        $delProdImgUrl     = '';
        $delProdName       = 'delProdName';
        $delProdPrice      = 1000;
        $delProdUrl        = 'delProdUrl';
        $delProdOptions[]  = [
            'id'       => 'itemSku',
            'sku'      => 'itemSku',
            'name'     => 'itemName',
            'price'    => 'itemPrice',
            'imageUrl' => ''
        ];
        
        $expected[] = [
            'categories' => $delProdCategories,
            'id'         => $delProdId,
            'sku'        => $delProdSku,
            'imageUrl'   => $delProdImgUrl,
            'name'       => $delProdName,
            'price'      => $delProdPrice,
            'url'        => $delProdUrl,
            'options'    => $delProdOptions
        ];
    
        $this->request->expects($this->at(0))->method('getParam')
            ->with($this->equalTo($this->storeIdKey))
            ->will($this->returnValue($this->storeId));
        $this->request->expects($this->at(1))->method('getParam')
            ->with($this->equalTo($this->chunkIdKey))
            ->will($this->returnValue($this->chunkId));
        $this->request->expects($this->at(2))->method('getParam')
            ->with($this->equalTo($this->importTypeKey))
            ->will($this->returnValue('deletedProducts'));
    
        $this->deletedProductModel->expects($this->any())->method('getDeletedProductOrders')
            ->with($this->equalTo($this->storeId))
            ->will($this->returnValue($this->orderCollection));
    
        $this->orderCollection->expects($this->any())->method('create')
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->any())->method('addFieldToFilter')
            ->with($this->equalTo('entity_id'), $this->isType('array'))
            ->will($this->returnValue($this->orderCollection));
    
        $this->deletedProductSerializer->expects($this->any())->method('serialize')
            ->with($this->assertInstanceOf(OrderCollection::class, $this->orderCollection))
            ->will($this->returnValue($expected));
    
        $this->apiClient->expects($this->any())->method('productBatch')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->response));
    
        $this->ajaxController->execute();
    }
    
    public function testExecuteProducts()
    {
        $productCategories = [1,2,3];
        $productId         = 9090;
        $productSku        = 'productSku';
        $productImageUrl   = 'productImageUrl';
        $productName       = 'productName';
        $productPrice      = 1001;
        $productUrl        = 'productUrl';
        $productOptions[]  = [
            'id'       => 9091,
            'sku'      => 'childProductSku',
            'name'     => 'childProductName',
            'price'    => 1003,
            'imageUrl' => 'childProductImageUrl'
        ];
        
        $expected = [
            'categories' => $productCategories,
            'id'         => $productId,
            'sku'        => $productSku,
            'imageUrl'   => $productImageUrl,
            'name'       => $productName,
            'price'      => $productPrice,
            'url'        => $productUrl,
            'options'    => $productOptions
        ];
    
        $this->request->expects($this->at(0))->method('getParam')
            ->with($this->equalTo($this->storeIdKey))
            ->will($this->returnValue($this->storeId));
        $this->request->expects($this->at(1))->method('getParam')
            ->with($this->equalTo($this->chunkIdKey))
            ->will($this->returnValue($this->chunkId));
        $this->request->expects($this->at(2))->method('getParam')
            ->with($this->equalTo($this->importTypeKey))
            ->will($this->returnValue('products'));
    
        $this->productModel->expects($this->any())->method('getProducts')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->chunkId))
            ->will($this->returnValue($this->productCollection));
    
        $this->productCollection->expects($this->any())->method('setPageSize')
            ->with($this->equalTo($this->chunkItems));
        $this->productCollection->expects($this->any())->method('setCurPage')
            ->with($this->equalTo($this->chunkId));
        $this->productCollection->expects($this->any())->method('setDataToAll')
            ->with($this->equalTo('store_id'), $this->equalTo($this->storeId));
        $this->productCollection->expects($this->any())->method('getStoreId')
            ->with($this->equalTo($this->storeId));
        $this->productCollection->expects($this->any())->method('getId')
            ->will($this->returnValue($productId));
        $this->productCollection->expects($this->any())->method('getTypeId')
            ->will($this->returnValue('simple'));
        $this->productCollection->expects($this->any())->method('getImage')
            ->will($this->returnValue($productImageUrl));
        $this->productCollection->expects($this->any())->method('getPrice')
            ->will($this->returnValue($productPrice));
        $this->productCollection->expects($this->any())->method('getSpecialPrice')
            ->will($this->returnValue(999));
        $this->productCollection->expects($this->any())->method('getCategoryIds')
            ->will($this->returnValue($productCategories));
        $this->productCollection->expects($this->any())->method('getSku')
            ->will($this->returnValue($productSku));
        $this->productCollection->expects($this->any())->method('getName')
            ->will($this->returnValue($productName));
    
        $this->productSerializer->expects($this->any())->method('serialize')
            ->with($this->assertInstanceOf(ProductCollection::class, $this->productCollection))
            ->will($this->returnValue($expected));
    
        $this->apiClient->expects($this->any())->method('productBatch')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->response));
    
        $this->ajaxController->execute();
    }
    
    public function testExecuteOrders()
    {
        $orderIncrementId = 1000000001;
        $orderTimeStamp   = 1928310923;
        $orderEmail       = 'order@email.com';
        $orderAmount      = 900;
        $orderCoupons     = ['couponCode'];
        $orderStatus      = 'orderStatus';
        $orderProducts[]  = [
            'productId'  => 'orderItemSku',
            'quantity'   => 3
        ];
        $orderBilling     = [
            "firstName"     => 'customerFirstName',
            "lastName"      => 'customerLastName',
            "address"       => 'streetAddress',
            "city"          => 'orderCity',
            "countryCode"   => 'orderCountryCode',
            "phone"         => 'orderPhone',
            "postcode"      => 'orderPostcode',
            "paymentMethod" => 'orderPaymentMethodName'
        ];
        
        $expected = [
            'id'        => $orderIncrementId,
            'createdAt' => $orderTimeStamp,
            'email'     => $orderEmail,
            'amount'    => $orderAmount,
            'coupons'   => $orderCoupons,
            'status'    => $orderStatus,
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
    
        $this->request->expects($this->at(0))->method('getParam')
            ->with($this->equalTo($this->storeIdKey))
            ->will($this->returnValue($this->storeId));
        $this->request->expects($this->at(1))->method('getParam')
            ->with($this->equalTo($this->chunkIdKey))
            ->will($this->returnValue($this->chunkId));
        $this->request->expects($this->at(2))->method('getParam')
            ->with($this->equalTo($this->importTypeKey))
            ->will($this->returnValue('orders'));
    
        $this->orderModel->expects($this->any())->method('getOrders')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->chunkId))
            ->will($this->returnValue($this->orderCollection));
    
        $this->orderCollection->expects($this->any())->method('setPageSize')
            ->with($this->equalTo($this->chunkItems));
        $this->orderCollection->expects($this->any())->method('setCurPage')
            ->with($this->equalTo($this->chunkId));
    
        $this->orderSerializer->expects($this->any())->method('serialize')
            ->with($this->assertInstanceOf(OrderCollection::class, $this->orderCollection))
            ->will($this->returnValue($expected));
    
        $this->apiClient->expects($this->any())->method('orderBatch')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->response));
    
        $this->ajaxController->execute();
    }
}
