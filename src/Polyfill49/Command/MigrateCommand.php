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
 * @author     Martin Auswöger <martin@auswoeger.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Command;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationInstaller;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The migration command.
 */
final class MigrateCommand extends Command
{
    /**
     * The migration collection.
     *
     * @var MigrationCollectionPolyFill
     */
    private $migrations;

    /**
     * The file locator.
     *
     * @var FileLocator
     */
    private $fileLocator;

    /**
     * The project dir.
     *
     * @var string
     */
    private $projectDir;

    /**
     * The framework.
     *
     * @var ContaoFramework
     */
    private $framework;

    /**
     * The installer.
     *
     * @var ?MigrationInstaller
     */
    private $installer;

    /**
     * The output style.
     *
     * @var SymfonyStyle
     */
    private $ios;

    /**
     * The constructor.
     *
     * @param MigrationCollectionPolyFill $migrations  The migration collection.
     * @param FileLocator                 $fileLocator The file locator.
     * @param string                      $projectDir  The project dir.
     * @param ContaoFramework             $framework   The framework.
     * @param MigrationInstaller          $installer   The installer.
     */
    public function __construct(
        MigrationCollectionPolyFill $migrations,
        FileLocator $fileLocator,
        string $projectDir,
        ContaoFramework $framework,
        MigrationInstaller $installer
    ) {
        $this->migrations  = $migrations;
        $this->fileLocator = $fileLocator;
        $this->projectDir  = $projectDir;
        $this->framework   = $framework;
        $this->installer   = $installer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('contao:migrate')
            ->addOption(
                'with-deletes',
                null,
                InputOption::VALUE_NONE,
                'Execute all database migrations including DROP queries. Can be used together with --no-interaction.'
            )
            ->addOption('schema-only', null, InputOption::VALUE_NONE, 'Execute database schema migration only.')
            ->setDescription('Executes migrations and the database schema diff.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ios = new SymfonyStyle($input, $output);

        if ($input->getOption('schema-only')) {
            return $this->executeSchemaDiff($input->getOption('with-deletes')) ? 0 : 1;
        }

        if (!$this->executeMigrations()) {
            return 1;
        }

        if (!$this->executeSchemaDiff($input->getOption('with-deletes'))) {
            return 1;
        }

        if (!$this->executeMigrations()) {
            return 1;
        }

        $this->ios->success('All migrations completed.');

        return 0;
    }

    /**
     * Execute the migrations.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function executeMigrations(): bool
    {
        while (true) {
            $first = true;

            foreach ($this->migrations->getPendingNames() as $migration) {
                if ($first) {
                    $this->ios->section('Pending migrations');
                    $first = false;
                }

                $this->ios->writeln(' * ' . $migration);
            }

            $runOnceFiles = $this->getRunOnceFiles();

            if ($runOnceFiles) {
                // @codingStandardsIgnoreStart
                @\trigger_error(
                    'Using runonce.php files has been deprecated and will no longer work in Contao 5.0. ' .
                    'Use the migration framework instead.',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
            }

            foreach ($runOnceFiles as $file) {
                if ($first) {
                    $this->ios->section('Pending migrations');
                    $first = false;
                }

                $this->ios->writeln(' * Runonce file: ' . $file);
            }

            if ($first) {
                break;
            }

            if (!$this->ios->confirm('Execute the listed migrations?')) {
                return false;
            }

            $this->ios->section('Execute migrations');

            $count = 0;

            foreach ($this->migrations->run() as $result) {
                ++$count;

                $this->ios->writeln(' * ' . $result->getMessage());

                if (!$result->isSuccessful()) {
                    $this->ios->error('Migration failed');
                }
            }

            foreach ($this->getRunOnceFiles() as $file) {
                ++$count;

                $this->executeRunonceFile($file);
                $this->ios->writeln(' * Executed runonce file: ' . $file);
            }

            $this->ios->success('Executed ' . $count . ' migrations.');
        }

        return true;
    }

    /**
     * Get run once files.
     *
     * @return array
     */
    private function getRunOnceFiles(): array
    {
        try {
            $files = $this->fileLocator->locate('config/runonce.php', null, false);
        } catch (FileLocatorFileNotFoundException $e) {
            return [];
        }

        return \array_map(
            function ($path) {
                return \rtrim((new Filesystem())->makePathRelative($path, $this->projectDir), '/');
            },
            $files
        );
    }

    /**
     * Execute run once file.
     *
     * @param string $file The file.
     *
     * @return void
     */
    private function executeRunonceFile(string $file): void
    {
        $this->framework->initialize();

        include $this->projectDir . '/' . $file;

        (new Filesystem())->remove($this->projectDir . '/' . $file);
    }

    /**
     * Execute schema diff.
     *
     * @param bool $withDeletesOption With deletes option.
     *
     * @return bool
     */
    private function executeSchemaDiff(bool $withDeletesOption): bool
    {
        if (null === $this->installer) {
            $this->ios->error(
                'Service "contao.installer" not found. ' .
                'The installation bundle needs to be installed in order to execute schema diff migrations.'
            );

            return false;
        }

        $commandsByHash = [];

        while (true) {
            $this->installer->compileCommands();

            if (!$commands = $this->installer->getCommands()) {
                return true;
            }

            $hasNewCommands = \count(
                \array_filter(
                    \array_keys(\array_merge(...\array_values($commands))),
                    function ($hash) use ($commandsByHash) {
                        return !isset($commandsByHash[$hash]);
                    }
                )
            );

            if (!$hasNewCommands) {
                return true;
            }

            $this->ios->section('Pending database migrations');

            $commandsByHash = \array_merge(...\array_values($commands));

            $this->ios->listing($commandsByHash);

            $options = $withDeletesOption
                ? ['yes, with deletes', 'no']
                : ['yes', 'yes, with deletes', 'no'];

            $answer = $this->ios->choice('Execute the listed database updates?', $options, $options[0]);

            if ('no' === $answer) {
                return false;
            }

            $this->ios->section('Execute database migrations');

            $count = 0;

            foreach ($this->getCommandHashes($commands, 'yes, with deletes' === $answer) as $hash) {
                $this->ios->writeln(' * ' . $commandsByHash[$hash]);
                $this->installer->execCommand($hash);
                ++$count;
            }

            $this->ios->success('Executed ' . $count . ' SQL queries.');
        }

        return true;
    }

    /**
     * Get command hash.
     *
     * @param array $commands  The commands.
     * @param bool  $withDrops With drop.
     *
     * @return array
     */
    private function getCommandHashes(array $commands, bool $withDrops): array
    {
        if (!$withDrops) {
            unset($commands['ALTER_DROP']);

            foreach ($commands as $category => $commandsByHash) {
                foreach ($commandsByHash as $hash => $command) {
                    if ('DROP' === $category && false === \strpos($command, 'DROP INDEX')) {
                        unset($commands[$category][$hash]);
                    }
                }
            }
        }

        return \count($commands) ? \array_keys(\array_merge(...\array_values($commands))) : [];
    }
}
