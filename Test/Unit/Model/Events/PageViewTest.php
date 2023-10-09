<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\View\Page\Title;
use Magento\Framework\UrlInterface;
use Metrilo\Analytics\Model\Events\PageView;

class PageViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Page\Title
     */
    private $pageTitleView;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;
    
    /**
     * @var \Metrilo\Analytics\Model\Events\PageView
     */
    private $pageViewEvent;
    
    private $pageTitle = 'pageTitle';
    private $pageUrl   = 'http://website.domain/product.html';
    
    public function setUp(): void
    {
        $this->pageTitleView = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShort'])
            ->getMock();
        
        $this->urlInterface = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUrl'])
            ->getMockForAbstractClass();
        
        $this->pageViewEvent = new PageView($this->pageTitleView, $this->urlInterface);
    }
    
    public function testCallJs()
    {
        $this->pageTitleView->expects($this->any())->method('getShort')->will($this->returnValue($this->pageTitle));
    
        $this->urlInterface->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($this->pageUrl));
        
        $expected = "window.metrilo.viewPage('" . $this->pageUrl . "', " .
                    json_encode(array('name' => $this->pageTitle)) . ");";
        
        $result = $this->pageViewEvent->callJS();
        
        $this->assertSame($expected, $result);
    }
}
