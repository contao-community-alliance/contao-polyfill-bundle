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
 * The result of migration.
 *
 * Make sure to use Contao\CoreBundle\Migration\MigrationResult instead.
 *
 * @internal
 */
final class MigrationResultPolyFill
{
    /**
     * Is the migration successful.
     *
     * @var bool
     */
    private $successful;

    /**
     * The migration message.
     *
     * @var string
     */
    private $message;

    /**
     * The constructor.
     *
     * @param bool   $successful Is the migration successful.
     * @param string $message    The migration message.
     */
    public function __construct(bool $successful, string $message)
    {
        $this->successful = $successful;
        $this->message    = $message;
    }

    /**
     * Determine is the migration successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    /**
     * Get the migration message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
