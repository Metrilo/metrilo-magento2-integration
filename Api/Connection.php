<?php

namespace Metrilo\Analytics\Api;

class Connection
{
    /**
     * Create HTTP POST request to URL
     *
     * @param String $url
     * @param Array $bodyArray
     * @return void
     */
    public function post($url, $bodyArray = false, $hmacAuth = false)
    {
        $parsedUrl = parse_url($url);
        $headers = [
            'Content-Type: application/json',
            'Accept: */*',
            'User-Agent: HttpClient/1.0.0',
            'Connection: Close',
            'Host: '.$parsedUrl['host']
        ];
        
        $secret = $bodyArray['secret'];
        unset($bodyArray['secret']);
        $headers[] = 'X-Digest: ' . hash_hmac('sha256', json_encode($bodyArray), $secret);

        $encodedBody = $bodyArray ? json_encode($bodyArray) : '';
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
    private function curlCall($url, $headers = [], $body = '', $method = "POST")
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

        return [
            'response' => $response,
            'code' => $code
        ];
    }
}
