<?php

namespace Metrilo\Analytics\Test\Unit\Api;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Metrilo\Analytics\Api\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    private Connection $connection;

    private string $url = 'https://trk.mtrl.me/api';

    private array $bodyArray = ['data' => 'value', 'secret' => '82535e6593b51afed58e0a5a'];

    private Curl $curl;

    private Json $json;

    public function setUp(): void
    {
        $connectionFactory = $this->getMockBuilder(CurlFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->curl = $this->getMockBuilder(Curl::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $connectionFactory->expects($this->once())
                          ->method('create')
                          ->will($this->returnValue($this->curl));

        $this->json = $this->getMockBuilder(Json::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->connection = new Connection($connectionFactory, $this->json);
    }

    public function testPostWithHmacAuth()
    {
        $response = [
            'response' => '{"result":"success"}',
            'code' => 200
        ];

        $encodedBody = json_encode($this->bodyArray);
        $this->json->expects($this->once())
                   ->method('serialize')
                   ->with($this->bodyArray)
                   ->will($this->returnValue($encodedBody));
        $hmac = hash_hmac('sha256', $encodedBody, '82535e6593b51afed58e0a5a');

        $this->curl->expects($this->once())
                   ->method('setOptions')
                   ->with($this->isType('array'));

        $this->curl->expects($this->exactly(6))
                   ->method('addHeader')
                   ->withConsecutive(
                       ['Content-Type', 'application/json'],
                       ['Accept', '*/*'],
                       ['User-Agent', 'HttpClient/1.0.0'],
                       ['Connection', 'Close'],
                       ['Host', 'trk.mtrl.me'],
                       ['X-Digest', $hmac],
                   );

        $this->curl->expects($this->once())
                   ->method('post')
                   ->with($this->url, $encodedBody);

        $this->curl->expects($this->once())->method('getBody')
                   ->will($this->returnValue('{"result":"success"}'));

        $this->curl->expects($this->once())->method('getStatus')
                   ->will($this->returnValue(200));

        $result = $this->connection->post($this->url, $this->bodyArray, '82535e6593b51afed58e0a5a');

        $this->assertEquals($response, $result);
    }

    public function testPostWithoutBodyArray()
    {
        $response = [
            'response' => '{"result":"success"}',
            'code' => 200
        ];

        $this->json->expects($this->once())
                   ->method('serialize')
                   ->with(null)
                   ->will($this->throwException(new \InvalidArgumentException));

        $this->curl->expects($this->once())
                   ->method('setOptions')
                   ->with($this->isType('array'));

        $this->curl->expects($this->exactly(5))
                   ->method('addHeader')
                   ->withConsecutive(
                       ['Content-Type', 'application/json'],
                       ['Accept', '*/*'],
                       ['User-Agent', 'HttpClient/1.0.0'],
                       ['Connection', 'Close'],
                       ['Host', 'trk.mtrl.me'],
                   );

        $this->curl->expects($this->once())
                   ->method('post')
                   ->with($this->url, '');

        $this->curl->expects($this->once())->method('getBody')
                   ->will($this->returnValue('{"result":"success"}'));

        $this->curl->expects($this->once())->method('getStatus')
                   ->will($this->returnValue(200));

        $result = $this->connection->post($this->url, null, '82535e6593b51afed58e0a5a');

        $this->assertEquals($response, $result);
    }
}
