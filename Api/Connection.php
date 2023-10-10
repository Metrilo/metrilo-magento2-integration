<?php

namespace Metrilo\Analytics\Api;

use Laminas\Uri\UriFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Connection
{
    private CurlFactory $curlFactory;

    private Json $json;

    public function __construct(
        CurlFactory $curlFactory,
        Json $json
    ) {
        $this->curlFactory = $curlFactory;
        $this->json = $json;
    }

    /**
     * Create HTTP POST request to URL
     *
     * @param string $url
     * @param array|null $bodyArray
     * @param string $secret
     *
     * @return array
     */
    public function post($url, $bodyArray = null, $secret = ''): array
    {
        $parsedUrl = UriFactory::factory($url);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'User-Agent' => 'HttpClient/1.0.0',
            'Connection' => 'Close',
            'Host' => $parsedUrl->getHost()
        ];
        try {
            $body = $this->json->serialize($bodyArray);
        } catch (\InvalidArgumentException) {
            $body = '';
        }

        if ($body) {
            $headers['X-Digest'] = hash_hmac('sha256', $body, $secret);
        }

        return $this->curlCall($url, $headers, $body);
    }

    /**
     * CURL call
     *
     * @param string $url
     * @param array $headers
     * @param string $body
     * @return array
     */
    private function curlCall(string $url, array $headers = [], string $body = ''): array
    {
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();

        $curl->setOptions([
            CURLOPT_COOKIESESSION => true,
            CURLOPT_CONNECTTIMEOUT_MS => 2000,
            CURLOPT_TIMEOUT_MS => 3000,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        foreach ($headers as $header => $value) {
            $curl->addHeader($header, $value);
        }

        $curl->post($url, $body);

        return [
            'response' => $curl->getBody(),
            'code' => $curl->getStatus()
        ];
    }
}
