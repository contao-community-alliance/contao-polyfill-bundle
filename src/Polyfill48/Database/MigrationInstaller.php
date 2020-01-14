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
 * @author     Leo Feyer <github@contao.org>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill48\Database;

use Contao\Database\Installer;
use Doctrine\DBAL\Connection;

/**
 * The database installer.
 */
class MigrationInstaller extends Installer
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The commands.
     *
     * @var array
     */
    private $commands = null;

    /**
     * The constructor.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    /**
     * Get the commands.
     *
     * @return string[]
     */
    public function getCommands(): array
    {
        if (null === $this->commands) {
            $this->compileCommands();
        }

        return $this->commands;
    }

    /**
     * {@inheritDoc}
     */
    // @codingStandardsIgnoreStart
    public function compileCommands(): array
    {
        return parent::compileCommands();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Exec the command.
     *
     * @param string $hash The command hash.
     *
     * @return void
     *
     * @throws \InvalidArgumentException Throws exception if hash is invalid.
     */
    public function execCommand(string $hash): void
    {
        if (null === $this->commands) {
            $this->compileCommands();
        }

        foreach ($this->commands as $commands) {
            if (isset($commands[$hash])) {
                $this->connection->query($commands[$hash]);

                return;
            }
        }

        throw new \InvalidArgumentException(sprintf('Invalid hash: %s', $hash));
    }
}
