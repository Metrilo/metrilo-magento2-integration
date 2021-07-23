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
        $endPoint = $this->dataHelper->getActivityEndpoint();
        $client   = $this->apiClient->getClient($storeId);
        
        $data = [
            'type'   => $type
        ];
        
        $url = $endPoint . '/tracking/' . $token . '/activity';
        
        return $client->createActivity($url, $data);
    }
}
