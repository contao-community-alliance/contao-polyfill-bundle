<?php

/**
 * This file is part of contao-community-alliance/contao-polyfill-bundle.
 *
 * (c) 2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/contao-polyfill-bundle
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Leo Feyer <github@contao.org>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\Fixtures\Util;

use PackageVersions\Versions;

class PackageUtil
{
    /**
     * Returns the version number of a package.
     */
    public static function getVersion(string $packageName): string
    {
        $version = Versions::getVersion($packageName);

        return static::parseVersion($version);
    }

    /**
     * Returns the version number as "major.minor.patch".
     */
    public static function getNormalizedVersion(string $packageName): string
    {
        $chunks = explode('.', static::getVersion($packageName));
        $chunks += [0, 0, 0];

        if (\count($chunks) > 3) {
            $chunks = \array_slice($chunks, 0, 3);
        }

        return implode('.', $chunks);
    }

    /**
     * Parses a version number.
     *
     * The method either returns a version number such as 1.0.0 (a leading "v"
     * will be stripped) or a branch name such as dev-master.
     */
    public static function parseVersion(string $version): string
    {
        return ltrim(strstr($version, '@', true), 'v');
    }
}
