<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template\Context;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\ProductView;
use Metrilo\Analytics\Model\Events\PageView;
use Metrilo\Analytics\Model\Events\CategoryView;
use Metrilo\Analytics\Model\Events\CatalogSearch;
use Metrilo\Analytics\Model\Events\CartView;
use Metrilo\Analytics\Model\Events\CheckoutView;
use Metrilo\Analytics\Block\Analytics;
use PHPUnit\Framework\TestCase;

class AnalyticsTest extends TestCase
{
    private RequestInterface $request;

    private Data $dataHelper;

    private SessionEvents $sessionEventsHelper;

    private ProductView $productViewEvent;

    private PageView $pageViewEvent;

    private CategoryView $categoryViewEvent;

    private CatalogSearch $catalogSearchEvent;

    private CartView $cartViewEvent;

    private CheckoutView $checkoutViewEvent;

    private Analytics $analyticsBlock;

    public function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->request = $this->getMockBuilder(Http::class)
                              ->disableOriginalConstructor()->getMock();

        $context->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));

        $this->dataHelper = $this->getMockBuilder(Data::class)
                                 ->disableOriginalConstructor()
                                 ->setMethods(['getApiEndpoint', 'getApiToken', 'getStoreId', 'isEnabled'])
                                 ->getMock();

        $this->sessionEventsHelper = $this->getMockBuilder(SessionEvents::class)
                                          ->disableOriginalConstructor()
                                          ->setMethods(['getSessionEvents'])
                                          ->getMock();

        $this->productViewEvent = $this->getMockBuilder(ProductView::class)
                                       ->disableOriginalConstructor()
                                       ->setMethods(['callJs'])
                                       ->getMock();

        $this->pageViewEvent = $this->getMockBuilder(PageView::class)
                                    ->disableOriginalConstructor()
                                    ->setMethods(['callJs'])
                                    ->getMock();

        $this->categoryViewEvent = $this->getMockBuilder(CategoryView::class)
                                        ->disableOriginalConstructor()
                                        ->setMethods(['callJs'])
                                        ->getMock();

        $this->catalogSearchEvent = $this->getMockBuilder(CatalogSearch::class)
                                         ->disableOriginalConstructor()
                                         ->setMethods(['callJs'])
                                         ->getMock();

        $this->cartViewEvent = $this->getMockBuilder(CartView::class)
                                    ->setMethods(['callJs'])
                                    ->getMock();

        $this->checkoutViewEvent = $this->getMockBuilder(CheckoutView::class)
                                        ->setMethods(['callJs'])
                                        ->getMock();

        $this->analyticsBlock = new Analytics(
            $context,
            $this->dataHelper,
            $this->sessionEventsHelper,
            $this->productViewEvent,
            $this->pageViewEvent,
            $this->categoryViewEvent,
            $this->catalogSearchEvent,
            $this->cartViewEvent,
            $this->checkoutViewEvent
        );
    }

    public function testGetLibraryUrl()
    {
        $apiEndpoint = 'https://trk.mtrl.me';
        $apiToken = '9b4dd74a736d9d7d';
        $storeId = 1;

        $this->dataHelper->expects($this->any())->method('getApiEndpoint')
                         ->will($this->returnValue($apiEndpoint));
        $this->dataHelper->expects($this->any())->method('getApiToken')
                         ->with($this->equalTo($storeId))->will($this->returnValue($apiToken));
        $this->dataHelper->expects($this->any())->method('getStoreId')
                         ->will($this->returnValue($storeId));

        $expected = $apiEndpoint . '/tracking.js?token=' . $apiToken;
        $result = $this->analyticsBlock->getLibraryUrl();

        $this->assertSame($expected, $result);
    }

    public function testGetEventProductView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue('catalog_product_view'));

        $this->productViewEvent->expects($this->any())->method('callJs')
                               ->will($this->returnValue('zzzzProductView'));

        $this->assertEquals('zzzzProductView', $this->analyticsBlock->getEvent());
    }

    public function testGetEventCategoryView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue('catalog_category_view'));
        $this->categoryViewEvent->expects($this->any())->method('callJs')
                                ->will($this->returnValue('zzzzCategoryView'));

        $this->assertEquals('zzzzCategoryView', $this->analyticsBlock->getEvent());
    }

    public function testGetEventCatalogSearchView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue('catalogsearch_result_index'));
        $this->catalogSearchEvent->expects($this->any())->method('callJs')
                                 ->will($this->returnValue('zzzzCatalogSearch'));

        $this->assertEquals('zzzzCatalogSearch', $this->analyticsBlock->getEvent());
    }

    public function testGetEventCatalogAdvancedSearchView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue('catalogsearch_advanced_result'));
        $this->catalogSearchEvent->expects($this->any())->method('callJs')
                                 ->will($this->returnValue('zzzzAdvancedSearch'));

        $this->assertEquals('zzzzAdvancedSearch', $this->analyticsBlock->getEvent());
    }

    public function testGetEventCartView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue('checkout_cart_index'));
        $this->cartViewEvent->expects($this->any())->method('callJs')
                            ->will($this->returnValue('zzzzCart'));

        $this->assertEquals('zzzzCart', $this->analyticsBlock->getEvent());
    }

    public function testGetEventCheckoutView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue('checkout_index_index'));
        $this->checkoutViewEvent->expects($this->any())->method('callJs')
                                ->will($this->returnValue('zzzz'));

        $this->assertEquals('zzzz', $this->analyticsBlock->getEvent());
    }

    public function testGetEventPageView()
    {
        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue(''));
        $this->pageViewEvent->expects($this->any())->method('callJs')
                            ->will($this->returnValue('zzzz'));

        $this->assertEquals('zzzz', $this->analyticsBlock->getEvent());
    }

    public function testGetEventsWithSessionEvent()
    {
        $pageViewEvent = "window.metrilo.viewProduct(1);";

        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue(''));
        $this->pageViewEvent->expects($this->any())->method('callJs')
                            ->will($this->returnValue($pageViewEvent));

        $this->sessionEventsHelper->expects($this->any())->method('getSessionEvents')
                                  ->will($this->returnValue(['sessionEvent']));

        $expected = ['sessionEvent'];
        $expected[] = $pageViewEvent;

        $this->assertSame($expected, $this->analyticsBlock->getEvents());
    }

    public function testGetEventsWithoutSessionEvent()
    {
        $pageViewEvent = "window.metrilo.viewProduct(1);";

        $this->request->expects($this->once())
                      ->method('getFullActionName')
                      ->will($this->returnValue(''));

        $this->pageViewEvent->expects($this->any())->method('callJs')
                            ->will($this->returnValue($pageViewEvent));

        $this->sessionEventsHelper->expects($this->any())->method('getSessionEvents')
                                  ->will($this->returnValue(null));

        $expected = null;
        $expected[] = $pageViewEvent;

        $this->assertSame($expected, $this->analyticsBlock->getEvents());
    }
}
