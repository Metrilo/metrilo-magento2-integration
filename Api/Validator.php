<?php
    
    namespace Metrilo\Analytics\Api;
    
    class Validator {
        public $logDestination = __DIR__ . '/MetriloApiErrors.log'; // construct log path in ajax controller and pass it as constructor param for Validator class
    
        public $errors = [];
    
        public function check($var){
            $this->var = $var;
            
            return $this;
        }
    
        public function value($value){
            $this->value = $value;
            
            return $this;
        }
    
        public function required(){
            if($this->value == '' || $this->value == null){
                $this->errors[] = 'Field ' . $this->var . ' is required. ';
            }
            
            return $this;
        }
        
        public function isSuccess(){
            if(empty($this->errors)) return true;
        }
    
        public function getErrors(){
            if(!$this->isSuccess()) return $this->errors;
        }
    
        public function isString(){
            if(!is_string($this->value)) {
                $this->errors[] = 'Field ' . $this->var . ' is not String type. ';
            }
            
            return $this;
        }
    
        public function isInt(){
            if(!filter_var($this->value, FILTER_VALIDATE_INT)) {
                $this->errors[] = 'Field ' . $this->var . ' is not Integer type. ';
            }
            
            return $this;
        }
    
        public function isEmail(){
            if(!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = 'Field ' . $this->var . ' is not a valid email address. ';
            }
            
            return $this;
        }
    
        public function isNumeric(){
            if(!is_numeric($this->value)) {
                $this->errors[] = 'Field ' . $this->var . ' is not Number. ';
            }
            
            return $this;
        }
        
        public function logger($error) {
            $validationErrors = implode("| ", $this->getErrors());
            $error           .= $validationErrors;
            return error_log($error . PHP_EOL, 3, $this->logDestination);
        }
        
        public function validateCustomer($customer) {
            $this->check('email')->value($customer['email'])->required()->isString()->isEmail();
            $this->check('createdAt')->value($customer['createdAt'])->required()->isInt();
            
            if (!$this->isSuccess()) {
                $error = 'Customer ' . $customer['firstName'] . ' ' . $customer['lastName'] . ' errors: ';
                $this->logger($error);
            }
            return $customer;
        }
        
        public function validateCustomers($customers = []) {
            $index = 0;
            foreach ($customers as $customer) {
                $this->validateCustomer($customer);
                if (!$this->isSuccess()) {
                    unset($customers[$index]);
                    $this->errors = [];
                }
                $index++;
            }
            
            return $customers;
        }
        
        public function validateCategory($category) {
            $this->check('id')->value($category['id'])->required()->isString();
            $this->check('name')->value($category['name'])->required()->isString();
            
            if (!$this->isSuccess()) {
                $error = 'Category ' . $category['url'] . ' errors: ';
                $this->logger($error);
            }
            return $category;
        }
        
        public function validateCategories($categories = []) {
            $index = 0;
            foreach ($categories as $category) {
                $this->validateCategory($category);
                if (!$this->isSuccess()) {
                    unset($categories[$index]);
                    $this->errors = [];
                }
                $index++;
            }
    
            return $categories;
        }
        
        public function validateProduct($product) {
            foreach ($product['categories'] as $category) {
                $this->check('categoryId')->value($category)->required()->isString();
            }
            $this->check('id')->value($product['id'])->required()->isString();
            $this->check('name')->value($product['name'])->required()->isString();
            
            if (!empty($product['options'])) {
                foreach ($product['options'] as $option) {
                    $this->check('optionId')->value($option['productId'])->required()->isString();
                    $this->check('optionName')->value($option['name'])->required()->isString();
                    $this->check('optionPrice')->value($option['price'])->required()->isNumeric();
                }
            }
            
            if (!$this->isSuccess()) {
                $error = 'Product with SKU - ' . $product['sku'] . ' errors: ';
                $this->logger($error);
            }
            
            return $product;
        }
        
        public function validateProducts($products = []) {
            $index = 0;
            foreach ($products as $product) {
                $this->validateProduct($product);
                if (!$this->isSuccess()) {
                    unset($products[$index]);
                    $this->errors = [];
                }
                $index++;
            }
        
            return $products;
        }
        
        public function validateOrder($order) {
            $this->check('orderId')->value($order['id'])->required()->isString();
            $this->check('createdAt')->value($order['createdAt'])->required()->isInt();
            $this->check('amount')->value($order['amount'])->required()->isNumeric();
            $this->check('status')->value($order['status'])->required()->isString();
            $this->check('products')->value($order['products'])->required();
        
            if (!empty($order['products'])) {
                foreach ($order['products'] as $product) {
                    $this->check('productId')->value($product['productId'])->required()->isString();
                    $this->check('quantity')->value($product['quantity'])->required()->isNumeric();
                }
            }
        
            if (!$this->isSuccess()) {
                $error = 'Order with email - ' . $order['email'] . ' errors: ';
                $this->logger($error);
            }
            
            return $order;
        }
        
        public function validateOrders($orders = []) {
            $index = 0;
            foreach ($orders as $order) {
                $this->validateOrder($order);
                if (!$this->isSuccess()) {
                    unset($orders[$index]);
                    $this->errors = [];
                }
                $index++;
            }
        
            return $orders;
        }
    }