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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoCommunityAlliance\Polyfills\Polyfill45\CcaContaoPolyfill45Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill46\CcaContaoPolyfill46Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill47\CcaContaoPolyfill47Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill49\CcaContaoPolyfill49Bundle;
use ContaoCommunityAlliance\Polyfills\Util\PackageUtil;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        $bundles     = [];
        $coreVersion = $this->getContaoCoreVersion();
        $loadAfter   = [ContaoCoreBundle::class];
        if (!\preg_match('/^(\d+\.)+\d+$/', $coreVersion)) {
            return [];
        }

        foreach ([
                     CcaContaoPolyfill45Bundle::class => '4.5',
                     CcaContaoPolyfill46Bundle::class => '4.6',
                     CcaContaoPolyfill47Bundle::class => '4.7',
                     CcaContaoPolyfill49Bundle::class => '4.9',
        ] as $bundleClass => $untilVersion) {
            if (!$this->acceptVersion($coreVersion, $untilVersion)) {
                continue;
            }
            $bundles[]   = BundleConfig::create($bundleClass)->setLoadAfter($loadAfter);
            $loadAfter[] = $bundleClass;
        }

        return $bundles;
    }

    /**
     * Obtain the Contao core version.
     *
     * @return string
     *
     * @internal Only protected to allow unit testing.
     *
     * @codeCoverageIgnore - This is in fact only here to mock.
     */
    protected function getContaoCoreVersion(): string
    {
        return PackageUtil::getVersion('contao/core-bundle') ?: PackageUtil::getVersion('contao/contao');
    }

    /**
     * Test if a version is accepted.
     *
     * @param string $coreVersion  The version of the installed Contao core.
     * @param string $untilVersion The most recent version that is acceptable.
     *
     * @return bool
     */
    private function acceptVersion(string $coreVersion, string $untilVersion): bool
    {
        // Version compares for the branch alias develop.
        if (false !== \strpos($coreVersion, 'x-dev')) {
            \preg_match('/(\d+\.)+\d+/', $coreVersion, $matches);

            return version_compare($matches[0], $untilVersion, '<');
        }

        return version_compare($coreVersion, $untilVersion, '<=');
    }
}
