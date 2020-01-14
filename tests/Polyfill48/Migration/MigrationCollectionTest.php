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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill48\Migration;

use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationCollection;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationResult;
use ContaoCommunityAlliance\Polyfills\Test\Polyfill48\Fixtures\AbstractMigration;
use PHPUnit\Framework\TestCase;

/**
 * Test the migration collection.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationCollection
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationResult
 */
class MigrationCollectionTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testGetPendingNames(): void
    {
        $migrations        = new MigrationCollection($this->getMigrationServices());
        $pendingMigrations = $migrations->getPendingNames();

        if (!\is_array($pendingMigrations)) {
            $pendingMigrations = \iterator_to_array($pendingMigrations);
        }

        $this->assertSame(['Successful Migration', 'Failing Migration'], $pendingMigrations);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testRunMigrations(): void
    {
        $migrations = new MigrationCollection($this->getMigrationServices());
        $results    = $migrations->run();

        if (!\is_array($results)) {
            $results = \iterator_to_array($results);
        }

        $this->assertCount(2, $results);
        $this->assertInstanceOf(MigrationResult::class, $results[0]);
        $this->assertTrue($results[0]->isSuccessful());
        $this->assertSame('successful', $results[0]->getMessage());
        $this->assertInstanceOf(MigrationResult::class, $results[1]);
        $this->assertFalse($results[1]->isSuccessful());
        $this->assertSame('failing', $results[1]->getMessage());
    }

    public function getMigrationServices(): array
    {
        return [
            new class() extends AbstractMigration {
                public function getName(): string
                {
                    return 'Successful Migration';
                }

                public function shouldRun(): bool
                {
                    return true;
                }

                public function run(): MigrationResult
                {
                    return $this->createResult(true, 'successful');
                }
            },
            new class() extends AbstractMigration {
                public function getName(): string
                {
                    return 'Failing Migration';
                }

                public function shouldRun(): bool
                {
                    return true;
                }

                public function run(): MigrationResult
                {
                    return $this->createResult(false, 'failing');
                }
            },
            new class() extends AbstractMigration {
                public function getName(): string
                {
                    return 'Inactive Migration';
                }

                public function shouldRun(): bool
                {
                    return false;
                }

                public function run(): MigrationResult
                {
                    throw new \LogicException('Should never be executed');
                }
            },
        ];
    }
}
