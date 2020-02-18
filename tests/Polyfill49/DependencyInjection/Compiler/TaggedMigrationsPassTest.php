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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\DependencyInjection\Compiler;

use ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Compiler\TaggedMigrationsPass;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Migration\MigrationCollectionPolyFill;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test the tagged migration pass.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Compiler\TaggedMigrationsPass
 */
class TaggedMigrationsPassTest extends TestCase
{
    private function getExpectedResult(): array
    {
        $coreVersion = Versions::getVersion('contao/core-bundle');
        $migrations  = [];
        switch (true) {
            case \version_compare($coreVersion, '4.8', '>='):
                $migrations[] = 'Contao\InstallationBundle\Database\Version480Update';
                // No break.
            case \version_compare($coreVersion, '4.7', '>='):
                $migrations[] = 'Contao\InstallationBundle\Database\Version470Update';
                // No break.
            case \version_compare($coreVersion, '4.6', '>='):
                $migrations[] = 'Contao\InstallationBundle\Database\Version460Update';
                // No break.
            case \version_compare($coreVersion, '4.5', '>='):
                $migrations[] = 'Contao\InstallationBundle\Database\Version450Update';
                // No break.
        }

        return \array_merge(
            [
                'Contao\InstallationBundle\Database\Version330Update',
                'Contao\InstallationBundle\Database\Version350Update',
                'Contao\InstallationBundle\Database\Version400Update',
                'Contao\InstallationBundle\Database\Version410Update',
                'Contao\InstallationBundle\Database\Version430Update',
                'Contao\InstallationBundle\Database\Version440Update',
                'Contao\InstallationBundle\Database\Version447Update'
            ],
            \array_reverse($migrations),
            [
                'Test\Migration1PrioPositive1',
                'Test\Migration1PrioPositive2',
                'Test\Migration1PrioPositive12',
                'Test\Migration1',
                'Test\Migration2',
                'Test\Migration12',
                'Test\MigrationPrioNegative1',
                'Test\MigrationPrioNegative2',
                'Test\MigrationPrioNegative12'
            ]
        );
    }

    public function testAddsTheMigrations(): void
    {
        $coreVersion = Versions::getVersion('contao/core-bundle');
        if ((0 === \strpos($coreVersion, 'dev-master')) || \version_compare($coreVersion, '4.9', '>=')) {
            $this->markTestSkipped('Obsolete in Contao 4.9+');
        }
        $container = new ContainerBuilder();
        $container->setDefinition(
            MigrationCollectionPolyFill::class,
            new Definition(MigrationCollectionPolyFill::class)
        );

        $migrations = [
            'Test\Migration1'               => [],
            'Test\Migration12'              => [],
            'Test\Migration2'               => [],
            'Test\MigrationPrioNegative1'   => ['priority' => -1],
            'Test\MigrationPrioNegative12'  => ['priority' => -1],
            'Test\MigrationPrioNegative2'   => ['priority' => -1],
            'Test\Migration1PrioPositive1'  => ['priority' => 1],
            'Test\Migration1PrioPositive12' => ['priority' => 1],
            'Test\Migration1PrioPositive2'  => ['priority' => 1],
        ];

        foreach ($migrations as $migration => $attributes) {
            $definition = new Definition($migration);
            $definition->addTag('contao.migration', $attributes);

            $container->setDefinition($migration, $definition);
        }

        $pass = new TaggedMigrationsPass();
        $pass->process($container);

        $migrationServices = $container->getDefinition(MigrationCollectionPolyFill::class)->getArgument(0);

        $this->assertSame($this->getExpectedResult(), array_keys($migrationServices));
    }
}
