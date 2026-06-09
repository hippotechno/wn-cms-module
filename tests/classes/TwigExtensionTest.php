<?php

namespace Cms\Tests\Classes;

use Cms\Twig\Extension;
use Cms\Classes\Controller;
use Illuminate\Support\HtmlString;
use System\Tests\Bootstrap\TestCase;
use Winter\Storm\Exception\SystemException;

class TestTwigExtension extends Extension
{
    public bool $multiSiteHelperAvailable = false;
    public string $rewritePrefix = 'rewritten:';

    public function publicRewriteRenderedAssetUrls(string $html): string
    {
        return $this->rewriteRenderedAssetUrls($html);
    }

    protected function hasMultiSiteUrlRewriter(): bool
    {
        return $this->multiSiteHelperAvailable;
    }

    protected function replaceHostInUrl(string $url): string
    {
        return $this->rewritePrefix . $url;
    }
}

class TwigExtensionTest extends TestCase
{
    public function testPartialFunction()
    {
        $extension = new Extension;
        $controller = Controller::getController() ?: new Controller;
        $extension->setController($controller);

        $this->assertFalse($extension->partialFunction('invalid-partial-file', [], false));

        $this->expectException(SystemException::class);
        $this->expectExceptionMessageMatches('/is\snot\sfound/');
        $this->assertFalse($extension->partialFunction('invalid-partial-file', [], true));
    }

    public function testContentFunction()
    {
        $extension = new Extension;
        $controller = Controller::getController() ?: new Controller;
        $extension->setController($controller);

        $this->assertFalse($extension->contentFunction('invalid-content-file', [], false));

        $this->expectException(SystemException::class);
        $this->expectExceptionMessageMatches('/is\snot\sfound/');
        $this->assertFalse($extension->contentFunction('invalid-content-file', [], true));
    }

    public function testMultiSitePageFilterFallsBackToCoreBehaviorWhenHelperIsUnavailable()
    {
        $extension = new TestTwigExtension;
        $controller = $this->createMock(Controller::class);
        $controller->expects($this->once())
            ->method('pageUrl')
            ->with('home', ['foo' => 'bar'], false)
            ->willReturn('/home?foo=bar');

        $extension->setController($controller);

        $this->assertSame(
            '/home?foo=bar',
            $extension->multiSitePageFilter('home', ['foo' => 'bar'], false)
        );
    }

    public function testMultiSiteThemeFilterRewritesUrlWhenHelperIsAvailable()
    {
        $extension = new TestTwigExtension;
        $extension->multiSiteHelperAvailable = true;

        $controller = $this->createMock(Controller::class);
        $controller->expects($this->once())
            ->method('themeUrl')
            ->with('assets/app.css')
            ->willReturn('https://example.test/themes/demo/assets/app.css');

        $extension->setController($controller);

        $this->assertSame(
            'rewritten:https://example.test/themes/demo/assets/app.css',
            $extension->multiSiteThemeFilter('assets/app.css')
        );
    }

    public function testRewriteRenderedAssetUrlsFallsBackWhenHelperIsUnavailable()
    {
        $extension = new TestTwigExtension;

        $html = '<script src="https://example.test/app.js"></script>';

        $this->assertSame($html, $extension->publicRewriteRenderedAssetUrls($html));
    }

    public function testRewriteRenderedAssetUrlsRewritesSrcAndHrefWhenHelperIsAvailable()
    {
        $extension = new TestTwigExtension;
        $extension->multiSiteHelperAvailable = true;

        $html = '<link href="https://example.test/app.css"><script src="https://example.test/app.js"></script>';

        $this->assertSame(
            '<link href="rewritten:https://example.test/app.css"><script src="rewritten:https://example.test/app.js"></script>',
            $extension->publicRewriteRenderedAssetUrls($html)
        );
    }
}
