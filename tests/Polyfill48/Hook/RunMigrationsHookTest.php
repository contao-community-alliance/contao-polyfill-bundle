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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill48\Hook;

use ContaoCommunityAlliance\Polyfills\Polyfill48\Hook\RunMigrationsHook;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationInterfacePolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationResultPolyFill;
use PHPUnit\Framework\TestCase;

/**
 * Test the run migration hook.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\Hook\RunMigrationsHook
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationCollectionPolyFill
 */
class RunMigrationsHookTest extends TestCase
{
    /**
     * Create the test migration.
     *
     * @return \Generator
     */
    public function createMigration(): \Generator
    {
        yield 'Test successful migration' => [
            'name'       => 'successful',
            'shouldRun'  => true,
            'successful' => true
        ];

        yield 'Test not successful migration' => [
            'name'       => 'not successful',
            'shouldRun'  => true,
            'successful' => false
        ];

        yield 'Test not run migration' => [
            'name'       => 'not run',
            'shouldRun'  => false,
            'successful' => true
        ];
    }

    /**
     * Test.
     *
     * @param string $name       The name.
     * @param bool   $shouldRun  Should run the migration.
     * @param bool   $successful Migration is successful.
     *
     * @dataProvider createMigration
     *
     * @return void
     */
    public function testRunNotEmptyMigrationCollection(string $name, bool $shouldRun, bool $successful): void
    {
        $migration = $this->createMock(MigrationInterfacePolyFill::class);
        $migration
            ->expects(self::never())
            ->method('getName')
            ->willReturn($name);
        $migration
            ->expects(self::once())
            ->method('shouldRun')
            ->willReturn($shouldRun);
        $migration
            ->expects($shouldRun ? self::once() : self::never())
            ->method('run')
            ->willReturn(new MigrationResultPolyFill($successful, $name));

        $migrations = new MigrationCollectionPolyFill([$migration]);

        $hook = new RunMigrationsHook($migrations);
        $hook->__invoke();
    }
}
