<?php

namespace Metrilo\Analytics\Helper;

class Activity extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Metrilo\Analytics\Helper\Data      $dataHelper,
        \Metrilo\Analytics\Helper\ApiClient $apiClient
    ) {
        $this->dataHelper = $dataHelper;
        $this->apiClient  = $apiClient;
    }
    
    public function createActivity($storeId, $type)
    {
        $token    = $this->dataHelper->getApiToken($storeId);
        $secret   = $this->dataHelper->getApiSecret($storeId);
        $endPoint = $this->dataHelper->getActivityEndpoint();
        $client   = $this->apiClient->getClient($storeId);
        
        $data = [
            'type' => $type
        ];
        
        $signature = hash_hmac('sha256', json_encode($data), $secret);
        
        $url = $endPoint . '/tracking/' . $token . '/activity'; // should be modified once the enpoint is ready
        
        return $client->createActivity($url, $signature);
    }
}
