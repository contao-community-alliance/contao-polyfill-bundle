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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Command\MigrateCommand;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationInstaller;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationResultPolyFill;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test the migrate command.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\Command\MigrateCommand
 */
final class MigrateCommandTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Some class mapping for Contao 4.4.
        self::aliasContaoClass('System');
        self::aliasContaoClass('Controller');
    }

    public function testExecutesWithoutPendingMigrations(): void
    {
        $command = $this->getCommand();
        $tester  = new CommandTester($command);
        $code    = $tester->execute([]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $code);
        $this->assertRegExp('/All migrations completed/', $display);
    }

    public function testExecutesPendingMigrations(): void
    {
        $command = $this->getCommand(
            [['Migration 1', 'Migration 2']],
            [[new MigrationResultPolyFill(true, 'Result 1'), new MigrationResultPolyFill(true, 'Result 2')]]
        );

        $tester = new CommandTester($command);
        $tester->setInputs(['y']);

        $code    = $tester->execute([]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $code);
        $this->assertRegExp('/Migration 1/', $display);
        $this->assertRegExp('/Migration 2/', $display);
        $this->assertRegExp('/Result 1/', $display);
        $this->assertRegExp('/Result 2/', $display);
        $this->assertRegExp('/Executed 2 migrations/', $display);
        $this->assertRegExp('/All migrations completed/', $display);
    }

    /**
     * @group               legacy
     *
     * @expectedDeprecation Using runonce.php files has been deprecated %s.
     */
    public function testExecutesRunOnceFiles(): void
    {
        $runOnceFile = $this->getFixturesDir() . '/runonceFile.php';

        \file_put_contents($runOnceFile, '<?php $GLOBALS["test_' . self::class . '"] = "executed";');

        $command = $this->getCommand([], [], [[$runOnceFile]]);

        $tester = new CommandTester($command);
        $tester->setInputs(['y']);

        $code    = $tester->execute([]);
        $display = $tester->getDisplay();

        $this->assertSame('executed', $GLOBALS['test_' . self::class]);

        unset($GLOBALS['test_' . self::class]);

        $this->assertSame(0, $code);
        $this->assertRegExp('/runonceFile.php/', $display);
        $this->assertRegExp('/All migrations completed/', $display);
    }

    public function testExecutesSchemaDiff(): void
    {
        $installer = $this->createMock(MigrationInstaller::class);
        $installer
            ->expects($this->atLeastOnce())
            ->method('compileCommands');

        $installer
            ->expects($this->atLeastOnce())
            ->method('getCommands')
            ->willReturn(
                [
                    'CREATE' => ['hash1' => 'First call QUERY 1', 'hash2' => 'First call QUERY 2'],
                ],
                [
                    'CREATE' => ['hash3' => 'Second call QUERY 1', 'hash4' => 'Second call QUERY 2'],
                    'DROP'   => ['hash5' => 'DROP QUERY'],
                ],
                []
            );

        $command = $this->getCommand([], [], [], $installer);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes', 'yes']);

        $code    = $tester->execute([]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $code);
        $this->assertRegExp('/First call QUERY 1/', $display);
        $this->assertRegExp('/First call QUERY 2/', $display);
        $this->assertRegExp('/Second call QUERY 1/', $display);
        $this->assertRegExp('/Second call QUERY 2/', $display);
        $this->assertRegExp('/Executed 2 SQL queries/', $display);
        $this->assertNotRegExp('/Executed 3 SQL queries/', $display);
        $this->assertRegExp('/All migrations completed/', $display);
    }

    public function testAbortsIfAnswerIsNo(): void
    {
        $command = $this->getCommand(
            [['Migration 1', 'Migration 2']],
            [[new MigrationResultPolyFill(true, 'Result 1'), new MigrationResultPolyFill(true, 'Result 2')]]
        );

        $tester = new CommandTester($command);
        $tester->setInputs(['n']);

        $code    = $tester->execute([]);
        $display = $tester->getDisplay();

        $this->assertSame(1, $code);
        $this->assertRegExp('/Migration 1/', $display);
        $this->assertRegExp('/Migration 2/', $display);
        $this->assertNotRegExp('/Result 1/', $display);
        $this->assertNotRegExp('/Result 2/', $display);
        $this->assertNotRegExp('/All migrations completed/', $display);
    }

    public function testDoesNotAbortIfMigrationFails(): void
    {
        $command = $this->getCommand(
            [['Migration 1', 'Migration 2']],
            [[new MigrationResultPolyFill(false, 'Result 1'), new MigrationResultPolyFill(true, 'Result 2')]]
        );

        $tester = new CommandTester($command);
        $tester->setInputs(['y']);

        $code    = $tester->execute([]);
        $display = $tester->getDisplay();

        $this->assertSame(0, $code);
        $this->assertRegExp('/Migration 1/', $display);
        $this->assertRegExp('/Migration 2/', $display);
        $this->assertRegExp('/Result 1/', $display);
        $this->assertRegExp('/Migration failed/', $display);
        $this->assertRegExp('/Result 2/', $display);
        $this->assertRegExp('/All migrations completed/', $display);
    }

    /**
     * @param array<array<string>>                  $pendingMigrations
     * @param array<array<MigrationResultPolyFill>> $migrationResults
     * @param array<array<string>>                  $runonceFiles
     * @param MigrationInstaller&MockObject         $installer
     */
    private function getCommand(
        array $pendingMigrations = [],
        array $migrationResults = [],
        array $runonceFiles = [],
        MigrationInstaller $installer = null
    ): MigrateCommand {
        $migrations = $this->createMock(MigrationCollectionPolyFill::class);

        $pendingMigrations[] = [];
        $pendingMigrations[] = [];
        $pendingMigrations[] = [];

        $migrations
            ->method('getPendingNames')
            ->willReturn(...$pendingMigrations);

        $migrationResults[] = [];

        $migrations
            ->method('run')
            ->willReturn(...$migrationResults);

        $runonceFiles[]         = [];
        $runonceFiles[]         = [];
        $duplicatedRunonceFiles = [];

        foreach ($runonceFiles as $runonceFile) {
            $duplicatedRunonceFiles[] = $runonceFile;
            $duplicatedRunonceFiles[] = $runonceFile;
        }

        $fileLocator = $this->createMock(FileLocator::class);
        $fileLocator
            ->method('locate')
            ->with('config/runonce.php', null, false)
            ->willReturn(...$duplicatedRunonceFiles);

        return new MigrateCommand(
            $migrations,
            $fileLocator,
            $this->getFixturesDir(),
            $this->createMock(ContaoFramework::class),
            $installer ?? $this->createMock(MigrationInstaller::class)
        );
    }

    private function getFixturesDir(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures';
    }

    /**
     * Mapping between root namespace of contao and the contao namespace.
     * Can map class, interface and trait.
     *
     * @param string $class The name of the class
     *
     * @return void
     */
    private static function aliasContaoClass(string $class): void
    {
        // Class.
        if (!\class_exists($class, true) && \class_exists('\\Contao\\' . $class, true)) {
            if (!\class_exists($class, false)) {
                \class_alias('\\Contao\\' . $class, $class);
            }
            return;
        }
        // Trait.
        if (!\trait_exists($class, true) && \trait_exists('\\Contao\\' . $class, true)) {
            if (!\trait_exists($class, false)) {
                \class_alias('\\Contao\\' . $class, $class);
            }
            return;
        }
        // Interface.
        if (!\interface_exists($class, true) && \interface_exists('\\Contao\\' . $class, true)) {
            if (!\interface_exists($class, false)) {
                \class_alias('\\Contao\\' . $class, $class);
            }
            return;
        }
    }
}
