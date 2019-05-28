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

namespace ContaoCommunityAlliance\Polyfill\TaggedHooksBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The configuration.
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cca_polyfill_tagged_hooks');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = \method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
                : $treeBuilder->root('cca_polyfill_tagged_hooks');

        $rootNode
            ->children()
                ->booleanNode('is_active')
                    ->info('For disable cca_polyfill_tagged_hooks set is_active to false.')
                    ->defaultTrue()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
