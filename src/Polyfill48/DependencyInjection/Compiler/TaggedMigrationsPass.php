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

namespace ContaoCommunityAlliance\Polyfills\Polyfill48\DependencyInjection\Compiler;

use ContaoCommunityAlliance\Polyfills\Polyfill48\Migration\MigrationCollection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        if (!$container->has(MigrationCollection::class)) {
            return;
        }

        $definition = $container->findDefinition(MigrationCollection::class);
        $services   = [];

        foreach ($container->findTaggedServiceIds('contao.migration', true) as $serviceId => $attributes) {
            $priority                    = ($attributes[0]['priority'] ?? 0);
            $class                       = $container->getDefinition($serviceId)->getClass();
            $services[$priority][$class] = new Reference($serviceId);
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
    }
}
