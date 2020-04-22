<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\ApiClient;
use Metrilo\Analytics\Api\Client;

class ApiClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $metaData;
    
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;
    
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directoryList;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\ApiClient
     */
    private $apiClient;
    
    /**
     * @var \Metrilo\Analytics\Api\Client
     */
    private $client;
    
    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private $storeId = 1;
    
    public function setUp()
    {
        $this->metaData = $this->getMockBuilder(ProductMetadata::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEdition', 'getVersion'])
            ->getMock();
    
        $this->moduleList = $this->getMockBuilder(ModuleListInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOne', 'getAll', 'getNames', 'has'])
            ->getMock();
    
        $this->directoryList = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPath'])
            ->getMock();
    
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getApiToken', 'getApiEndpoint'])
            ->getMock();
        
        $this->apiClient = new ApiClient($this->dataHelper, $this->metaData, $this->moduleList, $this->directoryList);
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
        
        $this->directoryList->expects($this->any())->method('getPath')
            ->with($this->equalTo('log'))
            ->will($this->returnValue('path/to/log/dir'));
        
        $this->dataHelper->expects($this->any())->method('getApiToken')
            ->with($this->equalTo($this->storeId))
            ->will($this->returnValue('9b4dd74a736d9d7d'));
        $this->dataHelper->expects($this->any())->method('getApiEndpoint')
            ->will($this->returnValue('https://trk.mtrl.me'));
        
        $this->assertInstanceOf(Client::class, $this->apiClient->getClient($this->storeId));
    }
}