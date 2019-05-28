<?php

/**
 * This file is part of contao-community-alliance/contao-polyfill-bundle.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/contao-polyfill-bundle
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\Polyfill\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoCommunityAlliance\Polyfill\CcaContaoPolyfillBundle;
use ContaoCommunityAlliance\Polyfill\TaggedHooksBundle\CcaPolyfillTaggedHooksBundle;
use ContaoCommunityAlliance\Polyfill\VersionBundle\CcaContaoPolyfillVersion44Bundle;
use ContaoCommunityAlliance\Polyfill\VersionBundle\CcaContaoPolyfillVersion45Bundle;
use ContaoCommunityAlliance\Polyfill\VersionBundle\CcaContaoPolyfillVersion46Bundle;
use ContaoCommunityAlliance\Polyfill\VersionBundle\CcaContaoPolyfillVersion47Bundle;
use ContaoCommunityAlliance\Polyfill\VersionBundle\CcaContaoPolyfillVersionNoneBundle;
use PackageVersions\Versions;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(CcaContaoPolyfillBundle::class)
                ->setLoadAfter(
                    [
                        ContaoCoreBundle::class
                    ]
                ),
            BundleConfig::create($this->getVersionBundle())
                ->setLoadAfter(
                    [
                        CcaContaoPolyfillBundle::class
                    ]
                ),
            BundleConfig::create(CcaPolyfillTaggedHooksBundle::class)
                ->setLoadAfter(
                    [
                        CcaContaoPolyfillVersion44Bundle::class
                    ]
                )
        ];
    }

    /**
     * Get the version bundle of the installed Contao version.
     *
     * @return string
     */
    private function getVersionBundle(): string
    {
        $version = \ltrim(\strstr(Versions::getVersion('contao/core-bundle'), '@', true), 'v');

        $bundles = [
            '4.4.0'      => CcaContaoPolyfillVersion44Bundle::class,
            '4.5.0'      => CcaContaoPolyfillVersion45Bundle::class,
            '4.6.0'      => CcaContaoPolyfillVersion46Bundle::class,
            '4.7.0'      => CcaContaoPolyfillVersion47Bundle::class
        ];

        $bundles['dev-master'] = \end($bundles);

        $detectedBundleClass = null;
        foreach ($bundles as $bundleVersion => $bundleClass) {
            if (!\version_compare($bundleVersion, $version, '<=')) {
                continue;
            }

            if (!(float) $bundleVersion && $detectedBundleClass) {
                continue;
            }

            $detectedBundleClass = $bundleClass;
        }

        return $detectedBundleClass ?? CcaContaoPolyfillVersionNoneBundle::class;
    }
}
