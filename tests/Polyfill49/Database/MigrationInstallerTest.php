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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\Database;

use PHPUnit\Framework\TestCase;

/**
 * Test the datbase installer.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationInstaller
 */
class MigrationInstallerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::markTestSkipped('The tests for the class ' . __CLASS__ . ' are all skipped.');
    }

    /**
     * Test.
     */
    public function test__construct()
    {
        // Do nothing.
    }
}
