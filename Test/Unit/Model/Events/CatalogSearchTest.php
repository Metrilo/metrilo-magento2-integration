<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Metrilo\Analytics\Model\Events\CatalogSearch;

class CatalogSearchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Metrilo\Analytics\Model\Events\CatalogSearch
     */
    private $catalogSearchEvent;
    
    private $pageUrl     = 'http://website.domain/product.html';
    private $searchQuery = 'search_query';

    public function setUp(): void
    {
        $this->urlInterface = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUrl'])
            ->getMockForAbstractClass();
    
        $this->urlInterface->expects($this->any())->method('getCurrentUrl')
            ->will($this->returnValue($this->pageUrl));

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->catalogSearchEvent = new CatalogSearch($this->urlInterface, $this->request);
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
        $this->request->expects($this->at(0))->method('getParam')
            ->with($this->equalTo('q'))
            ->will($this->returnValue(''));
        
        $this->request->expects($this->at(1))->method('getParam')
            ->with($this->equalTo('name'))
            ->will($this->returnValue($this->searchQuery));
    
        $expected = "window.metrilo.search('" . $this->searchQuery . "', '" . $this->pageUrl . "');";
    
        $result = $this->catalogSearchEvent->callJS();
    
        $this->assertSame($expected, $result);
    }
}
