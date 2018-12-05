<?php

    namespace Metrilo\Analytics\Api;

    use \Metrilo\Analytics\Api\Validator;
    use \Metrilo\Analytics\Api\Connector;

    class Client {
    
//        private $url  = 'https://postb.in/kzTiQWNl';
        private $url  = 'https://postman-echo.com/post';
    
        public function __construct(
            $token,
            $platform,
            $pluginVersion
        ) {
            $this->backendParams['token']         = $token;
            $this->backendParams['platform']      = $platform;
            $this->backendParams['pluginVersion'] = $pluginVersion;
        }
    
        public function backendCall($path, $body) {
            $connection                  = new Connection();
            $this->backendParams['time'] = time();
            $body                        = array_merge($body, $this->backendParams);
            
            return $connection->post($this->url, $body);
        }
    
        public function customer($customer) {
            $validator     = new Validator();
            $validCustomer = $validator->validateCustomer($customer);
            
            if ($validCustomer) {
                return $this->backendCall('/customer', ['params' => $customer]);
            }
        }
     
        public function customerBatch($customers) {
            $validator      = new Validator();
            $validCustomers = $validator->validateCustomers($customers);
            
            if (!empty($validCustomers)) {
                return $this->backendCall('/customer/batch', ['batch' => $validCustomers]);
            }
        }
    
        public function category($category) {
            $validator     = new Validator();
            $validCategory = $validator->validateCategory($category);
            
            if ($validCategory) {
                return $this->backendCall('/category', ['params' => $category]);
            }
        }
    
        public function categoryBatch($categories) {
            $validator       = new Validator();
            $validCategories = $validator->validateCategories($categories);
            
            if (!empty($validCategories)) {
                return $this->backendCall('/customer/batch', ['batch' => $validCategories]);
            }
        }

        public function product($product) {
            $validator    = new Validator();
            $validProduct = $validator->validateProduct($product);
            
            if ($validProduct) {
                return $this->backendCall('/product', ['params' => $product]);
            }
        }
    
        public function productBatch($products) {
            $validator     = new Validator();
            $validProducts = $validator->validateProducts($products);
            
            if (!empty($validProducts)) {
                return $this->backendCall('/product/batch', ['batch' => $validProducts]);
            }
        }
    
        public function order($order) {
            $validator  = new Validator();
            $validOrder = $validator->validateOrder($order);
            
            if ($validOrder) {
                return $this->backendCall('/order', ['params' => $order]);
            }
        }
    
        public function orderBatch($orders) {
            $validator   = new Validator();
            $validOrders = $validator->validateOrders($orders);
            
            if (!empty($validOrders)) {
                return $this->backendCall('/order/batch', ['batch' => $validOrders]);
            }
        }
    }