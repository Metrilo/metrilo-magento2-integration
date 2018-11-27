<?php

    namespace Metrilo\Analytics\Api;

    use \Metrilo\Analytics\Api\Validator;

    class Client {
    
        const VERSION = '1.0.0';
//        private $url  = 'https://postb.in/kzTiQWNl';
        private $url  = 'https://postman-echo.com/post';
    
        public function __construct(
            array $backendParams = []
        ) {
            $this->backendParams = $backendParams;
            $this->validator     = new Validator();
        }
    
        /**
         * Create HTTP POST request to URL
         *
         * @param String $url
         * @param Array $bodyArray
         * @return void
         */
        public function post($url, $bodyArray = false)
        {
            $encodedBody = $bodyArray ? json_encode($bodyArray) : '';
            $parsedUrl = parse_url($url);
            $headers = [
                'Content-Type: application/json',
                'Accept: */*',
                'User-Agent: HttpClient/1.0.0',
                'Connection: Close',
                'Host: '.$parsedUrl['host']
            ];
            return $this->curlCall($url, $headers, $encodedBody);
        }
    
        /**
         * CURL call
         *
         * @param string $url
         * @param array $headers
         * @param string $body
         * @param string $method
         * @return void
         */
        public function curlCall($url, $headers = [], $body = '', $method = "POST")
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 2000);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 3000);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    
            $response = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
            curl_close($curl);
    
            return array(
                'response' => $response,
                'code' => $code
            );
        }
    
        public function backendCall($path, $body) {
            // concatenate path with url
            $body = array_merge($body, $this->backendParams);
        
            return $this->post($this->url, $body);
        }
    
        public function customer($customer) {
            $validatedCustomer = $this->validator->validateCustomer($customer);
        
            return $this->backendCall('/customer', ['params' => $validatedCustomer]);
        }
     
        public function customerBatch($customers) { // usable for both single and batch
            $validatedCustomers = $this->validator->validateCustomers($customers);
        
            return $this->backendCall('/customer/batch', ['batch' => $validatedCustomers]);
        }
    
        public function category($category) {
            $validatedCategory = $this->validator->validateCategory($category);
        
            return $this->backendCall('/category', ['params' => $validatedCategory]);
        }
    
        public function categoryBatch($categories) {
            $validatedCategories = $this->validator->validateCategories($categories);
        
            return $this->backendCall('/category/batch', ['batch' => $validatedCategories]);
        }
    
        public function product($product) {
            $validatedProduct = $this->validator->validateProduct($product);
        
            return $this->backendCall('/product', ['params' => $validatedProduct]);
        }
    
        public function productBatch($products) {
            $validatedProducts = $this->validator->validateProducts($products);
        
            return $this->backendCall('/product/batch', ['batch' => $validatedProducts]);
        }
    
        public function order($order) {
            $validatedOrder = $this->validator->validateOrder($order);
        
            return $this->backendCall('/order', ['params' => $validatedOrder]);
        }
    
        public function orderBatch($orders) {
            $validatedOrders = $this->validator->validateOrders($orders);
        
            return $this->backendCall('/order/batch', ['batch' => $validatedOrders]);
        }
    }