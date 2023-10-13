<?php

namespace Metrilo\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Activity extends AbstractHelper
{
    private Data $dataHelper;

    private ApiClient $apiClient;

    public function __construct(
        Data $dataHelper,
        ApiClient $apiClient,
        Context $context
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->apiClient = $apiClient;
    }

    public function createActivity($storeId, $type)
    {
        $token = $this->dataHelper->getApiToken($storeId);
        $secret = $this->dataHelper->getApiSecret($storeId);
        $endPoint = $this->dataHelper->getActivityEndpoint();
        $client = $this->apiClient->getClient($storeId);

        $data = [
            'type' => $type,
            'secret' => $secret
        ];

        $url = $endPoint . '/tracking/' . $token . '/activity';

        return $client->createActivity($url, $data);
    }
}
