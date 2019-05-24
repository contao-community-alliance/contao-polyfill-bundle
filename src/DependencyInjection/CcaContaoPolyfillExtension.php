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

namespace ContaoCommunityAlliance\Polyfill\DependencyInjection;

use PackageVersions\Versions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class CcaContaoPolyfillExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        try {
            $version = \ltrim(\strstr(Versions::getVersion('contao/core-bundle'), '@', true), 'v');
        } catch (\OutOfBoundsException $e) {
            $version = \ltrim(\strstr(Versions::getVersion('contao/contao'), '@', true), 'v');
        }

        $services   = [[]];
        $services[] = $this->filterServicesByVersion($version, '4.5');
        $services[] = $this->filterServicesByVersion($version, '4.6');
        $services[] = $this->filterServicesByVersion($version, '4.7');

        $services = \array_merge(...$services);
        if (!\count($services)) {
            return;
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach ($services as $file) {
            $loader->load($file);
        }
    }

    /**
     * Filter the services by the Contao version.
     *
     * @param string $version  The Contao version.
     * @param string $excepted The excepted version.
     *
     * @return array
     */
    private function filterServicesByVersion(string $version, string $excepted): array
    {
        $services      = [];
        $versionFolder = \str_replace('.', '', $excepted);
        if (\version_compare($version, $excepted, '<')) {
            foreach ((array) \scandir(__DIR__ . '/../Resources/config/' . $versionFolder) as $file) {
                $fileInfo = \pathinfo($file);
                if ('yml' !== $fileInfo['extension']) {
                    continue;
                }

                $services[] = $versionFolder . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $services;
    }
}
