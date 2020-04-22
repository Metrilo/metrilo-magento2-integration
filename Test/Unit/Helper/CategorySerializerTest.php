<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\CategorySerializer;

class CategorySerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var \Metrilo\Analytics\Helper\CategorySerializer
     */
    private $categorySerializer;
    
    public function setUp()
    {
        $this->categoryCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStoreId', 'getName', 'getRequestPath'])
            ->getMock();
    
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getStore',
                'getBaseUrl',
                'setIsSingleStoreModeAllowed',
                'hasSingleStore',
                'isSingleStoreMode',
                'getStores',
                'getWebsite',
                'getWebsites',
                'reinitStores',
                'getDefaultStoreView',
                'getGroup',
                'getGroups',
                'setCurrentStore'])
            ->getMock();
        
        $this->categorySerializer = new CategorySerializer($this->storeManager);
    }
    
    public function testSerialize() {
        $this->categoryCollection->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->categoryCollection->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->categoryCollection->expects($this->any())->method('getName')->will($this->returnValue('name'));
        $this->categoryCollection->expects($this->any())->method('getRequestPath')->will($this->returnValue('/url/request/path'));
        
        $this->storeManager->expects($this->any())->method('getStore')
            ->with($this->isType('int'))
            ->will($this->returnSelf());
        $this->storeManager->expects($this->any())->method('getBaseUrl')
            ->will($this->returnValue('base/url/string'));
        
        $expected = array(
            'id'   => 1,
            'name' => 'name',
            'url'  => 'base/url/string/url/request/path'
        );
        
        $this->assertEquals($expected, $this->categorySerializer->serialize($this->categoryCollection));
    }
}