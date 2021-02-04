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

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Database;

use ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationPolyfillWrapper;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationInterfacePolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationResultPolyFill;
use ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Fixtures\Updater;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationPolyfillWrapper
 */
class MigrationWrapperTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!\class_exists(\Contao\CoreBundle\Migration\MigrationCollection::class)) {
            \class_alias(MigrationCollectionPolyFill::class, \Contao\CoreBundle\Migration\MigrationCollection::class);
        }
        if (!\interface_exists(\Contao\CoreBundle\Migration\MigrationInterface::class)) {
            \class_alias(MigrationInterfacePolyFill::class, \Contao\CoreBundle\Migration\MigrationInterface::class);
        }
        if (!\class_exists(\Contao\CoreBundle\Migration\MigrationResult::class)) {
            \class_alias(MigrationResultPolyFill::class, \Contao\CoreBundle\Migration\MigrationResult::class);
        }
    }

    public function testGetName(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $connection = $this->createMock(Connection::class);

        $wrapper = new MigrationPolyfillWrapper($container, $connection, Updater::class);

        self::assertSame(
            'ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Fixtures\Updater',
            $wrapper->getName()
        );
    }

    public function testNotShouldRun(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $connection = $this->createMock(Connection::class);

        $wrapper = new MigrationPolyfillWrapper($container, $connection, Updater::class);

        $updater = $this->getMockBuilder(Updater::class)
            ->disableOriginalConstructor()
            ->setMethods(['shouldBeRun'])
            ->getMock();
        $updater
            ->expects(self::once())
            ->method('shouldBeRun')
            ->willReturn(false);

        $reflection         = new \ReflectionObject($wrapper);
        $reflectionProperty = $reflection->getProperty('updater');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($wrapper, $updater);

        self::assertFalse($wrapper->shouldRun());
    }

    public function testShouldRun(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $connection = $this->createMock(Connection::class);

        $wrapper = new MigrationPolyfillWrapper($container, $connection, Updater::class);

        $updater = $this->getMockBuilder(Updater::class)
            ->disableOriginalConstructor()
            ->setMethods(['shouldBeRun'])
            ->getMock();
        $updater
            ->expects(self::once())
            ->method('shouldBeRun')
            ->willReturn(true);

        $reflection         = new \ReflectionObject($wrapper);
        $reflectionProperty = $reflection->getProperty('updater');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($wrapper, $updater);

        self::assertTrue($wrapper->shouldRun());
    }

    public function testRunNotSuccessful(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $connection = $this->createMock(Connection::class);

        $wrapper = new MigrationPolyfillWrapper($container, $connection, Updater::class);

        $updater = $this->getMockBuilder(Updater::class)
            ->disableOriginalConstructor()
            ->setMethods(['run', 'hasMessage', 'getMessage'])
            ->getMock();
        $updater
            ->expects(self::once())
            ->method('run')
            ->willThrowException(new \Exception());
        $updater
            ->expects(self::never())
            ->method('hasMessage')
            ->willReturn(false);
        $updater
            ->expects(self::once())
            ->method('getMessage');

        $reflection         = new \ReflectionObject($wrapper);
        $reflectionProperty = $reflection->getProperty('updater');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($wrapper, $updater);

        $result = $wrapper->run();
        self::assertFalse($result->isSuccessful());
        self::assertSame(\get_class($updater) . ' execution failed', $result->getMessage());
    }

    public function testRunSuccessful(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $connection = $this->createMock(Connection::class);

        $wrapper = new MigrationPolyfillWrapper($container, $connection, Updater::class);

        $runUpdater = false;
        $updater    = $this->getMockBuilder(Updater::class)
            ->disableOriginalConstructor()
            ->setMethods(['run', 'hasMessage', 'getMessage'])
            ->getMock();
        $updater
            ->expects(self::once())
            ->method('run')
            ->willReturnCallback(
                function () use (&$runUpdater) {
                    $runUpdater = true;
                }
            );
        $updater
            ->expects(self::never())
            ->method('hasMessage')
            ->willReturn(false);
        $updater
            ->expects(self::once())
            ->method('getMessage');

        $reflection         = new \ReflectionObject($wrapper);
        $reflectionProperty = $reflection->getProperty('updater');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($wrapper, $updater);

        $result = $wrapper->run();
        self::assertTrue($runUpdater);
        self::assertTrue($result->isSuccessful());
        self::assertSame(\get_class($updater) . ' executed successfully', $result->getMessage());
    }

    public function testRunSuccessfulAlternativeMessage(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $connection = $this->createMock(Connection::class);

        $wrapper = new MigrationPolyfillWrapper($container, $connection, Updater::class);

        $runUpdater = false;
        $updater    = $this->getMockBuilder(Updater::class)
            ->disableOriginalConstructor()
            ->setMethods(['run', 'hasMessage', 'getMessage'])
            ->getMock();
        $updater
            ->expects(self::once())
            ->method('run')
            ->willReturnCallback(
                function () use (&$runUpdater) {
                    $runUpdater = true;
                }
            );
        $updater
            ->expects(self::never())
            ->method('hasMessage')
            ->willReturn(true);
        $updater
            ->expects(self::once())
            ->method('getMessage')
            ->willReturn('Alternative message');

        $reflection         = new \ReflectionObject($wrapper);
        $reflectionProperty = $reflection->getProperty('updater');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($wrapper, $updater);

        $result = $wrapper->run();
        self::assertTrue($runUpdater);
        self::assertTrue($result->isSuccessful());
        self::assertSame('Alternative message', $result->getMessage());
    }
}
