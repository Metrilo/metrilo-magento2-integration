<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Metrilo\Analytics\Helper\CategorySerializer;
use PHPUnit\Framework\TestCase;

class CategorySerializerTest extends TestCase
{
    private Collection $categoryCollection;

    private StoreManagerInterface $storeManager;

    private CategorySerializer $categorySerializer;

    public function setUp(): void
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
                                       'setCurrentStore'
                                   ])
                                   ->getMock();

        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->categorySerializer = new CategorySerializer($this->storeManager, $context);
    }

    public function testSerialize()
    {
        $this->categoryCollection->expects($this->any())->method('getId')
                                 ->will($this->returnValue(1));
        $this->categoryCollection->expects($this->any())->method('getStoreId')
                                 ->will($this->returnValue(1));
        $this->categoryCollection->expects($this->any())->method('getName')
                                 ->will($this->returnValue('name'));
        $this->categoryCollection->expects($this->any())->method('getRequestPath')
                                 ->will($this->returnValue('/url/request/path'));

        $this->storeManager->expects($this->any())->method('getStore')
                           ->with($this->isType('int'))
                           ->will($this->returnSelf());
        $this->storeManager->expects($this->any())->method('getBaseUrl')
                           ->will($this->returnValue('base/url/string'));

        $expected = [
            'id' => 1,
            'name' => 'name',
            'url' => 'base/url/string/url/request/path'
        ];

        $this->assertEquals($expected, $this->categorySerializer->serialize($this->categoryCollection));
    }
}
