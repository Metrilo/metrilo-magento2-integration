<?php

    namespace Metrilo\Analytics\Api;

    use \Metrilo\Analytics\Api\Validator;
    use \Metrilo\Analytics\Api\Connector;

    class Client {
    
//        private $url  = 'https://postb.in/kzTiQWNl';
        private $url  = 'https://postman-echo.com/post';
    
        public function __construct(
            array $backendParams = []
        ) {
            $this->backendParams = $backendParams;
        }
    
        public function backendCall($path, $body) {
            $connection = new Connection();
            // concatenate path with url
            $body = array_merge($body, $this->backendParams);
        
            return $connection->post($this->url, $body);
        }
    
        public function customer($customer) {
            $validator = new Validator();
            $validatedCustomer = $validator->validateCustomer($customer);
        
            return $this->backendCall('/customer', ['params' => $validatedCustomer]);
        }
     
        public function customerBatch($customers) {
            $validator = new Validator();
            $validatedCustomers = $validator->validateCustomers($customers);
        
            return $this->backendCall('/customer/batch', ['batch' => $validatedCustomers]);
        }
    
        public function category($category) {
            $validator = new Validator();
            $validatedCategory = $validator->validateCategory($category);
        
            return $this->backendCall('/category', ['params' => $validatedCategory]);
        }
    
        public function categoryBatch($categories) {
            $validator = new Validator();
            $validatedCategories = $validator->validateCategories($categories);
        
            return $this->backendCall('/customer/batch', ['batch' => $validatedCategories]);
        }

        public function product($product) {
            $validator = new Validator();
            $validatedProduct = $validator->validateProduct($product);
    
            return $this->backendCall('/product', ['params' => $validatedProduct]);
        }
    
        public function productBatch($products) {
            $validator = new Validator();
            $validatedProducts = $validator->validateProducts($products);
    
            return $this->backendCall('/product/batch', ['batch' => $validatedProducts]);
        }
    
        public function order($order) {
            $validator = new Validator();
            $validatedOrder = $validator->validateOrder($order);
    
            return $this->backendCall('/order', ['params' => $validatedOrder]);
        }
    
        public function orderBatch($orders) {
            $validator = new Validator();
            $validatedOrders = $validator->validateOrders($orders);
        
            return $this->backendCall('/order/batch', ['batch' => $validatedOrders]);
        }
    }