<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json;
use Metrilo\Analytics\Api\ClientFactory;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Api\Client;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private ProductMetadata $metaData;

    private Data $dataHelper;

    private ApiClient $apiClient;

    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private int $storeId = 1;

    private ComponentRegistrarInterface $componentRegistrar;

    private Json $json;

    private ReadInterface $read;

    private ClientFactory $clientFactory;

    private Client $client;

    public function setUp(): void
    {
        $this->metaData = $this->getMockBuilder(ProductMetadata::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getEdition', 'getVersion'])
                               ->getMock();

        $this->dataHelper = $this->getMockBuilder(Data::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(['getApiToken', 'getApiSecret', 'getApiEndpoint'])
                                 ->getMock();

        $this->read = $this->getMockBuilder(ReadInterface::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $readFactory = $this->getMockBuilder(ReadFactory::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $readFactory->expects($this->once())
                    ->method('create')
                    ->with($this->isType('string'))
                    ->will($this->returnValue($this->read));

        $this->componentRegistrar = $this->getMockBuilder(ComponentRegistrarInterface::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $this->json = $this->getMockBuilder(Json::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->client = $this->getMockBuilder(Client::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->clientFactory = $this->getMockBuilder(ClientFactory::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->apiClient = new ApiClient(
            $this->dataHelper,
            $this->metaData,
            $this->clientFactory,
            $this->componentRegistrar,
            $readFactory,
            $this->json,
            $context
        );
    }

    public function testGetClient()
    {
        $this->metaData->expects($this->any())->method('getEdition')
                       ->will($this->returnValue('123'));
        $this->metaData->expects($this->any())->method('getVersion')
                       ->will($this->returnValue('321'));

        $this->componentRegistrar->expects($this->once())
                                 ->method('getPath')
                                 ->with(ComponentRegistrar::MODULE, 'Metrilo_Analytics')
                                 ->will($this->returnValue('some/path/to/package'));

        $encoded = '{"name":"metrilo\/analytics-magento2-extension","version":"2.1.0"}';

        $this->read->expects($this->once())
                   ->method('readFile')
                   ->with('composer.json')
                   ->will($this->returnValue($encoded));

        $this->json->expects($this->once())
                   ->method('unserialize')
                   ->with($encoded)
                   ->will(
                       $this->returnValue([
                           "name" => "metrilo/analytics-magento2-extension",
                           "version" => "2.1.0"
                       ])
                   );

        $this->dataHelper->expects($this->any())->method('getApiToken')
                         ->with($this->equalTo($this->storeId))
                         ->will($this->returnValue('9b4dd74a736d9d7d'));
        $this->dataHelper->expects($this->any())->method('getApiEndpoint')
                         ->will($this->returnValue('https://trk.mtrl.me'));

        $this->dataHelper->expects($this->once())->method('getApiSecret')
                         ->with($this->storeId)->will($this->returnValue('secret-token-here'));

        $this->clientFactory->expects($this->once())
                            ->method('create')
                            ->with([
                                'token' => '9b4dd74a736d9d7d',
                                'secret' => 'secret-token-here',
                                'platform' => 'Magento 123 321',
                                'pluginVersion' => '2.1.0',
                                'apiEndpoint' => 'https://trk.mtrl.me',
                            ])
                            ->will($this->returnValue($this->client));

        $this->assertInstanceOf(Client::class, $this->apiClient->getClient($this->storeId));
    }
}
