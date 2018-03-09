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

    /**
    * Create HTTP POSTasync request to URL
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
		    'User-Agent: AsyncHttpClient/1.0.0',
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
