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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill45;

use Contao\CoreBundle\Util\PackageUtil as ContaoPackageUtil;
use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\AddAssetsPackagesPass;
use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\RegisterHookListenersPass;
use ContaoCommunityAlliance\Polyfills\Polyfill45\Util\PackageUtil;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Provides polyfills from Contao version 4.5.x.
 */
final class CcaContaoPolyfill45Bundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function boot(): void
    {
        parent::boot();

        \class_alias(PackageUtil::class, ContaoPackageUtil::class);
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterHookListenersPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new AddAssetsPackagesPass());
    }
}
