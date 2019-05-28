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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\DependencyInjection;

use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\CcaContaoPolyfill45Extension;
use ContaoCommunityAlliance\Polyfills\Polyfill45\Initialization\HookListenerRegistrar;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
        $container
            ->expects($this->once())
            ->method('setDefinition')
            ->with(HookListenerRegistrar::class);

        $extension = new CcaContaoPolyfill45Extension();

        $extension->load([['tagged_hooks' => true, 'asset' => false]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testLoadsAssetIfActive(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container
            ->expects($this->exactly(3))
            ->method('setDefinition')
            ->withConsecutive(
                ['cca.polyfill_45_event_listener.insert_tags_asset'],
                ['contao.assets.files_context'],
                ['contao.assets.plugins_context']
            );

        $extension = new CcaContaoPolyfill45Extension();

        $extension->load([['tagged_hooks' => false, 'asset' => true]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesLoadHooksAndAssetIfActive(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container
            ->expects($this->exactly(4))
            ->method('setDefinition')
            ->withConsecutive(
                [HookListenerRegistrar::class],
                ['cca.polyfill_45_event_listener.insert_tags_asset'],
                ['contao.assets.files_context'],
                ['contao.assets.plugins_context']
            );

        $extension = new CcaContaoPolyfill45Extension();

        $extension->load([['tagged_hooks' => true, 'asset' => true]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNotLoadHooksAndAssetIfDisabled(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container->expects($this->never())->method('setDefinition');

        $extension = new CcaContaoPolyfill45Extension();

        $extension->load([['tagged_hooks' => false, 'asset' => false]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testContainerCanBeCompiledWithAllFeatures(): void
    {
        $container = new ContainerBuilder();
        // Required by asset services.
        $container->setParameter('kernel.debug', false);
        $container->setDefinition('assets.packages', new Definition(\stdClass::class));
        $container->setDefinition('request_stack', new Definition(\stdClass::class));

        $container->registerExtension($extension = new CcaContaoPolyfill45Extension());
        $extension->load([['tagged_hooks' => true, 'asset' => true]], $container);
        $container->compile();
        // tagged_hooks services.
        $this->assertTrue($container->has(HookListenerRegistrar::class));

        // asset services.
        $this->assertTrue($container->has('cca.polyfill_45_event_listener.insert_tags_asset'));
        $this->assertTrue($container->has('contao.assets.files_context'));
        $this->assertTrue($container->has('contao.assets.plugins_context'));
    }
}
