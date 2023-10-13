<?php

namespace Metrilo\Analytics\Test\Unit\Model\Events;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Page\Title;
use Magento\Framework\UrlInterface;
use Metrilo\Analytics\Model\Events\PageView;
use PHPUnit\Framework\TestCase;

class PageViewTest extends TestCase
{
    private Title $pageTitleView;
    private UrlInterface $urlInterface;
    private PageView $pageViewEvent;
    private Json $json;
    private string $pageTitle = 'pageTitle';
    private string $pageUrl = 'http://website.domain/product.html';

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
        $this->json = new Json();

        $this->pageViewEvent = new PageView($this->pageTitleView, $this->urlInterface, $this->json);
    }

    public function testCallJs()
    {
        $this->pageTitleView->expects($this->any())->method('getShort')->will($this->returnValue($this->pageTitle));

        $this->urlInterface->expects($this->any())->method('getCurrentUrl')->will($this->returnValue($this->pageUrl));

        $expected = "window.metrilo.viewPage('" . $this->pageUrl . "', " .
                    $this->json->serialize(['name' => $this->pageTitle]) . ");";

        $result = $this->pageViewEvent->callJS();

        $this->assertSame($expected, $result);
    }
}
