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
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill49;

use ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Compiler\TaggedMigrationsPass;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\AbstractMigrationPolyfill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationInterfacePolyFill;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationResultPolyFill;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Provides polyfills from Contao version 4.8.x.
 */
final class CcaContaoPolyfill49Bundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function boot(): void
    {
        parent::boot();

        if ($this->isMigrationEnabled()) {
            \class_alias(MigrationCollectionPolyFill::class, \Contao\CoreBundle\Migration\MigrationCollection::class);
            \class_alias(MigrationInterfacePolyFill::class, \Contao\CoreBundle\Migration\MigrationInterface::class);
            \class_alias(MigrationResultPolyFill::class, \Contao\CoreBundle\Migration\MigrationResult::class);
            \class_alias(AbstractMigrationPolyfill::class, \Contao\CoreBundle\Migration\AbstractMigration::class);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TaggedMigrationsPass());
    }

    /**
     * Is the migration polyfill is enabled.
     *
     * @return bool
     */
    private function isMigrationEnabled(): bool
    {
        return $this->container->has(MigrationCollectionPolyFill::class);
    }
}
