<?php

/**
 * This file is part of contao-community-alliance/contao-polyfill-bundle.
 *
 * (c) 2019-2020 Contao Community Alliance.
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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\Asset;

use Contao\System;
use Contao\PageModel;
use Contao\CoreBundle\Config\ResourceFinder;
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
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Some class mapping for Contao 4.4.
        self::aliasContaoClass('System');
        self::aliasContaoClass('Model');
        self::aliasContaoClass('PageModel');
        self::aliasContaoClass('Date');
    }

    public function testReturnsAnEmptyBasePathInDebugMode(): void
    {
        $context = new ContaoContext(new RequestStack(), 'staticPlugins', true);

        self::assertSame('', $context->getBasePath());
    }

    public function testReturnsAnEmptyBasePathIfThereIsNoRequest(): void
    {
        $context = $this->getContaoContext('staticPlugins');

        self::assertSame('', $context->getBasePath());
    }

    public function testReturnsAnEmptyBasePathIfThePageDoesNotDefineIt(): void
    {
        $page = $this->getPageWithDetails();

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('staticPlugins');

        self::assertSame('', $context->getBasePath());

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
        $request = $this->createMock(Request::class);
        $request
            ->expects(self::once())
            ->method('getBasePath')
            ->willReturn($basePath);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $page                = $this->getPageWithDetails();
        $page->rootUseSSL    = $useSSL;
        $page->staticPlugins = $domain;

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('staticPlugins', $requestStack);

        self::assertSame($expected, $context->getBasePath());

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
        $request = $this->createMock(Request::class);
        $request
            ->expects(self::once())
            ->method('getBasePath')
            ->willReturn('/foo');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $page                = $this->getPageWithDetails();
        $page->rootUseSSL    = true;
        $page->staticPlugins = 'example.com';

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('staticPlugins', $requestStack);

        self::assertSame('https://example.com/foo/', $context->getStaticUrl());
    }

    public function testReturnsAnEmptyStaticUrlIfTheBasePathIsEmpty(): void
    {
        $context = new ContaoContext(new RequestStack(), 'staticPlugins');

        self::assertSame('', $context->getStaticUrl());
    }

    public function testReadsTheSslConfigurationFromThePage(): void
    {
        $page = $this->getPageWithDetails();

        $GLOBALS['objPage'] = $page;

        $context = $this->getContaoContext('');

        $page->rootUseSSL = true;
        self::assertTrue($context->isSecure());

        $page->rootUseSSL = false;
        self::assertFalse($context->isSecure());

        unset($GLOBALS['objPage']);
    }

    public function testReadsTheSslConfigurationFromTheRequest(): void
    {
        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $context = $this->getContaoContext('', $requestStack);

        self::assertFalse($context->isSecure());

        $request->server->set('HTTPS', 'on');
        self::assertTrue($context->isSecure());

        $request->server->set('HTTPS', 'off');
        self::assertFalse($context->isSecure());
    }

    public function testDoesNotReadTheSslConfigurationIfThereIsNoRequest(): void
    {
        $context = $this->getContaoContext('');

        self::assertFalse($context->isSecure());
    }

    private function getPageWithDetails(): PageModel
    {
        $finder = new ResourceFinder(\dirname(__DIR__) . '/Fixtures/vendor/contao/test-bundle/Resources/contao');

        $container = $this->getContainerWithContaoConfiguration();
        $container->set('contao.resource_finder', $finder);

        System::setContainer($container);

        $page                = $this
            ->getMockBuilder(PageModel::class)
            ->disableOriginalConstructor()
            ->setMethods([
                // Must deactivate 'detach' - we are not attached anyway.
                'detach',
                '__set',
                '__get',
            ])
            ->getMock();

        // Must mock __get() and __set() as they trigger notices without end - buffering in local array helps.
        $data = [];
        $page->method('__get')->willReturnCallback(function ($key) use (&$data) {
            return $data[$key] ?? null;
        });
        $page->method('__set')->willReturnCallback(function ($key, $value) use (&$data) {
            $data[$key] = $value;
        });

        /** @var PageModel $page */
        $page->type          = 'root';
        $page->fallback      = true;
        $page->staticPlugins = '';

        // Usually derived from \Contao\Config in PageModel::loadDetails() - must circumvent.
        $page->dateFormat    = 'Y-m-d';
        $page->timeFormat    = 'h:i:s';
        $page->datimFormat   = 'Y-m-d h:i:s';

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

    /**
     * Mapping between root namespace of contao and the contao namespace.
     * Can map class, interface and trait.
     *
     * @param string $class The name of the class
     *
     * @return void
     */
    private static function aliasContaoClass(string $class): void
    {
        // Class.
        if (!\class_exists($class, true) && \class_exists('\\Contao\\' . $class, true)) {
            if (!\class_exists($class, false)) {
                \class_alias('\\Contao\\' . $class, $class);
            }
            return;
        }
        // Trait.
        if (!\trait_exists($class, true) && \trait_exists('\\Contao\\' . $class, true)) {
            if (!\trait_exists($class, false)) {
                \class_alias('\\Contao\\' . $class, $class);
            }
            return;
        }
        // Interface.
        if (!\interface_exists($class, true) && \interface_exists('\\Contao\\' . $class, true)) {
            if (!\interface_exists($class, false)) {
                \class_alias('\\Contao\\' . $class, $class);
            }
            return;
        }
    }
}
