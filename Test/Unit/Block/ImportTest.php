<?php

namespace Metrilo\Analytics\Test\Unit\Block;

use Metrilo\Analytics\Block\System\Config\Button\Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Metrilo\Analytics\Block\Sysetm\Config\Button\Import
     */
    private $block;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ProductFactory
     */

    private $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Rbj\ProductUnitTest\Model\ProductCollection
     */
    private $productCollection;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Backend\Block\Template\Context::class);

        $this->helper = $this->createMock(\Metrilo\Analytics\Helper\Data::class);

        $this->customerData = $this->createMock(\Metrilo\Analytics\Model\CustomerData::class);

        $this->categoryData = $this->createMock(\Metrilo\Analytics\Model\CategoryData::class);

        $this->productData = $this->createMock(\Metrilo\Analytics\Model\ProductData::class);

        $this->orderData = $this->createMock(\Metrilo\Analytics\Model\OrderData::class);

        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->block = new Import(
            $this->context,
            $this->helper,
            $this->customerData,
            $this->categoryData,
            $this->productData,
            $this->orderData,
            $this->request
        );
    }

    public function testButtonEnabled()
    {
        $storeId = $this->block->getStoreId();
        return $this->helper->isEnabled($storeId)
            && $this->helper->getApiToken($storeId)
            && $this->helper->getApiSecret($storeId);
    }

    public function testGetAjaxUrl()
    {
        $block = $this->createMock(Import::class);
        $block->expects($this->any())->method('getAjaxUrl')->will($this->returnValue('string_url'));
        $getUrl   = $block->getAjaxUrl();
        $expected = 'string_url';
        $this->assertEquals($expected, $getUrl);
        $this->assertIsString($getUrl);
    }

    public function testGetOrderChunks()
    {
        $this->orderData->expects($this->any())->method('getOrderChunks')->will($this->returnValue(7));
        $orderCh      = $this->orderData->getOrderChunks($this->block->getStoreId());
        $blockOrderCh = $this->block->getOrderChunks();

        $this->assertIsInt($orderCh);
        $this->assertIsInt($blockOrderCh);

        $this->assertEquals(7, $orderCh);
        $this->assertEquals(7, $blockOrderCh);
    }

    public function testGetCustomerChunks()
    {
        $this->customerData->expects($this->any())->method('getCustomerChunks')->will($this->returnValue(8));
        $custCh      = $this->customerData->getCustomerChunks($this->block->getStoreId());
        $blockCustCh = $this->block->getCustomerChunks();

        $this->assertIsInt($custCh);
        $this->assertIsInt($blockCustCh);

        $this->assertEquals(8, $custCh);
        $this->assertEquals(8, $blockCustCh);
    }

    public function testGetCategoryChunks()
    {
        $this->categoryData->expects($this->any())->method('getCategoryChunks')->will($this->returnValue(9));
        $catCh      = $this->categoryData->getCategoryChunks($this->block->getStoreId());
        $blockCatCh = $this->block->getCategoryChunks();

        $this->assertIsInt($catCh);
        $this->assertIsInt($blockCatCh);

        $this->assertEquals(9, $catCh);
        $this->assertEquals(9, $blockCatCh);
    }

    public function testGetProductChunks()
    {
        $this->productData->expects($this->any())->method('getProductChunks')->will($this->returnValue(10));
        $prodCh      = $this->productData->getProductChunks($this->block->getStoreId());
        $blockProdCh = $this->block->getProductChunks();

        $this->assertIsInt($prodCh);
        $this->assertIsInt($blockProdCh);

        $this->assertEquals(10, $prodCh);
        $this->assertEquals(10, $blockProdCh);
    }

    public function testGetStoreId()
    {
        $this->assertEquals(
            $this->request->getParam('store', 0),
            $this->block->getStoreId()
        );

        $this->request->setParam('store', 1001);
        $this->block->request->setParam('store', 1001);

        $this->assertEquals($this->request->getParam('store', 0), $this->block->getStoreId());

        $this->assertIsInt($this->block->getStoreId());
    }
}
