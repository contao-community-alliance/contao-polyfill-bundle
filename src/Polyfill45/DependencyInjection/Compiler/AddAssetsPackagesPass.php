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
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Leo Feyer <github@contao.org>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler;

use ContaoCommunityAlliance\Polyfills\Polyfill45\Util\PackageUtil;
use PackageVersions\Versions;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The add assets compiler pass.
 */
class AddAssetsPackagesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('cca.polyfill_45_event_listener.insert_tags_asset')
            || !$container->hasDefinition('assets.packages')
        ) {
            return;
        }

        $this->addBundles($container);
        $this->addComponents($container);
    }

    /**
     * Adds every bundle with a public folder as assets package.
     *
     * @param ContainerBuilder $container The container.
     *
     * @return void
     */
    private function addBundles(ContainerBuilder $container): void
    {
        $packages = $container->getDefinition('assets.packages');
        $context  = new Reference('contao.assets.plugins_context');

        if ($container->hasDefinition('assets._version_default')) {
            $version = new Reference('assets._version_default');
        } else {
            $version = new Reference('assets.empty_version_strategy');
        }

        $bundles = $container->getParameter('kernel.bundles');
        $meta    = $container->getParameter('kernel.bundles_metadata');

        foreach (\array_keys($bundles) as $name) {
            if (!\is_dir($meta[$name]['path'] . '/Resources/public')) {
                continue;
            }

            $packageVersion = $version;
            $packageName    = $this->getBundlePackageName($name);
            $serviceId      = 'assets._package_' . $packageName;
            $basePath       = 'bundles/' . \preg_replace('/bundle$/', '', \strtolower($name));

            if (\is_file($meta[$name]['path'] . '/Resources/public/manifest.json')) {
                $def = new ChildDefinition('assets.json_manifest_version_strategy');
                $def->replaceArgument(0, $meta[$name]['path'] . '/Resources/public/manifest.json');

                $container->setDefinition('assets._version_' . $packageName, $def);
                $packageVersion = new Reference('assets._version_' . $packageName);
            }

            $container->setDefinition($serviceId, $this->createPackageDefinition($basePath, $packageVersion, $context));
            $packages->addMethodCall('addPackage', [$packageName, new Reference($serviceId)]);
        }
    }

    /**
     * Adds the Contao components as assets packages.
     *
     * @param ContainerBuilder $container The container.
     *
     * @return void
     */
    private function addComponents(ContainerBuilder $container): void
    {
        $packages = $container->getDefinition('assets.packages');
        $context  = new Reference('contao.assets.plugins_context');

        foreach (Versions::VERSIONS as $name => $version) {
            if (0 !== \strncmp('contao-components/', $name, 18)) {
                continue;
            }

            $serviceId = 'assets._package_' . $name;
            $basePath  = 'assets/' . \substr($name, 18);
            $version   = $this->createVersionStrategy($container, $version, $name);

            $container->setDefinition($serviceId, $this->createPackageDefinition($basePath, $version, $context));
            $packages->addMethodCall('addPackage', [$name, new Reference($serviceId)]);
        }
    }

    /**
     * Create the package definition.
     *
     * @param string    $basePath The base path.
     * @param Reference $version  The version.
     * @param Reference $context  The context.
     *
     * @return Definition
     */
    private function createPackageDefinition(string $basePath, Reference $version, Reference $context): Definition
    {
        $package = new ChildDefinition('assets.path_package');
        $package
            ->setPublic(false)
            ->replaceArgument(0, $basePath)
            ->replaceArgument(1, $version)
            ->replaceArgument(2, $context);

        return $package;
    }

    /**
     * Create the version strategy.
     *
     * @param ContainerBuilder $container The container.
     * @param string           $version   The version.
     * @param string           $name      The asset name.
     *
     * @return Reference
     */
    private function createVersionStrategy(ContainerBuilder $container, string $version, string $name): Reference
    {
        $def = new ChildDefinition('assets.static_version_strategy');
        $def->replaceArgument(0, PackageUtil::parseVersion($version));
        $def->replaceArgument(1, '%%s?v=%%s');

        $container->setDefinition('assets._version_' . $name, $def);

        return new Reference('assets._version_' . $name);
    }

    /**
     * Returns a bundle package name emulating what a bundle extension would look like.
     *
     * @param string $className The class name.
     *
     * @return string
     */
    private function getBundlePackageName(string $className): string
    {
        if ('Bundle' === \substr($className, -6)) {
            $className = \substr($className, 0, -6);
        }

        return Container::underscore($className);
    }
}
