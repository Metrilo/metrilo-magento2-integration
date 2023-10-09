<?php

namespace Metrilo\Analytics\Test\Unit\Api;

use Magento\Framework\Logger\Monolog;
use Metrilo\Analytics\Api\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    public function setUp(): void
    {
        $logger = $this->getMockBuilder(Monolog::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['error'])
                       ->getMock();
        $this->validator = new Validator($logger);
    }

    public function testValidateCustomer()
    {
        $customer = [
            'email'       => 'customer@email.com',
            'createdAt'   => 1278312378,
            'firstName'   => 'firstName',
            'lastName'    => 'lastName',
            'subscribed'  => true,
            'tags'        => 'customerGroup'
        ];

        $this->assertTrue($this->validator->validateCustomer($customer));
    }

    public function testValidateCustomers()
    {
        $customers[] = [
            'email'       => 'customer@email.com',
            'createdAt'   => 1278312378,
            'firstName'   => 'firstName',
            'lastName'    => 'lastName',
            'subscribed'  => true,
            'tags'        => 'customerGroup'
        ];

        $customers[] = [
            'email'       => 'customer2email.com',
            'createdAt'   => '1278312378',
            'firstName'   => 'customerFirstName',
            'lastName'    => 'customerLastName',
            'subscribed'  => true,
            'tags'        => 'customerGroup'
        ];

        $result = $this->validator->validateCustomers($customers);

        $this->assertEquals(1, count($result));
    }

    public function testValidateCategory()
    {
        $category = [
            'id'   => '123',
            'name' => 'categoryName',
            'url'  => 'base/url/string/url/request/path'
        ];

        $this->assertTrue($this->validator->validateCategory($category));
    }

    public function testValidateCategories()
    {
        $categories[] = [
            'id'   => '123',
            'name' => 'categoryName',
            'url'  => 'base/url/string/url/request/path'
        ];

        $categories[] = [
            'id'   => 1223,
            'name' => 'categoryName',
            'url'  => 'base/url/string/url/request/path'
        ];

        $result = $this->validator->validateCategories($categories);

        $this->assertEquals(1, count($result));
    }

    public function testValidateProduct()
    {
        $baseUrl        = 'base/url/string/';
        $imageUrl       = '/product/image/url.jpg';
        $requestPath    = 'product/request/path.html';

        $productOptions[] = [
            'id'       => 'productSku',
            'sku'      => 'productSku',
            'name'     => 'productName',
            'price'    => '120',
            'imageUrl' => 'base/url/string/catalog/product/product/image/url.jpg'
        ];

        $product = [
            'categories' => ['1', '3', '4'],
            'id'         => '1',
            'sku'        => 'productSku',
            'imageUrl'   => $baseUrl . 'catalog/product' . $imageUrl,
            'name'       => 'productName',
            'price'      => 0,
            'url'        => $baseUrl . $requestPath,
            'options'    => $productOptions
        ];

        $this->assertTrue($this->validator->validateProduct($product));
    }

    public function testValidateProducts()
    {
        $baseUrl        = 'base/url/string/';
        $imageUrl       = '/product/image/url.jpg';
        $requestPath    = 'product/request/path.html';

        $products[] = [
            'categories' => ['1', '3', '4'],
            'id'         => '1',
            'sku'        => 'productSku',
            'imageUrl'   => $baseUrl . 'catalog/product' . $imageUrl,
            'name'       => 'productName',
            'price'      => 0,
            'url'        => $baseUrl . $requestPath,
            'options'    => array([
                'id'       => 'optionSku',
                'sku'      => 'optionSku',
                'name'     => 'optionName',
                'price'    => '120',
                'imageUrl' => 'base/url/string/catalog/product/product/image/url.jpg'
                ])
            ];

        $products[] = [
            'categories' => ['2', '6'],
            'id'         => '12',
            'sku'        => 'productSku',
            'imageUrl'   => $baseUrl . 'catalog/product' . $imageUrl,
            'name'       => 'productName',
            'price'      => 0,
            'url'        => $baseUrl . $requestPath,
            'options'    => []
            ];

        $products[] = [
            'categories' => [],
            'id'         => 123,
            'sku'        => 'failProductSku',
            'imageUrl'   => $baseUrl . 'catalog/product' . $imageUrl,
            'name'       => 'failProductName',
            'price'      => 333,
            'url'        => $baseUrl . $requestPath,
            'options'    => []
        ];

        $products[] = [
            'categories' => [],
            'id'         => 1234,
            'sku'        => 'failProductOptionSku',
            'imageUrl'   => $baseUrl . 'catalog/product' . $imageUrl,
            'name'       => 'failProductName',
            'price'      => 0,
            'url'        => $baseUrl . $requestPath,
            'options'    => array([
                'id'       => 'failOptionSku',
                'sku'      => 'failOptionSku',
                'name'     => 'failOptionName',
                'price'    => '130',
                'imageUrl' => 'base/url/string/catalog/product/product/image/url.jpg'
            ])
        ];

        $result = $this->validator->validateProducts($products);

        $this->assertEquals(2, count($result));
    }

    public function validateOrder()
    {
        $orderProducts[] = [
            'productId'  => 'itemSku',
            'quantity'   => 3
        ];

        $orderBilling = [
            "firstName"     => 'orderFirstName',
            "lastName"      => 'orderLastName',
            "address"       => 'streetAddress',
            "city"          => 'cityName',
            "countryCode"   => 'countryCode',
            "phone"         => '0883444666',
            "postcode"      => 'postCode',
            "paymentMethod" => 'paymentMethodName'
        ];

        $order = [
            'id'        => 1000000001,
            'createdAt' => 18296312,
            'email'     => 'customer@email.com',
            'amount'    => 1001,
            'coupons'   => ['couponCode'],
            'status'    => 'orderStatus',
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];

        $this->assertTrue($this->validator->validateOrder($order));
    }

    public function validateOrders()
    {
        $orders[] = [
            'id'        => 1000000001,
            'createdAt' => 18296312,
            'email'     => 'customer@email.com',
            'amount'    => 1001,
            'coupons'   => ['couponCode'],
            'status'    => 'orderStatus',
            'products'  => array([
                'productId'  => 'itemSku',
                'quantity'   => 3
            ]),
            'billing'   => [
                "firstName"     => 'orderFirstName',
                "lastName"      => 'orderLastName',
                "address"       => 'streetAddress',
                "city"          => 'cityName',
                "countryCode"   => 'countryCode',
                "phone"         => '0883444666',
                "postcode"      => 'postCode',
                "paymentMethod" => 'paymentMethodName'
            ]
        ];

        $orders[] = [
            'id'        => 1000000002,
            'createdAt' => 155943172,
            'email'     => 'second@order.com',
            'amount'    => 812,
            'coupons'   => [],
            'status'    => 'orderStatus',
            'products'  => [],
            'billing'   => [
                "firstName"     => 'secondOrderFirstName',
                "lastName"      => 'secondOrderLastName',
                "address"       => 'secondOrderStreetAddress',
                "city"          => 'secondOrderCityName',
                "countryCode"   => 'secondOrderCountryCode',
                "phone"         => '0883222777',
                "postcode"      => 'secondOrderPostCode',
                "paymentMethod" => 'secondOrderPaymentMethodName'
            ]
        ];

        $orders[] = [
            'id'        => 1000000003,
            'createdAt' => 18296234,
            'email'     => 'third@order.com',
            'amount'    => 201,
            'coupons'   => [],
            'status'    => 'thirdOrderStatus',
            'products'  => array(
                [
                    'productId'  => 'optionItemSku',
                    'quantity'   => 3
                ], [
                    'productId'  => 'secondOptionItemSku',
                    'quantity'   => 1
                ]
            ),
            'billing'   => [
                "firstName"     => 'thirdOrderFirstName',
                "lastName"      => 'thirdOrderLastName',
                "address"       => 'thirdOrderStreetAddress',
                "city"          => 'thirdOrderCityName',
                "countryCode"   => 'thirdOrderCountryCode',
                "phone"         => '0883555999',
                "postcode"      => 'thirdOrderPostCode',
                "paymentMethod" => 'thirdOrderPaymentMethodName'
            ]
        ];

        $orders[] = [
            'id'        => 1000000004,
            'createdAt' => 155949928,
            'email'     => 'last@order.com',
            'amount'    => 812,
            'coupons'   => ['lastCouponCode'],
            'status'    => 'lastOrderStatus',
            'products'  => [],
            'billing'   => [
                "firstName"     => 'lastOrderFirstName',
                "lastName"      => 'lastOrderLastName',
                "address"       => 'lastOrderStreetAddress',
                "city"          => 'lastOrderCityName',
                "countryCode"   => 'lastOrderCountryCode',
                "phone"         => '0883111000',
                "postcode"      => 'lastOrderPostCode',
                "paymentMethod" => 'lastOrderPaymentMethodName'
            ]
        ];

        $result = $this->validator->validateOrders($orders);

        $this->assertEquals(2, count($result));
    }
}
