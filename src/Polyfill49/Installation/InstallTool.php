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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Installation;

use Contao\InstallationBundle\InstallTool as ContaoInstallTool;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * The install tool.
 */
class InstallTool extends ContaoInstallTool
{
    /**
     * The connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The root dir.
     *
     * @var string
     */
    private $rootDir;

    /**
     * The logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InstallTool constructor.
     *
     * @param Connection      $connection The connection.
     * @param string          $rootDir    The root dir.
     * @param LoggerInterface $logger     The logger.
     */
    public function __construct(Connection $connection, string $rootDir, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->rootDir    = $rootDir;
        $this->logger     = $logger;

        parent::__construct($connection, $rootDir, $logger);
    }

    /**
     * Checks if the database is older than version 3.2.
     * Fix for has old database contao lower than 4.9.
     *
     * @return bool
     */
    public function hasOldDatabase(): bool
    {
        if (!$this->hasTable('tl_layout')) {
            return false;
        }

        $sql = $this->connection
            ->getDatabasePlatform()
            ->getListTableColumnsSQL('tl_layout', $this->connection->getDatabase());

        $columns = $this->connection->fetchAll($sql);

        foreach ($columns as $column) {
            if ('sections' === $column['Field']) {
                return !\in_array($column['Type'], ['varchar(1022)', 'blob'], true);
            }
        }

        return false;
    }
}
