<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Metrilo\Analytics\Model\Events\CatalogSearch;
use PHPUnit\Framework\TestCase;

class CatalogSearchTest extends TestCase
{
    private Http $request;

    private CatalogSearch $catalogSearchEvent;

    private string $pageUrl = 'http://website.domain/product.html';

    private string $searchQuery = 'search_query';

    public function setUp(): void
    {
        $urlInterface = $this->getMockBuilder(UrlInterface::class)
                             ->disableOriginalConstructor()
                             ->setMethods(['getCurrentUrl'])
                             ->getMockForAbstractClass();

        $urlInterface->expects($this->any())->method('getCurrentUrl')
                           ->will($this->returnValue($this->pageUrl));

        $this->request = $this->getMockBuilder(Http::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->catalogSearchEvent = new CatalogSearch($urlInterface, $this->request);
    }

    public function testCallJsForAdvancedSearch()
    {
        $this->request->expects($this->any())->method('getParam')
                      ->with($this->equalTo('q'))
                      ->will($this->returnValue($this->searchQuery));

        $expected = "window.metrilo.search('" . $this->searchQuery . "', '" . $this->pageUrl . "');";

        $result = $this->catalogSearchEvent->callJS();

        $this->assertSame($expected, $result);
    }

    public function testCallJsForStandardSearch()
    {
        $this->request->expects($this->any())->method('getParam')
                      ->willReturnOnConsecutiveCalls('', $this->searchQuery);

        $expected = "window.metrilo.search('" . $this->searchQuery . "', '" . $this->pageUrl . "');";

        $result = $this->catalogSearchEvent->callJS();

        $this->assertSame($expected, $result);
    }
}
