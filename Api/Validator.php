<?php
    
    namespace Metrilo\Analytics\Api;
    
    class Validator {
        
        public $errors = [];
    
        public function __construct(
            $logPath
        ) {
            $this->logPath = $logPath . '/MetriloApiValidationErrors.log';
        }
    
        public function check($var){
            $this->var = $var;
            
            return $this;
        }
    
        public function value($value){
            $this->value = $value;
            
            return $this;
        }
    
        public function required(){
            if($this->value === '' || $this->value === null || $this->value === []){
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
            if(!is_int($this->value)) {
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
            return error_log($error . PHP_EOL, 3, $this->logPath);
        }
        
        public function validateCustomer($customer) {
            $this->check('email')->value($customer['email'])->required()->isString()->isEmail();
            $this->check('createdAt')->value($customer['createdAt'])->required()->isInt();
            
            if ($this->isSuccess()) {
                return true;
            } else {
                $error = 'Customer ' . $customer['firstName'] . ' ' . $customer['lastName'] . ' errors: ';
                $this->logger($error);
                $this->errors = [];
                return false;
            }
        }
        
        public function validateCustomers($customers = []) {
            $validCustomers = [];
            foreach ($customers as $customer) {
                $validCustomer = $this->validateCustomer($customer);
                if ($validCustomer) {
                    $validCustomers[] = $customer;
                }
            }
            
            return $validCustomers;
        }
        
        public function validateCategory($category) {
            $this->check('id')->value($category['id'])->required()->isString();
            $this->check('name')->value($category['name'])->required()->isString();
            
            if ($this->isSuccess()) {
                return true;
            } else {
                $error = 'Category ' . $category['url'] . ' errors: ';
                $this->logger($error);
                $this->errors = [];
                return false;
            }
        }
        
        public function validateCategories($categories = []) {
            $validCategories = [];
            foreach ($categories as $category) {
                $validCategory = $this->validateCategory($category);
                if ($validCategory) {
                    $validCategories[] = $category;
                }
            }
    
            return $validCategories;
        }
        
        public function validateProduct($product) {
            $this->check('id')->value($product['id'])->required()->isString();
            $this->check('name')->value($product['name'])->required()->isString();
            
            if (empty($product['options'])) {
                $this->check('price')->value($product['price'])->required()->isNumeric();
            } else {
                foreach ($product['options'] as $option) {
                    $this->check('optionId')->value($option['id'])->required()->isString();
                    $this->check('optionName')->value($option['name'])->required()->isString();
                    $this->check('optionPrice')->value($option['price'])->required()->isNumeric();
                }
            }
            
            if ($this->isSuccess()) {
                return true;
            } else {
                $error = 'Product with SKU - ' . $product['sku'] . ' errors: ';
                $this->logger($error);
                $this->errors = [];
                return false;
            }
        }
        
        public function validateProducts($products = []) {
            $validProducts = [];
            foreach ($products as $product) {
                $validProduct = $this->validateProduct($product);
                if ($validProduct) {
                    $validProducts[] = $product;
                }
            }
            
            return $validProducts;
        }
        
        public function validateOrder($order) {
            $this->check('orderId')->value($order['id'])->required()->isString();
            $this->check('createdAt')->value($order['createdAt'])->required()->isInt();
            $this->check('amount')->value($order['amount'])->required()->isNumeric();
            $this->check('status')->value($order['status'])->required()->isString();
            $this->check('products')->value($order['products'])->required();
            
            foreach ($order['products'] as $product) {
                $this->check('productId')->value($product['productId'])->required()->isString();
                $this->check('quantity')->value($product['quantity'])->required()->isNumeric();
            }
            
            if ($this->isSuccess()) {
                return true;
            } else {
                $error = 'Order with email - ' . $order['email'] . ' errors: ';
                $this->logger($error);
                $this->errors = [];
                return false;
            }
        }
        
        public function validateOrders($orders = []) {
            $validOrders = [];
            foreach ($orders as $order) {
                $validOrder = $this->validateOrder($order);
                if ($validOrder) {
                    $validOrders[] = $order;
                }
            }
            
            return $validOrders;
        }
    }
