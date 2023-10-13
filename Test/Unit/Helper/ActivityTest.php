<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Helper\Activity;
use Metrilo\Analytics\Api\Client;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{
    private Activity $activityHelper;

    private Data $dataHelper;

    private ApiClient $apiClientHelper;

    private Client $client;

    /**
     * @var String
     */
    private string $type = 'integrated';

    private int $storeId = 1;

    public function setUp(): void
    {
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApiToken', 'getApiSecret', 'getActivityEndpoint'])
            ->getMock();

        $this->apiClientHelper = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();

        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['createActivity'])
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->activityHelper = new Activity($this->dataHelper, $this->apiClientHelper, $context);
    }

    public function testCreateActivity()
    {
        $this->dataHelper->expects($this->any())->method('getApiToken')
            ->with($this->equalTo($this->storeId))
            ->willReturn('9b4dd74a736d9d7d');
        $this->dataHelper->expects($this->any())->method('getApiSecret')
            ->with($this->equalTo($this->storeId))
            ->willReturn('82535e6593b51afed58e0a5a');
        $this->dataHelper->expects($this->any())->method('getActivityEndpoint')
            ->willReturn('https://p.metrilo.com');

        $this->apiClientHelper->expects($this->any())->method('getClient')
            ->with($this->equalTo($this->storeId))
            ->willReturn($this->client);

        $data = [
            'type'   => $this->type,
            'secret' => $this->dataHelper->getApiSecret($this->storeId)
        ];

        $url = $this->dataHelper->getActivityEndpoint() . '/tracking/' .
            $this->dataHelper->getApiToken($this->storeId) . '/activity';

        $this->client->expects($this->any())->method('createActivity')
            ->with($this->equalTo($url), $this->equalTo($data))
            ->willReturn(true);

        $this->assertTrue($this->activityHelper->createActivity($this->storeId, $this->type));
    }
}
