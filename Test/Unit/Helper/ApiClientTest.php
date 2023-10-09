<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Metrilo\Analytics\Api\ClientFactory;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Api\Client;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private ProductMetadata $metaData;

    private ModuleListInterface $moduleList;

    private Data $dataHelper;

    private ApiClient $apiClient;

    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private $storeId = 1;

    public function setUp(): void
    {
        $this->metaData = $this->getMockBuilder(ProductMetadata::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getEdition', 'getVersion'])
                               ->getMock();

        $this->moduleList = $this->getMockBuilder(ModuleListInterface::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(['getOne', 'getAll', 'getNames', 'has'])
                                 ->getMock();

        $this->dataHelper = $this->getMockBuilder(Data::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(['getApiToken', 'getApiSecret', 'getApiEndpoint'])
                                 ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $clientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientFactory->method('create')->will($this->returnValue($client));

        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->apiClient = new ApiClient(
            $this->dataHelper,
            $this->metaData,
            $this->moduleList,
            $clientFactory,
            $context
        );
    }

    public function testGetClient()
    {
        $this->metaData->expects($this->any())->method('getEdition')
                       ->will($this->returnValue('123'));
        $this->metaData->expects($this->any())->method('getVersion')
                       ->will($this->returnValue('321'));

        $this->moduleList->expects($this->any())->method('getOne')
                         ->with($this->isType('string'))
                         ->will($this->returnValue(['setup_version' => '222']));

        $this->dataHelper->expects($this->any())->method('getApiToken')
                         ->with($this->equalTo($this->storeId))
                         ->will($this->returnValue('9b4dd74a736d9d7d'));
        $this->dataHelper->expects($this->any())->method('getApiEndpoint')
                         ->will($this->returnValue('https://trk.mtrl.me'));

        $this->assertInstanceOf(Client::class, $this->apiClient->getClient($this->storeId));
    }
}
