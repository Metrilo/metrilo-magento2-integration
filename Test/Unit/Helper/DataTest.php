<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\Data;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DataTest extends TestCase
{
    private ScopeConfigInterface $config;

    private StoreManagerInterface $storeManager;

    private Data $data;

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
        $logger = $this->getMockBuilder(LoggerInterface::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $context->method('getScopeConfig')->will($this->returnValue($this->config));

        $this->data = new Data($this->storeManager, $logger, $context);
    }

    public function testGetStoreId()
    {
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnSelf());
        $this->storeManager->expects($this->any())->method('getId')->will($this->returnValue('1'));

        $this->assertEquals(1, $this->data->getStoreId());
    }

    public function testIsEnabled()
    {
        $this->config->expects($this->any())->method('isSetFlag')
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

    public function testGetStoreIdsPerProject()
    {

        $this->config->expects($this->any())->method('isSetFlag')
                     ->willReturnMap([
                         ['metrilo_analytics/general/enable', ScopeInterface::SCOPE_STORE, 1, true],
                         ['metrilo_analytics/general/enable', ScopeInterface::SCOPE_STORE, 3, true],
                         ['metrilo_analytics/general/enable', ScopeInterface::SCOPE_STORE, 4, true]
                     ]);

        $this->config->expects($this->any())->method('isSetFlag')
                     ->willReturnMap([
                         ['metrilo_analytics/general/api_key', ScopeInterface::SCOPE_STORE, 1, '9b4dd74a736d9d7d'],
                         ['metrilo_analytics/general/api_key', ScopeInterface::SCOPE_STORE, 3, '9b4dd74a736d9d7d'],
                         ['metrilo_analytics/general/api_key', ScopeInterface::SCOPE_STORE, 4, '9b4dd74a736d9d7d']
                     ]);

        $this->assertEquals([1], $this->data->getStoreIdsPerProject([1, 3, 4]));
    }
}
