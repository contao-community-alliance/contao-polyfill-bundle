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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\EventListener;

use Contao\CoreBundle\Migration\MigrationCollection;
use Contao\InstallationBundle\Event\InitializeApplicationEvent;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Controller\MigrationController;
use ContaoCommunityAlliance\Polyfills\Polyfill49\EventListener\MigrationApplicationListener;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationInterfacePolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationResultPolyFill;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\EventListener\MigrationApplicationListener
 */
class MigrationApplicationListenerTest extends TestCase
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
            \class_alias(MigrationCollectionPolyFill::class, \Contao\CoreBundle\Migration\MigrationResult::class);
        }
    }

    public function testHasNoPendingMigrations(): void
    {
        $event = $this->createMock(InitializeApplicationEvent::class);

        $controller = $this
            ->getMockBuilder(MigrationController::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $invokeController = false;
        $controller
            ->expects(self::never())
            ->method('__invoke')
            ->willReturnCallback(
                function () use (&$invokeController) {
                    $invokeController = true;
                }
            );

        $migrations = $this
            ->getMockBuilder(MigrationCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPendingNames'])
            ->getMock();
        $migrations
            ->expects(self::once())
            ->method('getPendingNames')
            ->willReturn(new \ArrayIterator());

        $listener = new MigrationApplicationListener($controller, $migrations);
        $listener->__invoke($event);

        self::assertFalse($invokeController);
    }

    public function testHasPendingMigrations(): void
    {
        $event = $this->createMock(InitializeApplicationEvent::class);

        $controller = $this
            ->getMockBuilder(MigrationController::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();

        $invokeController = false;
        $controller
            ->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                function () use (&$invokeController) {
                    $invokeController = true;
                }
            );

        $migrations = $this
            ->getMockBuilder(MigrationCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPendingNames'])
            ->getMock();
        $migrations
            ->expects(self::once())
            ->method('getPendingNames')
            ->willReturn(new \ArrayIterator(['foo', 'bar']));

        $listener = new MigrationApplicationListener($controller, $migrations);
        $listener->__invoke($event);

        self::assertTrue($invokeController);
    }
}
