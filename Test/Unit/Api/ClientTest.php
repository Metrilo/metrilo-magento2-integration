<?php

namespace Metrilo\Analytics\Test\Unit\Api;

use Metrilo\Analytics\Api\Validator;
use \Metrilo\Analytics\Api\Connection;
use \Metrilo\Analytics\Api\Client;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    private $logPath = BP . '/var/log';
    private $validator;
    private $connection;
    private $client;
    
    public function setup()
    {
        $this->validator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'validateCustomer',
                'validateCustomers',
                'validateCategory',
                'validateCategories',
                'validateProduct',
                'validateProducts',
                'validateOrder',
                'validateOrders',
                'createActivity'])
            ->getMock();
    
        $this->connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['post'])
            ->getMock();
        
        $this->client = new Client(
            '9b4dd74a736d9d7d',
            'Magento 2.3.0',
            '2.0.0',
            'https://trk.mtrl.me',
            $this->logPath
        );
    }
    
    public function testCustomer()
    {
        $serializedCustomer = [
            'email'       => 'customer@email.com',
            'createdAt'   => 1518004715732,
            'firstName'   => 'customerFirstName',
            'lastName'    => 'customerLastName',
            'subscribed'  => true,
            'tags'        => ['customer_group']
        ];
        
        $this->validator->expects($this->any())->method('validateCustomer')
            ->with($this->equalTo($serializedCustomer))
            ->will($this->returnValue(true));
        
        $expected = [
            'response' => '',
            'code'     => 204
        ];
        
        $result = $this->client->customer($serializedCustomer);
        
        $this->assertSame($expected, $result);
    }
    
    public function testCustomerBatch()
    {
        $serializedCustomers[] = [
            'email'       => 'customers@email.com',
            'createdAt'   => 1518004715732,
            'firstName'   => 'customersFirstName',
            'lastName'    => 'customersLastName',
            'subscribed'  => false,
            'tags'        => ['customers_group']
        ];
    
        $serializedCustomers[] = [
            'email'       => 'subscriber@email.com',
            'createdAt'   => 1518004715739,
            'firstName'   => 'subscriberFirstName',
            'lastName'    => 'subscriberLastName',
            'subscribed'  => true,
            'tags'        => ['subscriber_group']
        ];
        
        $this->validator->expects($this->any())->method('validateCustomers')
            ->with($this->equalTo($serializedCustomers))
            ->will($this->returnValue($serializedCustomers));
        
        $expected = [
            'response' => '',
            'code'     => 204
        ];
        
        $result = $this->client->customerBatch($serializedCustomers);
        
        $this->assertSame($expected, $result);
    }
    
    public function testCategory()
    {
        $category = [
            'id'   => '123',
            'name' => 'categoryName',
            'url'  => 'https://base/url/string/request/path.html'
        ];
        
        $this->validator->expects($this->any())->method('validateCategory')
            ->with($this->equalTo($category))
            ->will($this->returnValue(true));
    
        $expected = [
            'response' => '',
            'code'     => 204
        ];
    
        $result = $this->client->category($category);
    
        $this->assertSame($expected, $result);
    }
    
    public function testCategoryBatch()
    {
        $categories[] = [
            'id'   => '1234',
            'name' => 'firstCategoryName',
            'url'  => 'https://base/url/string/request/path/firstCat.html'
        ];
    
        $categories[] = [
            'id'   => '1235',
            'name' => 'secondCategoryName',
            'url'  => 'https://base/url/string/request/path/secondCat.html'
        ];
        
        $this->validator->expects($this->any())->method('validateCategories')
            ->with($this->equalTo($categories))
            ->will($this->returnValue($categories));
        
        $expected = [
            'response' => '',
            'code'     => 204
        ];
        
        $result = $this->client->categoryBatch($categories);
        
        $this->assertSame($expected, $result);
    }
    
    public function testProduct()
    {
        $productOptions[] = [
                'id'       => 'productOptionSku',
                'sku'      => 'productOptionSku',
                'name'     => 'productOptionName',
                'price'    => 123,
                'imageUrl' => 'https://base/url/string/catalog/product/product/image/url.jpg'
        ];
        
        $product = [
            'categories' => ['1', '2'],
            'id'         => '1',
            'sku'        => 'productSku',
            'imageUrl'   => 'https://base/url/string/catalog/product/product/image/url.jpg',
            'name'       => 'productName',
            'price'      => 0,
            'url'        => 'https://base/url/string/product/request/path.html',
            'options'    => $productOptions
        ];
        
        $this->validator->expects($this->any())->method('validateProduct')
            ->with($this->equalTo($product))
            ->will($this->returnValue(true));
    
        $expected = [
            'response' => '',
            'code'     => 204
        ];
    
        $result = $this->client->product($product);
    
        $this->assertSame($expected, $result);
    }
    
    public function testProductBatch()
    {
        $firstProductOptions[] = [
            'id'       => 'firstProductOptionSku',
            'sku'      => 'firstProductOptionSku',
            'name'     => 'firstProductOptionName',
            'price'    => 123,
            'imageUrl' => 'https://base/url/string/catalog/product/image/firstProductOption.jpg'
        ];
        
        $products[] = [
            'categories' => ['11', '22'],
            'id'         => '11',
            'sku'        => 'firstProductSku',
            'imageUrl'   => 'https://base/url/string/catalog/product/image/firstProduct.jpg',
            'name'       => 'firstProductName',
            'price'      => 0,
            'url'        => 'https://base/url/string/product/request/firstProduct.html',
            'options'    => $firstProductOptions
        ];
        
        $products[] = [
            'categories' => ['33', '44'],
            'id'         => '6',
            'sku'        => 'secondProductSku',
            'imageUrl'   => 'https://base/url/string/catalog/product/image/secondProduct.jpg',
            'name'       => 'secondProductName',
            'price'      => 332,
            'url'        => 'https://base/url/string/product/request/secondProduct.html',
            'options'    => []
        ];
        
        $this->validator->expects($this->any())->method('validateProducts')
            ->with($this->equalTo($products))
            ->will($this->returnValue($products));
        
        $expected = [
            'response' => '',
            'code'     => 204
        ];
        
        $result = $this->client->productBatch($products);
        
        $this->assertSame($expected, $result);
    }
    
    public function testOrder()
    {
        $orderProducts[] = [
            'productId'  => 'productSku',
            'quantity'   => 1
        ];
        
        $orderBilling = [
            "firstName"     => 'firstName',
            "lastName"      => 'lastName',
            "address"       => 'orderAddress',
            "city"          => 'orderCity',
            "countryCode"   => 'BG',
            "phone"         => 883444555,
            "postcode"      => '1000',
            "paymentMethod" => 'Bank Transfer'
        ];
        
        $order = [
            'id'        => '111',
            'createdAt' => 1518004715749,
            'email'     => 'order@email.com',
            'amount'    => 124,
            'coupons'   => ['coupon_code'],
            'status'    => 'processing',
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
        
        $this->validator->expects($this->any())->method('validateOrder')
            ->with($this->equalTo($order))
            ->will($this->returnValue(true));
    
        $expected = [
            'response' => '',
            'code'     => 204
        ];
    
        $result = $this->client->order($order);
    
        $this->assertSame($expected, $result);
    }
    
    public function testOrderBatch()
    {
        $orderProducts[] = [
            'productId'  => 'productSku',
            'quantity'   => 1
        ];
        
        $orderBilling = [
            "firstName"     => 'firstName',
            "lastName"      => 'lastName',
            "address"       => 'orderAddress',
            "city"          => 'orderCity',
            "countryCode"   => 'BG',
            "phone"         => 883444555,
            "postcode"      => '1000',
            "paymentMethod" => 'Bank Transfer'
        ];
        
        $orders[] = [
            'id'        => '222',
            'createdAt' => 1518004715747,
            'email'     => 'firstOrder@email.com',
            'amount'    => 111,
            'coupons'   => ['first_coupon_code'],
            'status'    => 'canceled',
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
    
        $orders[] = [
            'id'        => '333',
            'createdAt' => 1518004715748,
            'email'     => 'secondOrder@email.com',
            'amount'    => 119,
            'coupons'   => ['second_coupon_code'],
            'status'    => 'shipped',
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
        
        $this->validator->expects($this->any())->method('validateOrders')
            ->with($this->equalTo($orders))
            ->will($this->returnValue($orders));
        
        $expected = [
            'response' => '',
            'code'     => 204
        ];
        
        $result = $this->client->orderBatch($orders);
        
        $this->assertSame($expected, $result);
    }
    
    public function testActivity()
    {
        $token    = '9b4dd74a736d9d7d';
        $endPoint = 'https://p.metrilo.com';
        
        $data = [
            'type'   => 'activity_type',
            'secret' => '82535e6593b51afed58e0a5a'
        ];
        
        $url = $endPoint . '/tracking/' . $token . '/activity';
        
        $result = $this->client->createActivity($url, $data);
        
        $this->assertTrue($result);
    }
}