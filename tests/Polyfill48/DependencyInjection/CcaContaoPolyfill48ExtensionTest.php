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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill48\DependencyInjection;

use ContaoCommunityAlliance\Polyfills\Polyfill48\Command\MigrateCommand;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Controller\MigrationController;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Database\MigrationInstaller;
use ContaoCommunityAlliance\Polyfills\Polyfill48\DependencyInjection\CcaContaoPolyfill48Extension;
use ContaoCommunityAlliance\Polyfills\Polyfill48\EventListener\MigrationApplicationListener;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Factory\ServiceFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\DependencyInjection\CcaContaoPolyfill48Extension
 */
class CcaContaoPolyfill48ExtensionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CcaContaoPolyfill48Extension::class, new CcaContaoPolyfill48Extension());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testLoadsMigrationIfActive(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container
            ->expects($this->exactly(6))
            ->method('setDefinition')
            ->withConsecutive(
                [MigrationCollectionPolyFill::class],
                [MigrationController::class],
                [MigrationApplicationListener::class],
                [ServiceFactory::class],
                [MigrationInstaller::class],
                [MigrateCommand::class]
            );

        $extension = new CcaContaoPolyfill48Extension();

        $extension->load([['migration' => true]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNotLoadMigrationIfDisabled(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['setDefinition'])
            ->getMock();
        $container->expects($this->never())->method('setDefinition');

        $extension = new CcaContaoPolyfill48Extension();

        $extension->load([['migration' => false]], $container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testContainerCanBeCompiledWithAllFeatures(): void
    {
        $container = new ContainerBuilder();
        // Required by migration services.
        $container->setParameter('kernel.project_dir', '');
        $container->setDefinition('database_connection', new Definition(\stdClass::class));
        $container->setDefinition('contao.resource_locator', new Definition(\stdClass::class));
        $container->setDefinition('contao.framework', new Definition(\stdClass::class));

        $container->registerExtension($extension = new CcaContaoPolyfill48Extension());
        $extension->load([['migration' => true]], $container);

        // migration services.
        $this->assertTrue($container->has(MigrationCollectionPolyFill::class));
        $this->assertTrue($container->has(MigrationController::class));
        $this->assertTrue($container->has(MigrationApplicationListener::class));
        $this->assertTrue($container->has(ServiceFactory::class));
        $this->assertTrue($container->has(MigrationInstaller::class));
        $this->assertTrue($container->has(MigrateCommand::class));

        $container->compile();

        // migration services not public.
        $this->assertFalse($container->has(MigrationCollectionPolyFill::class));
        $this->assertFalse($container->has(MigrationController::class));
        $this->assertFalse($container->has(MigrationApplicationListener::class));
        $this->assertFalse($container->has(ServiceFactory::class));
        $this->assertFalse($container->has(MigrationInstaller::class));
        $this->assertFalse($container->has(MigrateCommand::class));
    }
}
