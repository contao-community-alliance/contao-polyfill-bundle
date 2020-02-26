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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45;

use Contao\ManagerPlugin\Config\ContainerBuilder;
use ContaoCommunityAlliance\Polyfills\Polyfill45\CcaContaoPolyfill45Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\AddAssetsPackagesPass;
use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\RegisterHookListenersPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\CcaContaoPolyfill45Bundle
 */
class CcaContaoPolyfill45BundleTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CcaContaoPolyfill45Bundle::class, new CcaContaoPolyfill45Bundle());
    }

    public function testBootKernelInTwoTimes(): void
    {
        $bundle = new CcaContaoPolyfill45Bundle();

        $kernel = $this->getMockForAbstractClass(
            Kernel::class,
            [],
            'AppKernel',
            false,
            true,
            true,
            ['getBundles', 'initializeBundles', 'initializeContainer']
        );
        $kernel
            ->expects(self::exactly(3))
            ->method('getBundles')
            ->willReturnCallback(
                function () use ($bundle) {
                    return [$bundle];
                }
            );
        $kernel
            ->expects(self::exactly(2))
            ->method('initializeBundles');
        $kernel
            ->expects(self::exactly(2))
            ->method('initializeContainer');

        $kernel->boot();
        $kernel->shutdown();
        $kernel->boot();
    }

    public function testRegistersCompilerPass(): void
    {
        $bundle = new CcaContaoPolyfill45Bundle();

        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container
            ->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->withConsecutive(
                [$this->isInstanceOf(RegisterHookListenersPass::class), PassConfig::TYPE_OPTIMIZE],
                [$this->isInstanceOf(AddAssetsPackagesPass::class)]
            )
            ->willReturn($container);

        $bundle->build($container);
    }
}
