<?php

    namespace Metrilo\Analytics\Api;

    use \Metrilo\Analytics\Api\Validator;
    use \Metrilo\Analytics\Api\Connector;

    class Client {
    
        public function __construct(
            $token,
            $platform,
            $pluginVersion,
            $apiEndpoint,
            $logPath
        ) {
            $this->backendParams['token']         = $token;
            $this->backendParams['platform']      = $platform;
            $this->backendParams['pluginVersion'] = $pluginVersion;
            $this->apiEndpoint                    = $apiEndpoint;
            $this->validator                      = new Validator($logPath);
        }
    
        public function backendCall($path, $body) {
            $connection                  = new Connection();
            $this->backendParams['time'] = round(microtime(true) * 1000);
            $body                        = array_merge($body, $this->backendParams);
        
            return $connection->post($this->apiEndpoint.$path, $body);
        }
    
        public function customer($customer) {
            $validCustomer = $this->validator->validateCustomer($customer);
            
            if ($validCustomer) {
                return $this->backendCall('/customer', ['params' => $customer]);
            }
        }
     
        public function customerBatch($customers) {
            $validCustomers = $this->validator->validateCustomers($customers);
            
            if (!empty($validCustomers)) {
                return $this->backendCall('/customer/batch', ['batch' => $validCustomers]);
            }
        }
    
        public function category($category) {
            $validCategory = $this->validator->validateCategory($category);
            
            if ($validCategory) {
                return $this->backendCall('/category', ['params' => $category]);
            }
        }
    
        public function categoryBatch($categories) {
            $validCategories = $this->validator->validateCategories($categories);
            
            if (!empty($validCategories)) {
                return $this->backendCall('/category/batch', ['batch' => $validCategories]);
            }
        }
    
        public function product($product) {
            $validProduct = $this->validator->validateProduct($product);
            
            if ($validProduct) {
                return $this->backendCall('/product', ['params' => $product]);
            }
        }
    
        public function productBatch($products) {
            $validProducts = $this->validator->validateProducts($products);
            
            if (!empty($validProducts)) {
                return $this->backendCall('/product/batch', ['batch' => $validProducts]);
            }
        }
    
        public function order($order) {
            $validOrder = $this->validator->validateOrder($order);
            
            if ($validOrder) {
                return $this->backendCall('/order', ['params' => $order]);
            }
        }
    
        public function orderBatch($orders) {
            $validOrders = $this->validator->validateOrders($orders);
            
            if (!empty($validOrders)) {
                return $this->backendCall('/order/batch', ['batch' => $validOrders]);
            }
        }
    
        public function createActivity($url, $data) {
            $connection = new Connection();
            $result     = $connection->post($url, $data, true);
            return $result['code'] == 200;
        }
    }
