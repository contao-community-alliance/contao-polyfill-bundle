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

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Factory;

use Contao\CoreBundle\Doctrine\Schema\DcaSchemaProvider;
use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationInstaller;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Factory\ServiceFactory;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\Factory\ServiceFactory
 */
class ServiceFactoryTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', \sys_get_temp_dir());
        }

        self::aliasContaoClass('System');
        self::aliasContaoClass('Config');
        self::aliasContaoClass('Controller');
    }

    public function testCreateMigrationInstaller(): void
    {
        $connection     = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $schemaProvider = $this->getMockBuilder(DcaSchemaProvider::class)->disableOriginalConstructor()->getMock();
        $container      = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->willReturn(false);
        $container
            ->method('getParameter')
            ->willReturnCallback(
                function ($name) {
                    if ('kernel.project_dir' === $name) {
                        return 'foo';
                    }
                    if ('kernel.cache_dir' === $name) {
                        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures/cache';
                    }

                    $this->fail('Unknown parameter ' . $name . ' requested.');
                }
            );
        $framework = $this
            ->getMockBuilder(ContaoFramework::class)
            ->disableOriginalConstructor()
            ->setMethods(['initialize'])
            ->getMock();

        $initialize = false;
        $framework
            ->expects(self::once())
            ->method('initialize')
            ->willReturnCallback(
                function () use (&$initialize) {
                    $initialize = true;
                }
            );

        $factory = new ServiceFactory($framework, $connection, $schemaProvider, $container);

        $migrationInstaller = $factory->createMigrationInstaller();

        self::assertTrue($initialize);
        self::assertInstanceOf(MigrationInstaller::class, $migrationInstaller);
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
