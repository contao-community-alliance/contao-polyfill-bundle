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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;

/**
 * The abstract migration.
 */
abstract class AbstractMigration implements MigrationInterface
{
    protected function createResult(bool $successful, string $message = null): MigrationResult
    {
        if (null === $message) {
            $message = $this->getName().' '.($successful ? 'executed successfully' : 'execution failed');
        }

        return new MigrationResult($successful, $message);
    }
}
