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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill48;

use ContaoCommunityAlliance\Polyfills\Polyfill48\CcaContaoPolyfill48Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill48\DependencyInjection\Compiler\TaggedMigrationsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\CcaContaoPolyfill48Bundle
 */
class CcaContaoPolyfill48BundleTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CcaContaoPolyfill48Bundle::class, new CcaContaoPolyfill48Bundle());
    }

    public function testRegistersCompilerPass(): void
    {
        $bundle = new CcaContaoPolyfill48Bundle();

        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container
            ->expects($this->once())
            ->method('addCompilerPass')
            ->withConsecutive(
                [$this->isInstanceOf(TaggedMigrationsPass::class)]
            )
            ->willReturn($container);

        $bundle->build($container);
    }
}
