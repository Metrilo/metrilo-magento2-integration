<?php

namespace Metrilo\Analytics\Helper;

class Activity extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Metrilo\Analytics\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }
    
    public function createActivity($storeId, $type)
    {
        $token    = $this->dataHelper->getApiToken($storeId);
        $secret   = $this->dataHelper->getApiSecret($storeId);
        $endPoint = $this->dataHelper->getApiEndpoint();
        
        $data = array(
            'type'          => $type,
            'project_token' => $token,
            'signature'     => md5($token . $type . $secret)
        );
        
        $url = $endPoint . '/tracking/' . $token . '/activity';
        
        return array('url' => $url, 'data' => $data);
    }
}