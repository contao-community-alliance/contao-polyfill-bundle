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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\DependencyInjection;

use Contao\CoreBundle\Migration\MigrationCollection;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Command\MigrateCommand;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Controller\MigrationController;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationInstaller;
use ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\CcaContaoPolyfill49Extension;
use ContaoCommunityAlliance\Polyfills\Polyfill49\EventListener\MigrationApplicationListener;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Installation\InstallTool;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\FixVersion447Update;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Factory\ServiceFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\CcaContaoPolyfill49Extension
 */
class CcaContaoPolyfill49ExtensionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(CcaContaoPolyfill49Extension::class, new CcaContaoPolyfill49Extension());
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
            ->expects(self::exactly(8))
            ->method('setDefinition')
            ->withConsecutive(
                [MigrationCollectionPolyFill::class],
                [MigrationController::class],
                [MigrationApplicationListener::class],
                [ServiceFactory::class],
                [MigrationInstaller::class],
                [MigrateCommand::class],
                [FixVersion447Update::class],
                [InstallTool::class]
            );

        $extension = new CcaContaoPolyfill49Extension();

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
        $container->expects(self::never())->method('setDefinition');

        $extension = new CcaContaoPolyfill49Extension();

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
        $container->setDefinition('logger', new Definition(\stdClass::class));

        $container->registerExtension($extension = new CcaContaoPolyfill49Extension());
        $extension->load([['migration' => true]], $container);

        // migration services.
        self::assertTrue($container->has(MigrationCollectionPolyFill::class));
        self::assertTrue($container->has(MigrationController::class));
        self::assertTrue($container->has(MigrationApplicationListener::class));
        self::assertTrue($container->has(ServiceFactory::class));
        self::assertTrue($container->has(MigrationInstaller::class));
        self::assertTrue($container->has(MigrateCommand::class));
        // It set public true in the compiler pass.
        $container->getDefinition(MigrationCollectionPolyFill::class)->setPublic(true);
        $container->compile();

        // migration services must be public.
        self::assertTrue($container->has(MigrationCollectionPolyFill::class));
        // migration services not public.
        self::assertFalse($container->has(MigrationController::class));
        self::assertFalse($container->has(MigrationApplicationListener::class));
        self::assertFalse($container->has(ServiceFactory::class));
        self::assertFalse($container->has(MigrationInstaller::class));
        self::assertFalse($container->has(MigrateCommand::class));
    }
}
