<?php

namespace Metrilo\Analytics\Helper;

/**
 * Helper for sending async requests to Metrilo end points
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Async extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * Create HTTP GET async request to URL
    *
    * @param String $url
    * @return void
    */
    public function get($url)
    {
        $parsedUrl = parse_url($url);
        $raw = $this->_buildRawGet($parsedUrl['host'], $parsedUrl['path']);
        $fp = fsockopen(
            $parsedUrl['host'],
            isset($parsedUrl['port']) ? $parsedUrl['port'] : 80,
            $errno,
            $errstr,
            30
        );
        if ($fp) {
            fwrite($fp, $raw);
            fclose($fp);
        }
    }

    private function _curlPost($url, $body)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($body)]);

        $server_output = curl_exec ($ch);

        $this->_logger->debug($server_output);

        curl_close($ch);

    }

    /**
    * Create HTTP POSTasync request to URL
    *
    * @param String $url
    * @param Array $bodyArray
     * @param $async
    * @return void
    */
    public function post($url, $bodyArray = false, $async = true)
    {

        $encodedBody = $bodyArray ? json_encode($bodyArray) : '';

        $this->_curlPost($url, $encodedBody);
    }

    /**
     * Build headers as string for GET requests
     *
     * @param  string $host
     * @param  string $path
     * @return string
     */
    protected function _buildRawGet($host, $path)
    {
        $out  = "GET ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        // $out .= "Accept: application/json\r\n";
        $out .= "Connection: Close\r\n\r\n";
        return $out;
    }

    /**
     * Build headers for POST request
     *
     * @param  string $host
     * @param  string $path
     * @return string
     */
    protected function _buildRawPost($host, $path, $encodedCall)
    {
        $out  = "POST ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Content-Length: ".strlen($encodedCall)."\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "User-Agent: AsyncHttpClient/1.0.0\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $encodedCall;
        return $out;
    }

    /**
     * @param $fp
     */
    private function _waitForResponse($fp)
    {
        while (!feof($fp)) {
            fgets($fp, 1024);
        }
    }
}
