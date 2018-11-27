<?php
    
    namespace Metrilo\Analytics\Api;
    
    class Validator {
        private $logDestination = __DIR__ . '/MetriloApiErrors.log'; // construct log path in ajax controller and pass it as constructor param for Validator class
        
        public function logger($error, $logDestination) {
            return error_log($error, 3, $logDestination);
        }
        
        public function validateCustomer($customer) {
            $error = 'Customer ' . $customer['firstName'] . ' ' . $customer['lastName'];
            if (empty($customer['email']) || empty($customer['createdAt'])) {
                $error .= ' have missing email or createdAt timestamp!' . PHP_EOL;
                $this->logger($error, $this->logDestination);
            } else {
                if (is_string($customer['email']) && is_int($customer['createdAt'])) {
                    if (!filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
                        $error .= ' have invalid email address! (' . $customer['email'] . ')' . PHP_EOL;
                        $this->logger($error, $this->logDestination);
                    }
                } else {
                    if (!is_string($customer['email']) || !is_int($customer['createdAt'])) {
                        $error .= ' have invalid email/createdAt type! Should be string/integer.' . PHP_EOL;
                        $this->logger($error, $this->logDestination);
                    }
                }
            }
            
            return $customer;
        }
        
        public function validateCustomers($customers = []) {
            foreach ($customers as $customer) {
                $this->validateCustomer($customer);
            }
            
            return $customers;
        }
        
        public function validateCategory($category) {
            $error = 'Category - ' . $category['url'];
            if (empty($category['id']) || empty($category['name'])) {
                $error .= ' have missing id/name! ' . PHP_EOL;
                $this->logger($error, $this->logDestination);
            } else {
                if (!is_string($category['id']) || !is_string($category['name'])) {
                    $error .= ' have invalid id/name type! Should be string.' . PHP_EOL;
                    $this->logger($error, $this->logDestination);
                }
            }
            
            return $category;
        }
        
        public function validateCategories($categories = []) {
            foreach ($categories as $category) {
                $this->validateCategory($category);
            }
            
            return $categories;
        }
        
        public function validateProduct($product) {
            foreach ($product['categories'] as $category) {
                $categoryError = 'Product with SKU - ' . $product['sku'];
                if (empty($category)) {
                    $categoryError .= ' have missing category id!' . PHP_EOL;
                    $this->logger($categoryError, $this->logDestination);
                } else {
                    if (!is_string($category)) {
                        $categoryError .= ' have invalid id type! Should be string.' . PHP_EOL;
                        $this->logger($categoryError, $this->logDestination);
                    }
                }
            }
            
            $error  = 'Product with SKU - ' . $product['sku'];
            if (empty($product['id']) || empty($product['name'])) {
                $error .= ' have missing id/name!' . PHP_EOL;
                $this->logger($error, $this->logDestination);
            } else {
                if (!is_string($product['id']) || !is_string($product['name'])) {
                    $error .= ' have invalid id/name type! Should be string/string.' . PHP_EOL;
                    $this->logger($error, $this->logDestination);
                }
            }
            
            if (!empty($product['options'])) {
                foreach ($product['options'] as $option) {
                    $optionError  = 'Product with SKU1 - ' . $product['sku'];
                    if (empty($option['productId']) || empty($option['name']) || empty($option['price'])) {
                        $optionError .= ' have missing option id/name/price!' . PHP_EOL;
                        $this->logger($optionError, $this->logDestination);
                    } else {
                        if (!is_string($option['productId']) || !is_string($option['name']) || !is_numeric($option['price'])) { // so according to api spec $option['price'] needs to be number, doesn't matter the type.
                            $optionError .= ' have invalid productId/name/price type! Should be string/string/numeric.' . PHP_EOL;
                            $this->logger($optionError, $this->logDestination);
                        }
                    }
                }
            }
            
            return $product;
        }
        
        public function validateProducts($products = []) {
            foreach ($products as $product) {
                $this->validateProduct($product);
            }
            
            return $products;
        }
        
        public function validateOrder($order) {
            $error = 'Order with email - ' . $order['email'];
            if (empty($order['id']) || empty($order['createdAt']) || empty($order['amount']) || empty($order['status']) || empty($order['products'])) {
                $error .= ' have missing id/timestamp/amount/status/products!' . PHP_EOL;
                $this->logger($error, $this->logDestination);
            } else {
                if (!is_string($order['id']) || !is_int($order['createdAt']) || !is_numeric($order['amount']) || !is_string($order['status'])) { // so according to api spec $order['amount'] needs to be number, doesn't matter the type.
                    $error .= ' have invalid id/timestamp/amount/status type! Should be string/integer/numeric/string.' . PHP_EOL;
                    $this->logger($error, $this->logDestination);
                }
            }
            
            if (!empty($order['products'])) {
                foreach ($order['products'] as $product) {
                    $productError = 'Order with ID - ' . $order['id'];
                    if (empty($product['productId']) || empty($product['quantity'])) {
                        $productError .= ' have missing option id/quantity!' . PHP_EOL;
                        $this->logger($productError, $this->logDestination);
                    } else {
                        if (!is_string($product['productId']) || !is_numeric($product['quantity'])) { // so according to api spec $product['quantity'] needs to be number, doesn't matter the type.
                            $productError .= ' have invalid productId/quantity type! Should be string/number.' . PHP_EOL;
                            $this->logger($productError, $this->logDestination);
                        }
                    }
                }
            }
            
            return $order;
        }
        
        public function validateOrders($orders = []) {
            foreach ($orders as $order) {
                $this->validateOrder($order);
            }
            
            return $orders;
        }
    }