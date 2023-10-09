<?php

namespace Metrilo\Analytics\Test\Unit\Helper;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Paypal\Model\Cart;
use Metrilo\Analytics\Helper\Data;
use Metrilo\Analytics\Helper\SessionEvents;
use Metrilo\Analytics\Model\Events\ProductView;
use Metrilo\Analytics\Model\Events\PageView;
use Metrilo\Analytics\Model\Events\CategoryView;
use Metrilo\Analytics\Model\Events\CatalogSearch;
use Metrilo\Analytics\Model\Events\CartView;
use Metrilo\Analytics\Model\Events\CheckoutView;
use Metrilo\Analytics\Block\Analytics;

class AnalyticsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $actionContext;
    
    /**
     * @var \Metrilo\Analytics\Helper\Data
     */
    private $dataHelper;
    
    /**
     * @var \Metrilo\Analytics\Helper\SessionEvents
     */
    private $sessionEventsHelper;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\ProductView
     */
    private $productViewEvent;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\PageView
     */
    private $pageViewEvent;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\CategoryView
     */
    private $categoryViewEvent;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\CatalogSearch
     */
    private $catalogSearchEvent;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\CartView
     */
    private $cartViewEvent;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\ProductView
     */
    private $checkoutViewEvent;
    
    /**
     * @var \Metrilo\Analytics\Block\Analytics
     */
    private $analyticsBlock;
    
    public function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->actionContext = $this->getMockBuilder(ActionContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest', 'getFullActionName'])
            ->getMock();
        
        $this->actionContext->expects($this->any())->method('getRequest')->will($this->returnSelf());
        
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
            $this->context,
            $this->actionContext,
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
        $apiToken    = '9b4dd74a736d9d7d';
        $storeId     = 1;
        
        $this->dataHelper->expects($this->any())->method('getApiEndpoint')
            ->will($this->returnValue($apiEndpoint));
        $this->dataHelper->expects($this->any())->method('getApiToken')
            ->with($this->equalTo($storeId))->will($this->returnValue($apiToken));
        $this->dataHelper->expects($this->any())->method('getStoreId')
            ->will($this->returnValue($storeId));
    
        $expected = $apiEndpoint . '/tracking.js?token=' . $apiToken;
        $result   = $this->analyticsBlock->getLibraryUrl();
        
        $this->assertSame($expected, $result);
    }
    
    public function testGetEventProductView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('catalog_product_view'));
        
        $this->productViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());
        
        $this->assertInstanceOf(ProductView::class, $this->analyticsBlock->getEvent());
    }
    
    public function testGetEventCategoryView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('catalog_category_view'));
    
        $this->categoryViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());

        $this->assertInstanceOf(CategoryView::class, $this->analyticsBlock->getEvent());
    }

    public function testGetEventCatalogSearchView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('catalogsearch_result_index'));

        $this->catalogSearchEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());

        $this->assertInstanceOf(CatalogSearch::class, $this->analyticsBlock->getEvent());
    }

    public function testGetEventCatalogAdvancedSearchView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('catalogsearch_advanced_result'));

        $this->catalogSearchEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());

        $this->assertInstanceOf(CatalogSearch::class, $this->analyticsBlock->getEvent());
    }

    public function testGetEventCartView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('checkout_cart_index'));

        $this->cartViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());

        $this->assertInstanceOf(CartView::class, $this->analyticsBlock->getEvent());
    }

    public function testGetEventCheckoutView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue('checkout_index_index'));

        $this->checkoutViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());

        $this->assertInstanceOf(CheckoutView::class, $this->analyticsBlock->getEvent());
    }

    public function testGetEventPageView()
    {
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue(''));

        $this->pageViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnSelf());

        $this->assertInstanceOf(PageView::class, $this->analyticsBlock->getEvent());
    }
    
    public function testGetEventsWithSessionEvent()
    {
        $pageViewEvent = "window.metrilo.viewProduct(1);";
        
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue(''));
        
        $this->pageViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnValue($pageViewEvent));
    
        $this->sessionEventsHelper->expects($this->any())->method('getSessionEvents')
            ->will($this->returnValue(['sessionEvent']));
    
        $expected   = ['sessionEvent'];
        $expected[] = $pageViewEvent;

        $this->assertSame($expected, $this->analyticsBlock->getEvents());
    }
    
    public function testGetEventsWithoutSessionEvent()
    {
        $pageViewEvent = "window.metrilo.viewProduct(1);";
        
        $this->actionContext->expects($this->any())->method('getFullActionName')
            ->will($this->returnValue(''));
        
        $this->pageViewEvent->expects($this->any())->method('callJs')
            ->will($this->returnValue($pageViewEvent));
        
        $this->sessionEventsHelper->expects($this->any())->method('getSessionEvents')
            ->will($this->returnValue(null));
        
        $expected   = null;
        $expected[] = $pageViewEvent;
        
        $this->assertSame($expected, $this->analyticsBlock->getEvents());
    }
}
