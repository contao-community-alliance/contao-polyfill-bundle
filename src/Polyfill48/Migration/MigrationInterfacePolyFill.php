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

use Contao\CoreBundle\Migration\MigrationResult;

/**
 * The interface for migration.
 *
 * Make sure to use Contao\CoreBundle\Migration\MigrationInterface instead.
 *
 * @internal
 */
interface MigrationInterfacePolyFill
{
    /**
     * The name of the migration.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Should run the migration.
     *
     * @return bool
     */
    public function shouldRun(): bool;

    /**
     * Run the migration.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult;
}
