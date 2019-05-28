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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\DependencyInjection;

use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\CcaContaoPolyfill45Extension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\CcaContaoPolyfill45Extension
 */
class CcaContaoPolyfill45ExtensionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CcaContaoPolyfill45Extension::class, new CcaContaoPolyfill45Extension());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testLoadsHooksIfActive(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container->expects($this->once())->method('setDefinition');

        $extension = new CcaContaoPolyfill45Extension();

        $extension->load([['tagged_hooks' => true]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNotLoadHooksIfDisabled(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container->expects($this->never())->method('setDefinition');

        $extension = new CcaContaoPolyfill45Extension();

        $extension->load([['tagged_hooks' => false]], $container);
    }
}
