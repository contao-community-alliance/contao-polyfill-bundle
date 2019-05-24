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

namespace ContaoCommunityAlliance\Polyfill;

use ContaoCommunityAlliance\Polyfill\DependencyInjection\Compiler\RegisterHookListenersCompiler;
use PackageVersions\Versions;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The Contao Community Alliance Polyfill Bundle.
 */
class CcaContaoPolyfillBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        try {
            $version = \ltrim(\strstr(Versions::getVersion('contao/core-bundle'), '@', true), 'v');
        } catch (\OutOfBoundsException $e) {
            $version = \ltrim(\strstr(Versions::getVersion('contao/contao'), '@', true), 'v');
        }

        if (\version_compare($version, '4.5', '<')) {
            $container->addCompilerPass(new RegisterHookListenersCompiler(), PassConfig::TYPE_OPTIMIZE);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return null;
    }
}
