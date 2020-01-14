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
 * @author     Martin Auswöger <martin@auswoeger.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill48\Migration;

/**
 * The collection of migrations.
 */
class MigrationCollection
{
    /**
     * The migrations.
     *
     * @var MigrationInterface[]|iterable
     */
    private $migrations;

    /**
     * The constructor.
     *
     * @param MigrationInterface[]|iterable $migrations The migrations.
     */
    public function __construct(iterable $migrations)
    {
        $this->migrations = $migrations;
    }

    /**
     * Get the pending migrations.
     *
     * @return MigrationInterface[]
     */
    public function getPending(): iterable
    {
        foreach ($this->migrations as $migration) {
            if ($migration->shouldRun()) {
                yield $migration;
            }
        }
    }

    /**
     * Get the names of pending migrations.
     *
     * @return string[]
     */
    public function getPendingNames(): iterable
    {
        foreach ($this->getPending() as $migration) {
            yield $migration->getName();
        }
    }

    /**
     * Run the collection of migrations.
     *
     * @return MigrationResult[]
     */
    public function run(): iterable
    {
        foreach ($this->getPending() as $migration) {
            yield $migration->run();
        }
    }
}
