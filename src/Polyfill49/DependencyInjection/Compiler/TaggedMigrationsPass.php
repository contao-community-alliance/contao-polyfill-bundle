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
 * @author     Martin Auswöger <martin@auswoeger.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Compiler;

use Contao\CoreBundle\Migration\MigrationCollection;
use Contao\InstallationBundle\Database\AbstractVersionUpdate;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Database\MigrationWrapper;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * The compiler for register migration listeners.
 */
final class TaggedMigrationsPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container The container.
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(MigrationCollectionPolyFill::class)) {
            return;
        }

        $definition = $container->findDefinition(MigrationCollectionPolyFill::class);
        $definition->setPublic(true);

        $services = [];
        foreach ($container->findTaggedServiceIds('contao.migration', true) as $serviceId => $attributes) {
            $priority                    = ($attributes[0]['priority'] ?? 0);
            $class                       = $container->getDefinition($serviceId)->getClass();
            $services[$priority][$class] = new Reference($serviceId);
        }

        foreach ($this->taggedContaoUpdateServices($container) as $updateServiceId) {
            $services[99][$updateServiceId] = new Reference($updateServiceId);
        }

        foreach (\array_keys($services) as $priority) {
            // Order by class name ascending
            \ksort($services[$priority], SORT_NATURAL);
        }

        if ($services) {
            // Order by priority descending
            \krsort($services);
            $services = \array_merge(...$services);
        }

        $definition->addArgument($services);

        if (!$container->has(MigrationCollection::class)) {
            $container->setAlias(MigrationCollection::class, MigrationCollectionPolyFill::class);
        }
    }

    /**
     * Tagged the contao update servcies.
     *
     * @param ContainerBuilder $container The container.
     *
     * @return array
     */
    private function taggedContaoUpdateServices(ContainerBuilder $container): array
    {
        $resourcePath = \dirname((new \ReflectionClass(AbstractVersionUpdate::class))->getFileName());

        $finder = Finder::create()
            ->files()
            ->name('Version*Update.php')
            ->sortByName()
            ->in($resourcePath);

        $services = [];
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class = 'Contao\InstallationBundle\Database\\' . $file->getBasename('.php');
            if (false === (new \ReflectionClass($class))->isSubclassOf(AbstractVersionUpdate::class)) {
                continue;
            }

            $definition = new Definition();
            $definition
                ->setClass(MigrationWrapper::class)
                ->setPrivate(true)
                ->setPublic(true)
                ->setArguments(
                    [
                        new Reference('service_container'),
                        new Reference('database_connection'),
                        $class
                    ]
                )
                ->setTags(
                    [
                        'contao.migration' => [
                            [
                                'priority' => 99
                            ]
                        ]
                    ]
                );

            $container->setDefinition($class, $definition);
            $services[] = $class;
        }

        return $services;
    }
}
