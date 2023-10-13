<?php

namespace Metrilo\Analytics\Api;

class Client
{
    private $customerPath = '/v2/customer';

    private $categoryPath = '/v2/category';

    private $productPath = '/v2/product';

    private $orderPath = '/v2/order';

    private $secret;

    private array $backendParams = [];

    private $apiEndpoint;

    private Validator $validator;

    private ConnectionFactory $connectionFactory;

    public function __construct(
        $token,
        $secret,
        $platform,
        $pluginVersion,
        $apiEndpoint,
        ValidatorFactory $validatorFactory,
        ConnectionFactory $connectionFactory
    ) {
        $this->backendParams['token'] = $token;
        $this->secret = $secret;
        $this->backendParams['platform'] = $platform;
        $this->backendParams['pluginVersion'] = $pluginVersion;
        $this->apiEndpoint = $apiEndpoint;
        $this->validator = $validatorFactory->create();
        $this->connectionFactory = $connectionFactory;
    }

    public function customer($customer)
    {
        $validCustomer = $this->validator->validateCustomer($customer);

        if ($validCustomer) {
            return $this->backendCall($this->customerPath, ['params' => $customer]);
        }
    }

    public function customerBatch($customers)
    {
        $validCustomers = $this->validator->validateCustomers($customers);

        if (!empty($validCustomers)) {
            return $this->backendCall($this->customerPath . '/batch', ['batch' => $validCustomers]);
        }
    }

    public function category($category)
    {
        $validCategory = $this->validator->validateCategory($category);

        if ($validCategory) {
            return $this->backendCall($this->categoryPath, ['params' => $category]);
        }
    }

    public function categoryBatch($categories)
    {
        $validCategories = $this->validator->validateCategories($categories);

        if (!empty($validCategories)) {
            return $this->backendCall($this->categoryPath . '/batch', ['batch' => $validCategories]);
        }
    }

    public function product($product)
    {
        $validProduct = $this->validator->validateProduct($product);

        if ($validProduct) {
            return $this->backendCall($this->productPath, ['params' => $product]);
        }
    }

    public function productBatch($products)
    {
        $validProducts = $this->validator->validateProducts($products);

        if (!empty($validProducts)) {
            return $this->backendCall($this->productPath . '/batch', ['batch' => $validProducts]);
        }
    }

    public function order($order)
    {
        $validOrder = $this->validator->validateOrder($order);

        if ($validOrder) {
            return $this->backendCall($this->orderPath, ['params' => $order]);
        }
    }

    public function orderBatch($orders)
    {
        $validOrders = $this->validator->validateOrders($orders);

        if (!empty($validOrders)) {
            return $this->backendCall($this->orderPath . '/batch', ['batch' => $validOrders]);
        }
    }

    public function createActivity($url, $data)
    {
        $connection = $this->connectionFactory->create();
        $result = $connection->post($url, $data, $this->secret);

        return $result['code'] == 200;
    }

    private function backendCall($path, $body)
    {
        $connection = $this->connectionFactory->create();
        $this->backendParams['time'] = round(microtime(true) * 1000);
        $body = array_merge($body, $this->backendParams);

        return $connection->post($this->apiEndpoint . $path, $body, $this->secret);
    }
}
