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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill48\Database;

use PHPUnit\Framework\TestCase;

/**
 * Test the datbase installer.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\Database\MigrationInstaller
 */
class MigrationInstallerTest extends TestCase
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

    /**
     * Test.
     */
    public function test__construct()
    {
        self::markTestIncomplete();
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGetCommands(): void
    {
        self::markTestIncomplete();
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testExecCommand(): void
    {
        self::markTestIncomplete();
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCompileCommands(): void
    {
        self::markTestIncomplete();
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
