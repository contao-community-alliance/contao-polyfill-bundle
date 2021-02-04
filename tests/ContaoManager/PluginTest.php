<?php

/**
 * This file is part of contao-community-alliance/contao-polyfill-bundle.
 *
 * (c) 2019-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/contao-polyfill-bundle
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoCommunityAlliance\Polyfills\ContaoManager\Plugin;
use ContaoCommunityAlliance\Polyfills\Polyfill45\CcaContaoPolyfill45Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill46\CcaContaoPolyfill46Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill47\CcaContaoPolyfill47Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill49\CcaContaoPolyfill49Bundle;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\ContaoManager\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * Provide test data.
     *
     * @return \Traversable
     */
    public function pluginTestProvider(): \Traversable
    {
        foreach ([
            '4.4.0' => [
                CcaContaoPolyfill45Bundle::class,
                CcaContaoPolyfill46Bundle::class,
                CcaContaoPolyfill47Bundle::class,
                CcaContaoPolyfill49Bundle::class
            ],
            '4.5.0' => [
                CcaContaoPolyfill46Bundle::class,
                CcaContaoPolyfill47Bundle::class,
                CcaContaoPolyfill49Bundle::class
            ],
            '4.6.0' => [
                CcaContaoPolyfill47Bundle::class,
                CcaContaoPolyfill49Bundle::class
            ],
            '4.8.0' => [
                CcaContaoPolyfill49Bundle::class
            ],
            'dev-master@012345678' => [
            ],
        ] as $version => $bundleClasses) {
            yield [
                'core-version'   => $version,
                'bundle-classes' => $bundleClasses,
            ];
        }
    }

    /**
     * Test that the bundles are correctly registered.
     *
     * @param string   $coreVersion   The core version to test.
     * @param string[] $bundleClasses The bundle classes to expect.
     *
     * @return void
     *
     * @dataProvider pluginTestProvider
     */
    public function testGetBundles(string $coreVersion, array $bundleClasses): void
    {
        $parser = $this->createMock(ParserInterface::class);
        $plugin = $this
            ->getMockBuilder(Plugin::class)
            ->setMethods(['getContaoCoreVersion'])
            ->getMock();
        $plugin->expects(self::once())->method('getContaoCoreVersion')->willReturn($coreVersion);
        /** @var Plugin $plugin */
        $bundles = $plugin->getBundles($parser);

        self::assertCount(count($bundleClasses), $bundles);
        foreach ($bundles as $index => $bundle) {
            self::assertInstanceOf(ConfigInterface::class, $bundle);
            self::assertSame($bundleClasses[$index], $bundle->getName());
            self::assertSame([], $bundle->getReplace());
            $loadAfter = array_merge([ContaoCoreBundle::class], array_slice($bundleClasses, 0, $index));
            sort($loadAfter);
            self::assertSame($loadAfter, $bundle->getLoadAfter());
            self::assertTrue($bundle->loadInDevelopment());
            self::assertTrue($bundle->loadInProduction());
        }
    }
}
