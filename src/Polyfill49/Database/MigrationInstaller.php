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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Database;

use Contao\CoreBundle\Doctrine\Schema\DcaSchemaProvider;
use Contao\Database\Installer;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * The schema provider.
     *
     * @var DcaSchemaProvider
     */
    private $schemaProvider;

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
    public function __construct(
        Connection $connection,
        DcaSchemaProvider $schemaProvider,
        ContainerInterface $container = null
    ) {
        if (null !== $container) {
            self::$objContainer = $container;
        }

        parent::__construct();

        $this->connection     = $connection;
        $this->schemaProvider = $schemaProvider;
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    // @codingStandardsIgnoreStart
    public function compileCommands(): array
    {
        $return = [
            'CREATE'       => [],
            'ALTER_TABLE'  => [],
            'ALTER_CHANGE' => [],
            'ALTER_ADD'    => [],
            'DROP'         => [],
            'ALTER_DROP'   => [],
        ];

        // The schema assets filter is a callable as of Doctrine DBAL 2.9
        $filter = static function (string $assetName): bool {
            return 0 === strncmp($assetName, 'tl_', 3);
        };

        $config = $this->connection->getConfiguration();

        // Overwrite the schema filter (see #78)
        $previousFilter = $config->getSchemaAssetsFilter();
        $config->setSchemaAssetsFilter($filter);

        // Create the from and to schema
        $fromSchema = $this->connection->getSchemaManager()->createSchema();
        $toSchema   = $this->schemaProvider->createSchema();

        // Reset the schema filter
        $config->setSchemaAssetsFilter($previousFilter);

        $diff = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($diff as $sql) {
            switch (true) {
                case 0 === strncmp($sql, 'CREATE TABLE ', 13):
                    $return['CREATE'][md5($sql)] = $sql;
                    break;

                case 0 === strncmp($sql, 'DROP TABLE ', 11):
                    $return['DROP'][md5($sql)] = $sql;
                    break;

                case 0 === strncmp($sql, 'CREATE INDEX ', 13):
                case 0 === strncmp($sql, 'CREATE UNIQUE INDEX ', 20):
                case 0 === strncmp($sql, 'CREATE FULLTEXT INDEX ', 22):
                    $return['ALTER_ADD'][md5($sql)] = $sql;
                    break;

                case 0 === strncmp($sql, 'DROP INDEX', 10):
                    $return['ALTER_CHANGE'][md5($sql)] = $sql;
                    break;

                case preg_match('/^(ALTER TABLE [^ ]+) /', $sql, $matches):
                    $prefix = $matches[1];
                    $sql    = substr($sql, \strlen($prefix));
                    $parts  = array_reverse(array_map('trim', explode(',', $sql)));

                    for ($i = 0, $count = \count($parts); $i < $count; ++$i) {
                        $part    = $parts[$i];
                        $command = $prefix . ' ' . $part;

                        switch (true) {
                            case 0 === strncmp($part, 'DROP ', 5):
                                $return['ALTER_DROP'][md5($command)] = $command;
                                break;

                            case 0 === strncmp($part, 'ADD ', 4):
                                $return['ALTER_ADD'][md5($command)] = $command;
                                break;

                            case 0 === strncmp($part, 'CHANGE ', 7):
                            case 0 === strncmp($part, 'RENAME ', 7):
                                $return['ALTER_CHANGE'][md5($command)] = $command;
                                break;

                            default:
                                $parts[$i + 1] .= ',' . $part;
                                break;
                        }
                    }
                    break;

                default:
                    throw new \RuntimeException(sprintf('Unsupported SQL schema diff: %s', $sql));
            }
        }

        $this->checkEngineAndCollation($return, $fromSchema, $toSchema);

        $return = array_filter($return);

        // HOOK: allow third-party developers to modify the array (see #3281)
        if (isset($GLOBALS['TL_HOOKS']['sqlCompileCommands'])
            && \is_array(
                $GLOBALS['TL_HOOKS']['sqlCompileCommands']
            )) {
            foreach ($GLOBALS['TL_HOOKS']['sqlCompileCommands'] as $callback) {
                $return = System::importStatic($callback[0])->{$callback[1]}($return);
            }
        }

        return $this->commands = $return;
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

    /**
     * Checks engine and collation and adds the ALTER TABLE queries.
     *
     * @param array  $sql        The sql parts.
     * @param Schema $fromSchema The from schema.
     * @param Schema $toSchema   The to schema.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function checkEngineAndCollation(array &$sql, Schema $fromSchema, Schema $toSchema): void
    {
        $tables  = $toSchema->getTables();
        $dynamic = $this->hasDynamicRowFormat();

        foreach ($tables as $table) {
            $tableName     = $table->getName();
            $alterTables   = [];
            $deleteIndexes = false;

            if (0 !== strncmp($tableName, 'tl_', 3)) {
                continue;
            }

            $this->setLegacyOptions($table);

            $tableOptions = $this->connection
                ->query("SHOW TABLE STATUS LIKE '" . $tableName . "'")
                ->fetch(\PDO::FETCH_OBJ);

            // The table does not yet exist
            if (false === $tableOptions) {
                continue;
            }

            $engine = $table->getOption('engine');
            $innodb = 'innodb' === strtolower($engine);

            if (strtolower($tableOptions->Engine) !== strtolower($engine)) {
                if ($innodb && $dynamic) {
                    $command = 'ALTER TABLE ' . $tableName . ' ENGINE = ' . $engine . ' ROW_FORMAT = DYNAMIC';
                } else {
                    $command = 'ALTER TABLE ' . $tableName . ' ENGINE = ' . $engine;
                }

                $deleteIndexes              = true;
                $alterTables[md5($command)] = $command;
            } elseif ($innodb && $dynamic) {
                if (false === stripos($tableOptions->Create_options, 'row_format=dynamic')) {
                    $command                    =
                        'ALTER TABLE ' . $tableName . ' ENGINE = ' . $engine . ' ROW_FORMAT = DYNAMIC';
                    $alterTables[md5($command)] = $command;
                }
            }

            $collate = $table->getOption('collate');

            if ($tableOptions->Collation !== $collate) {
                $charset                    = $table->getOption('charset');
                $command                    =
                    'ALTER TABLE ' . $tableName . ' CONVERT TO CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
                $deleteIndexes              = true;
                $alterTables[md5($command)] = $command;
            }

            // Delete the indexes if the engine changes in case the existing
            // indexes are too long. The migration then needs to be run muliple
            // times to re-create the indexes with the correct length.
            if ($deleteIndexes) {
                if (!$fromSchema->hasTable($tableName)) {
                    continue;
                }

                $platform = $this->connection->getDatabasePlatform();

                foreach ($fromSchema->getTable($tableName)->getIndexes() as $index) {
                    $indexName = $index->getName();

                    if ('primary' === strtolower($indexName)) {
                        continue;
                    }

                    $indexCommand = $platform->getDropIndexSQL($indexName, $tableName);
                    $strKey       = md5($indexCommand);

                    if (isset($sql['ALTER_CHANGE'][$strKey])) {
                        unset($sql['ALTER_CHANGE'][$strKey]);
                    }

                    $sql['ALTER_TABLE'][$strKey] = $indexCommand;
                }
            }

            foreach ($alterTables as $k => $alterTable) {
                $sql['ALTER_TABLE'][$k] = $alterTable;
            }
        }
    }

    /**
     * Has dynmaic row format.
     *
     * @return bool
     */
    private function hasDynamicRowFormat(): bool
    {
        $filePerTable = $this->connection
            ->query("SHOW VARIABLES LIKE 'innodb_file_per_table'")
            ->fetch(\PDO::FETCH_OBJ);

        // Dynamic rows require innodb_file_per_table to be enabled
        if (!\in_array(strtolower((string) $filePerTable->Value), ['1', 'on'], true)) {
            return false;
        }

        $fileFormat = $this->connection
            ->query("SHOW VARIABLES LIKE 'innodb_file_format'")
            ->fetch(\PDO::FETCH_OBJ);

        // MySQL 8 and MariaDB 10.3 no longer have the "innodb_file_format" setting
        if (false === $fileFormat || '' === $fileFormat->Value) {
            return true;
        }

        // Dynamic rows require the Barracuda file format in MySQL <8 and MariaDB <10.3
        return 'barracuda' === strtolower((string) $fileFormat->Value);
    }

    /**
     * Adds the legacy table options to remain backwards compatibility with database.sql files.
     *
     * @param Table $table The schema table.
     *
     * @return void
     */
    private function setLegacyOptions(Table $table): void
    {
        if (!$table->hasOption('engine')) {
            $table->addOption('engine', 'MyISAM');
        }

        if (!$table->hasOption('charset')) {
            $table->addOption('charset', 'utf8');
        }

        if (!$table->hasOption('collate')) {
            $table->addOption('collate', 'utf8_general_ci');
        }
    }
}
