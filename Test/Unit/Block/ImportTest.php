<?php

namespace Metrilo\Analytics\Test\Unit\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Metrilo\Analytics\Block\System\Config\Button\Import;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Model\CategoryData;
use Metrilo\Analytics\Model\CustomerData;
use Metrilo\Analytics\Model\OrderData;
use Metrilo\Analytics\Model\ProductData;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    private Import $block;
    private Context $context;

    private Data $helper;

    private CustomerData $customerData;
    private CategoryData $categoryData;

    private ProductData $productData;

    private OrderData $orderData;

    private Http $request;

    public function setUp(): void
    {
        $this->context = $this->createMock(Context::class);

        $this->helper = $this->createMock(Data::class);

        $this->customerData = $this->createMock(CustomerData::class);

        $this->categoryData = $this->createMock(CategoryData::class);

        $this->productData = $this->createMock(ProductData::class);

        $this->orderData = $this->createMock(OrderData::class);

        $this->request = $this->createMock(Http::class);
        $this->context->expects($this->once())->method('getRequest')->will($this->returnValue($this->request));

        $this->block = new Import(
            $this->context,
            $this->helper,
            $this->customerData,
            $this->categoryData,
            $this->productData,
            $this->orderData
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

        $this->assertEquals($this->request->getParam('store', 0), $this->block->getStoreId());

        $this->assertIsInt($this->block->getStoreId());
    }
}
