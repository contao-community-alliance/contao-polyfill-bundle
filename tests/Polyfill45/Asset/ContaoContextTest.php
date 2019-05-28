<?php

/**
 * This file is part of contao-community-alliance/contao-polyfill-bundle.
 *
 * (c) 2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/contao-polyfill-bundle
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Leo Feyer <github@contao.org>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\Asset;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Config\ResourceFinder;
use Contao\DcaExtractor;
use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use ContaoCommunityAlliance\Polyfills\Polyfill45\Asset\ContaoContext;
use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\CcaContaoPolyfill45Extension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ContaoContextTest
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\Asset\ContaoContext
 */
class ContaoContextTest extends TestCase
{
    public function testReturnsAnEmptyBasePathInDebugMode(): void
    {
        $context = new ContaoContext(new RequestStack(), 'staticPlugins', true);

        $this->assertSame('', $context->getBasePath());
    }

    public function testReturnsAnEmptyBasePathIfThereIsNoRequest(): void
    {
        $context = $this->getContaoContext('staticPlugins');

        $this->assertSame('', $context->getBasePath());
    }

    public function testReturnsAnEmptyBasePathIfThePageDoesNotDefineIt(): void
    {
        $this->markTestIncomplete(__FUNCTION__);
        $page = $this->getPageWithDetails();

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('staticPlugins');

        $this->assertSame('', $context->getBasePath());

        unset($GLOBALS['objPage']);
    }

    /**
     * @dataProvider getBasePaths
     */
    public function testReadsTheBasePathFromThePageModel(
        string $domain,
        bool $useSSL,
        string $basePath,
        string $expected
    ): void {
        $this->markTestIncomplete(__FUNCTION__);
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('getBasePath')
            ->willReturn($basePath);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $page                = $this->getPageWithDetails();
        $page->rootUseSSL    = $useSSL;
        $page->staticPlugins = $domain;

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('staticPlugins', $requestStack);

        $this->assertSame($expected, $context->getBasePath());

        unset($GLOBALS['objPage']);
    }

    public function getBasePaths(): \Generator
    {
        yield ['example.com', true, '', 'https://example.com'];
        yield ['example.com', false, '', 'http://example.com'];
        yield ['example.com', true, '/foo', 'https://example.com/foo'];
        yield ['example.com', false, '/foo', 'http://example.com/foo'];
        yield ['example.ch', false, '/bar', 'http://example.ch/bar'];
    }

    public function testReturnsTheStaticUrl(): void
    {
        $this->markTestIncomplete(__FUNCTION__);
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('getBasePath')
            ->willReturn('/foo');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $page                = $this->getPageWithDetails();
        $page->rootUseSSL    = true;
        $page->staticPlugins = 'example.com';

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('staticPlugins', $requestStack);

        $this->assertSame('https://example.com/foo/', $context->getStaticUrl());
    }

    public function testReturnsAnEmptyStaticUrlIfTheBasePathIsEmpty(): void
    {
        $context = new ContaoContext(new RequestStack(), 'staticPlugins');

        $this->assertSame('', $context->getStaticUrl());
    }

    public function testReadsTheSslConfigurationFromThePage(): void
    {
        $this->markTestIncomplete(__FUNCTION__);
        $page = $this->getPageWithDetails();

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('');

        $page->rootUseSSL = true;
        $this->assertTrue($context->isSecure());

        $page->rootUseSSL = false;
        $this->assertFalse($context->isSecure());

        unset($GLOBALS['objPage']);
    }

    public function testReadsTheSslConfigurationFromTheRequest(): void
    {
        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $context = $this->getContaoContext('', $requestStack);

        $this->assertFalse($context->isSecure());

        $request->server->set('HTTPS', 'on');
        $this->assertTrue($context->isSecure());

        $request->server->set('HTTPS', 'off');
        $this->assertFalse($context->isSecure());
    }

    public function testDoesNotReadTheSslConfigurationIfThereIsNoRequest(): void
    {
        $context = $this->getContaoContext('');

        $this->assertFalse($context->isSecure());
    }

    private function getPageWithDetails()
    {
        $finder = new ResourceFinder(\dirname(__DIR__) . '/Fixtures/vendor/contao/test-bundle/Resources/contao');

        $container = $this->getContainerWithContaoConfiguration();
        $container->set('contao.resource_finder', $finder);

        System::setContainer($container);

        $page                = new PageModel();
        $page->type          = 'root';
        $page->fallback      = true;
        $page->staticPlugins = '';

        return $page->loadDetails();
    }

    /**
     * Returns a Symfony container with the Contao core extension configuration.
     */
    private function getContainerWithContaoConfiguration(string $projectDir = ''): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.default_locale', 'en');
        $container->setParameter('kernel.cache_dir', $projectDir . '/var/cache');
        $container->setParameter('kernel.project_dir', $projectDir);
        $container->setParameter('kernel.root_dir', $projectDir . '/app');

        // Load the default configuration
        $extension = new CcaContaoPolyfill45Extension();
        $extension->load(['asset' => true], $container);

        return $container;
    }

    private function getContaoContext(string $field, RequestStack $requestStack = null): ContaoContext
    {
        if (null === $requestStack) {
            $requestStack = new RequestStack();
        }

        return new ContaoContext($requestStack, $field);
    }
}
