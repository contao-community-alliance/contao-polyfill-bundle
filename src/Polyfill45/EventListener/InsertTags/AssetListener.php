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

namespace ContaoCommunityAlliance\Polyfills\Polyfill45\EventListener\InsertTags;

use Symfony\Component\Asset\Packages;

/**
 * The insert tag asset listener.
 */
class AssetListener
{
    /**
     * The packages.
     *
     * @var Packages
     */
    private $packages;

    /**
     * The constructor.
     *
     * @param Packages $packages The packages.
     */
    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * Replaces the "asset" insert tag.
     *
     * @param string $tag The insert tag.
     *
     * @return string|false
     */
    public function onReplaceInsertTags(string $tag)
    {
        $chunks = \explode('::', $tag);

        if ('asset' !== $chunks[0]) {
            return false;
        }

        $url = $this->packages->getUrl($chunks[1], ($chunks[2] ?? null));

        // Contao paths are relative to the <base> tag, so remove leading slashes
        return \ltrim($url, '/');
    }
}
