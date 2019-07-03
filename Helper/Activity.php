<?php

namespace Metrilo\Analytics\Helper;

class Activity extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function createActivity($storeId, $type, $token, $secret)
    {
        $data = array(
            'type'          => $type,
            'project_token' => $token,
            'signature'     => md5($token . $type . $secret)
        );
        
        $url = 'http://p.metrilo.com/tracking/' . $token . '/activity';
        
        return array('url' => $url, 'data' => $data);
    }
}