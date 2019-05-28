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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Leo Feyer <github@contao.org>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler;

use ContaoCommunityAlliance\Polyfills\Polyfill45\Initialization\HookListenerRegistrar;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The compiler for register hook listeners.
 */
class RegisterHookListenersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition(HookListenerRegistrar::class)) {
            return;
        }

        $hooks = $this->getHooks($container);
        if (empty($hooks)) {
            return;
        }

        $paths   = $container->getParameter('contao.resources_paths');
        $paths[] = \dirname(__DIR__, 2) . '/Resources/tagged_hooks';
        $container->setParameter('contao.resources_paths', $paths);

        // Sort the listeners by priority
        foreach (\array_keys($hooks) as $hook) {
            \krsort($hooks[$hook]);
        }

        $container->getDefinition(HookListenerRegistrar::class)->replaceArgument(0, $hooks);
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

            $container->findDefinition($serviceId)->setPublic(true);
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
     * @return void
     *
     * @throws InvalidConfigurationException When the hook attribute is missing.
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
