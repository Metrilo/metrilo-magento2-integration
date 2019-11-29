<?php

    namespace Metrilo\Analytics\Api;

    class Connection {
        /**
         * Create HTTP POST request to URL
         *
         * @param String $url
         * @param Array $bodyArray
         * @return void
         */
        public function post($url, $bodyArray = false, $hmac_auth = false)
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
            
            if ($hmac_auth) {
                $headers[] = 'HTTP_X_DIGEST: ' . $encodedBody;
                
                return $this->curlCall($url, $headers, '');
            } else {
                return $this->curlCall($url, $headers, $encodedBody);
            }
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
    }
