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

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Util;

use ContaoCommunityAlliance\Polyfills\Util\PackageUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\Util\PackageUtil
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\Util\PackageUtil
 */
class PackageUtilTest extends TestCase
{
    public function testGetVersion(): void
    {
        $version1 = PackageUtil::getVersion('contao/core-bundle');
        self::assertNotEmpty($version1);

        $version2 = null;
        try {
            $version2 = PackageUtil::getVersion('foo/bar');
            self::assertSame($version2, 'found');
        } catch (\OutOfBoundsException $exception) {
            self::assertNull($version2);
        }
    }

    public function testGetNormalizedVersion(): void
    {
        $version1 = PackageUtil::getNormalizedVersion('contao/core-bundle');
        self::assertNotEmpty($version1);

        $version2 = null;
        try {
            $version2 = PackageUtil::getNormalizedVersion('foo/bar');
            self::assertSame($version2, 'found');
        } catch (\OutOfBoundsException $exception) {
            self::assertNull($version2);
        }
    }

    public function testGetContaoVersion(): void
    {
        $version1 = PackageUtil::getContaoVersion();
        self::assertNotEmpty($version1);
    }
}
