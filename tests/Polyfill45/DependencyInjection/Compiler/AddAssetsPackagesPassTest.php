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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\DependencyInjection\Compiler;

use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\CcaContaoPolyfill45Extension;
use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\AddAssetsPackagesPass;
use ContaoCommunityAlliance\Polyfills\Polyfill45\Util\PackageUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\AddAssetsPackagesPass
 */
class AddAssetsPackagesPassTest extends TestCase
{
    /**
     * @var array
     */
    private static $tempDirs = [];

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $fs = new Filesystem();
        $fs->mkdir(static::getTempDir().'/FooBarBundle/Resources/public');
        $fs->mkdir(static::getTempDir().'/FooBarPackage/Resources/public');
    }

    public function testAbortsIfTheAssetsPackagesServiceDoesNotExist(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('cca.polyfill_45_event_listener.insert_tags_asset')
            ->willReturn(false)
        ;

        $container
            ->expects($this->never())
            ->method('getDefinition')
        ;

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);
    }

    public function testIgnoresBundlesWithoutPublicFolder(): void
    {
        $bundlePath = static::getTempDir().'/BarFooBundle';
        $container = $this->getContainerWithAssets('BarFooBundle', 'Bar\Foo\BarFooBundle', $bundlePath);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $calls = $container->getDefinition('assets.packages')->getMethodCalls();
        $found = false;

        foreach ($calls as $call) {
            if ('addPackage' === $call[0] && 'bar_foo' === $call[1][0]) {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found);
    }

    public function testUsesTheBundleNameAsPackageName(): void
    {
        $bundlePath = static::getTempDir().'/FooBarBundle';
        $container = $this->getContainerWithAssets('FooBarBundle', 'Foo\Bar\FooBarBundle', $bundlePath);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $calls = $container->getDefinition('assets.packages')->getMethodCalls();
        $found = false;

        foreach ($calls as $call) {
            if ('addPackage' === $call[0] && 'foo_bar' === $call[1][0]) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
        $this->assertTrue($container->hasDefinition('assets._package_foo_bar'));

        $service = $container->getDefinition('assets._package_foo_bar');
        $this->assertSame('bundles/foobar', $service->getArgument(0));
        $this->assertSame('assets.empty_version_strategy', (string) $service->getArgument(1));
        $this->assertSame('contao.assets.plugins_context', (string) $service->getArgument(2));
    }

    public function testUsesTheDefaultVersionStrategyForBundles(): void
    {
        $bundlePath = static::getTempDir().'/FooBarBundle';

        $container = $this->getContainerWithAssets('FooBarBundle', 'Foo\Bar\FooBarBundle', $bundlePath);
        $container->setDefinition('assets._version_default', new Definition(StaticVersionStrategy::class));

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_foo_bar'));

        $service = $container->getDefinition('assets._package_foo_bar');

        $this->assertSame('assets._version_default', (string) $service->getArgument(1));
    }

    public function testSupportsBundlesWithWrongSuffix(): void
    {
        $bundlePath = static::getTempDir().'/FooBarPackage';
        $container = $this->getContainerWithAssets('FooBarPackage', 'Foo\Bar\FooBarPackage', $bundlePath);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_foo_bar_package'));

        $service = $container->getDefinition('assets._package_foo_bar_package');

        $this->assertSame('bundles/foobarpackage', $service->getArgument(0));
    }

    public function testRegistersTheContaoComponents(): void
    {
        $container = $this->getContainerWithContaoConfiguration();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_contao-components/contao'));
        $this->assertTrue($container->hasDefinition('assets._version_contao-components/contao'));
        $this->assertFalse($container->hasDefinition('assets._package_contao/image'));
        $this->assertFalse($container->hasDefinition('assets._version_contao/image'));

        $service = $container->getDefinition('assets._package_contao-components/contao');

        $this->assertSame('assets._version_contao-components/contao', (string) $service->getArgument(1));

        $expectedVersion = PackageUtil::getVersion('contao-components/contao');
        $actualVersion = $container->getDefinition('assets._version_contao-components/contao')->getArgument(0);

        $this->assertSame($expectedVersion, $actualVersion);
    }

    private function getContainerWithAssets(string $name, string $class, string $path): ContainerBuilder
    {
        $container = $this->getContainerWithContaoConfiguration();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->setParameter('kernel.bundles', [$name => $class]);
        $container->setParameter('kernel.bundles_metadata', [$name => ['path' => $path]]);

        return $container;
    }

    /**
     * Returns a Symfony container with the Contao core extension configuration.
     */
    protected function getContainerWithContaoConfiguration(string $projectDir = ''): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.default_locale', 'en');
        $container->setParameter('kernel.cache_dir', $projectDir.'/var/cache');
        $container->setParameter('kernel.project_dir', $projectDir);
        $container->setParameter('kernel.root_dir', $projectDir.'/app');

        // Load the default configuration
        $extension = new CcaContaoPolyfill45Extension();
        $extension->load(['asset' => true], $container);

        return $container;
    }

    /**
     * Returns the path to the temporary directory and creates it if it does not yet exist.
     */
    protected static function getTempDir(): string
    {
        $key = basename(strtr(static::class, '\\', '/'));

        if (!isset(self::$tempDirs[$key])) {
            self::$tempDirs[$key] = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid($key.'_', true);

            $fs = new Filesystem();

            if (!$fs->exists(self::$tempDirs[$key])) {
                $fs->mkdir(self::$tempDirs[$key]);
            }
        }

        return self::$tempDirs[$key];
    }
}
