<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Metrilo\Analytics\Api\Client;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\ProductSerializer;
use Metrilo\Analytics\Helper\ProductOptions;
use Metrilo\Analytics\Model\ProductData;
use Metrilo\Analytics\Observer\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private Observer $observer;

    private Client $client;

    private ProductModel $productModel;

    private Data $dataHelper;

    private ApiClient $apiClientHelper;

    private ProductSerializer $productSerializer;

    private ProductOptions $productOptionsHelper;

    private ProductData $productData;

    private Collection $productCollection;

    private Product $productObserver;

    public function setUp(): void
    {
        $this->observer = $this->getMockBuilder(Observer::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getEvent', 'getProduct'])
                               ->getMock();

        $this->client = $this->getMockBuilder(Client::class)
                             ->disableOriginalConstructor()
                             ->setMethods(['product'])
                             ->getMock();

        $this->productModel = $this->getMockBuilder(ProductModel::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['getStoreId', 'getStoreIds', 'getId', 'getTypeId'])
                                   ->getMock();

        $this->dataHelper = $this->getMockBuilder(Data::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(['isEnabled', 'logError', 'getStoreIdsPerProject'])
                                 ->getMock();

        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
                                      ->disableOriginalConstructor()
                                      ->setMethods(['getClient'])
                                      ->getMock();

        $this->productSerializer = $this->getMockBuilder(ProductSerializer::class)
                                        ->disableOriginalConstructor()
                                        ->setMethods(['serialize'])
                                        ->getMock();

        $this->productOptionsHelper = $this->getMockBuilder(ProductOptions::class)
                                           ->disableOriginalConstructor()
                                           ->setMethods(['getParentIds', 'getTypeId'])
                                           ->getMock();

        $this->productData = $this->getMockBuilder(ProductData::class)
                                  ->disableOriginalConstructor()
                                  ->setMethods(['getProductWithRequestPath'])
                                  ->getMock();

        $this->productCollection = $this->getMockBuilder(Collection::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->productObserver = new Product(
            $this->dataHelper,
            $this->apiClientHelper,
            $this->productSerializer,
            $this->productData,
            $this->productOptionsHelper
        );
    }

    public function testImplementsTheObserverInterface()
    {
        $this->assertInstanceOf(
            ObserverInterface::class,
            new Product(
                $this->dataHelper,
                $this->apiClientHelper,
                $this->productSerializer,
                $this->productData,
                $this->productOptionsHelper
            )
        );
    }

    public function testExecute()
    {
        $storeId = 1;
        $storeIds = [1, 2, 3];
        $productId = 2;
        $productType = 'configurable';

        $this->observer->expects($this->any())->method('getEvent')
                       ->will($this->returnSelf());
        $this->observer->expects($this->any())->method('getProduct')
                       ->will($this->returnValue($this->productModel));

        $this->productModel->expects($this->any())->method('getStoreId')
                           ->will($this->returnValue($storeId));
        $this->productModel->expects($this->any())->method('getStoreIds')
                           ->will($this->returnValue($storeIds));
        $this->productModel->expects($this->any())->method('getId')
                           ->will($this->returnValue($productId));
        $this->productModel->expects($this->any())->method('getTypeId')
                           ->will($this->returnValue($productType));

        $this->dataHelper->expects($this->any())->method('isEnabled')
                         ->with($this->isType('int'))
                         ->will($this->returnValue(true));
        $this->dataHelper->expects($this->any())->method('logError')
                         ->with($this->isType('object'));
        $this->dataHelper->expects($this->any())->method('getStoreIdsPerProject')
                         ->with($this->isType('array'))
                         ->will($this->returnValue($storeIds));

        $this->apiClientHelper->expects($this->any())->method('getClient')
                              ->with($this->equalTo($storeId))
                              ->will($this->returnValue($this->client));

        $this->client->expects($this->any())->method('product')
                     ->with($this->isInstanceOf(ProductSerializer::class));

        $this->productOptionsHelper->expects($this->any())->method('getParentIds')
                                   ->with($this->equalTo($productId), $this->equalTo($productType))
                                   ->will($this->returnValue([]));

        $this->productSerializer->expects($this->any())->method('serialize')
                                ->with($this->isInstanceOf(ProductSerializer::class))
                                ->will($this->returnValue([]));

        $this->productData->expects($this->any())->method('getProductWithRequestPath')
                          ->with($this->isType('int'), $this->equalTo($storeId))
                          ->will($this->returnValue($this->productCollection));

        $this->productObserver->execute($this->observer);
    }
}
