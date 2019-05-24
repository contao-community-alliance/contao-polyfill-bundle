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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Leo Feyer <github@contao.org>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\Polyfill\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The compiler for register hook listeners.
 */
class RegisterHookListenersCompiler implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $paths   = $container->getParameter('contao.resources_paths');
        $paths[] = __DIR__ . '/../../Resources/contao-polyfill/register_hook_listener';
        $container->setParameter('contao.resources_paths', $paths);

        $hooks = $this->getHooks($container);

        // Sort the listeners by priority
        foreach (\array_keys($hooks) as $hook) {
            \krsort($hooks[$hook]);
        }

        $container
            ->getDefinition('cca.contao_polyfill.controller.register_hook_listener')
            ->replaceArgument(0, $hooks);
    }

    /**
     * Returns the hook listeners.
     *
     * @param ContainerBuilder $container The container.
     *
     * @return array
     */
    private function getHooks(ContainerBuilder $container): array
    {
        $hooks      = [];
        $serviceIds = $container->findTaggedServiceIds('contao.hook');

        foreach ($serviceIds as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $this->addHookCallback($hooks, $serviceId, $attributes);
            }
        }

        return $hooks;
    }

    /**
     * Adds hook for given service and attributes.
     *
     * @param array  $hooks      The hooks.
     * @param string $serviceId  The service id.
     * @param array  $attributes The attributes.
     *
     * @throws InvalidConfigurationException
     */
    private function addHookCallback(array &$hooks, string $serviceId, array $attributes): void
    {
        if (!isset($attributes['hook'])) {
            throw new InvalidConfigurationException(
                \sprintf('Missing hook attribute in tagged hook service with service id "%s"', $serviceId)
            );
        }

        $priority = (int) ($attributes['priority'] ?? 0);

        $hooks[$attributes['hook']][$priority][] = [$serviceId, $this->getMethod($attributes)];
    }

    /**
     * Gets the method name from config or hook name.
     *
     * @param array $attributes The attributes.
     *
     * @return string
     */
    private function getMethod(array $attributes): string
    {
        if (isset($attributes['method'])) {
            return (string) $attributes['method'];
        }

        return 'on' . \ucfirst($attributes['hook']);
    }
}
