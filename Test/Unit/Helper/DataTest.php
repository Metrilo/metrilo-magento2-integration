<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\Data;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $data;
    
    /**
     * @var \Magento\Framework\App\Request\Http->getParam('store', 0)
     */
    private $storeId = 1;
    
    public function setUp(): void
    {
        $this->config = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getId'])
            ->getMockForAbstractClass();
        
        $this->data = new Data($this->config, $this->storeManager);
    }
    
    public function testGetStoreId()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnSelf());
        $this->storeManager->expects($this->any())->method('getId')->will($this->returnValue('1'));
        
        $this->assertEquals(1, $this->data->getStoreId());
    }
    
    public function testIsEnabled()
    {
        $this->config->expects($this->any())->method('getValue')
            ->with(
                $this->equalTo('metrilo_analytics/general/enable'),
                $this->equalTo('store'),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue(true));
        
        $this->assertTrue($this->data->isEnabled($this->storeId));
    }
    
    public function testGetApiToken()
    {
        $this->config->expects($this->any())->method('getValue')
            ->with(
                $this->equalTo('metrilo_analytics/general/api_key'),
                $this->equalTo('store'),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('9b4dd74a736d9d7d'));
    
        $this->assertEquals('9b4dd74a736d9d7d', $this->data->getApiToken($this->storeId));
    }
    
    public function testGetApiSecret()
    {
        $this->config->expects($this->any())->method('getValue')
            ->with(
                $this->equalTo('metrilo_analytics/general/api_secret'),
                $this->equalTo('store'),
                $this->equalTo($this->storeId)
            )
            ->will($this->returnValue('82535e6593b51afed58e0a5a'));
    
        $this->assertEquals('82535e6593b51afed58e0a5a', $this->data->getApiSecret($this->storeId));
    }
    
    public function testGetApiEndpoint()
    {
        $this->config->expects($this->any())->method('getValue')
            ->with($this->equalTo('metrilo_analytics/general/api_endpoint'))
            ->will($this->returnValue('https://trk.mtrl.me'));
    
        $this->assertEquals('https://trk.mtrl.me', $this->data->getApiEndpoint());
    }
    
    public function testGetActivityEndpoint()
    {
        $this->config->expects($this->any())->method('getValue')
            ->with($this->equalTo('metrilo_analytics/general/activity_endpoint'))
            ->will($this->returnValue('https://p.metrilo.com'));
    
        $this->assertEquals('https://p.metrilo.com', $this->data->getActivityEndpoint());
    }
    
    public function testLogError()
    {
        $logLocation = BP . '/var/log/metrilo.log';
        if (file_exists($logLocation)) {
            unlink($logLocation);
        }
        
        $this->data->logError('test');
        $result = file_exists($logLocation);
        
        $this->assertTrue($result);
        $this->assertFalse(filesize($logLocation) > 10 * 1024 * 1024);
    }
    
    public function testGetStoreIdsPerProject()
    {
        $this->config->expects($this->at(0))->method('getValue')
            ->with(
                $this->equalTo('metrilo_analytics/general/enable'),
                $this->equalTo('store'),
                $this->isType('int')
            )
            ->will($this->returnValue(true));
        
        $this->config->expects($this->at(1))->method('getValue')
            ->with(
                $this->equalTo('metrilo_analytics/general/api_key'),
                $this->equalTo('store'),
                $this->isType('int')
            )
            ->will($this->returnValue('9b4dd74a736d9d7d'));
        
        $this->assertEquals([1], $this->data->getStoreIdsPerProject([1,3,4]));
    }
}
