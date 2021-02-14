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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Migration;

use Contao\CoreBundle\Migration\MigrationCollection;
use Contao\CoreBundle\Migration\MigrationResult;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationInterfacePolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationResultPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\AbstractMigrationPolyfill;
use PHPUnit\Framework\TestCase;

/**
 * Test the migration collection.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationResultPolyFill
 */
class MigrationPolyfillCollectionTest extends TestCase
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
        if (!\class_exists(\Contao\CoreBundle\Migration\AbstractMigration::class)) {
            \class_alias(AbstractMigrationPolyfill::class, \Contao\CoreBundle\Migration\AbstractMigration::class);
        }
    }

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

        self::assertSame(['Successful Migration', 'Failing Migration'], $pendingMigrations);
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

        self::assertCount(2, $results);
        self::assertInstanceOf(MigrationResult::class, $results[0]);
        self::assertTrue($results[0]->isSuccessful());
        self::assertSame('successful', $results[0]->getMessage());
        self::assertInstanceOf(MigrationResult::class, $results[1]);
        self::assertFalse($results[1]->isSuccessful());
        self::assertSame('failing', $results[1]->getMessage());
    }

    public function getMigrationServices(): array
    {
        return [
            new class() extends AbstractMigrationPolyfill {
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
            new class() extends AbstractMigrationPolyfill {
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
            new class() extends AbstractMigrationPolyfill {
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
