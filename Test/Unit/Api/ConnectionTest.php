<?php

namespace Metrilo\Analytics\Test\Unit\Api;

use Metrilo\Analytics\Api\Connection;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $connection;
    
    private $response = [
            'response' => 'responseObject',
            'code'     => 'responseCode'
        ];
    private $url = 'https://trk.mtrl.me';
    private $bodyArray = ['data' => 'value', 'secret' => '82535e6593b51afed58e0a5a'];
    
    public function setUp()
    {
        $this->connection = new Connection();
    }
    
    public function testPostWithHmacAuth()
    {
        $this->response = [
            'response' => '',
            'code'     => 404
        ];

        $result = $this->connection->post($this->url, $this->bodyArray, true);

        $this->assertEquals($this->response, $result);
    }
    
    public function testPostWithoutHmacAuth()
    {
        $this->response = [
            'response' => '',
            'code'     => 404
        ];
        
        $result = $this->connection->post($this->url, $this->bodyArray, false);
        
        $this->assertEquals($this->response, $result);
    }
    
    public function testPostWithBodyArray()
    {
        $this->response = [
            'response' => '',
            'code'     => 404
        ];
    
        $result = $this->connection->post($this->url, false, false);
    
        $this->assertEquals($this->response, $result);
    }
}
