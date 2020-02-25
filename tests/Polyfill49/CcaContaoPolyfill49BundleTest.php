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

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49;

use ContaoCommunityAlliance\Polyfills\Polyfill49\CcaContaoPolyfill49Bundle;
use ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Compiler\TaggedMigrationsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\CcaContaoPolyfill49Bundle
 */
class CcaContaoPolyfill49BundleTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CcaContaoPolyfill49Bundle::class, new CcaContaoPolyfill49Bundle());
    }

    public function testMigrationEnabled(): void
    {
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['has'])
            ->getMock();
        $container
            ->expects(self::once())
            ->method('has')
            ->willReturn(true);
        $bundle    = new CcaContaoPolyfill49Bundle();
        $bundle->setContainer($container);
        $bundle->boot();

        self::assertTrue(\class_exists(\Contao\CoreBundle\Migration\MigrationCollection::class));
        self::assertTrue(\interface_exists(\Contao\CoreBundle\Migration\MigrationInterface::class));
        self::assertTrue(\class_exists(\Contao\CoreBundle\Migration\MigrationResult::class));
        self::assertTrue(\class_exists(\Contao\CoreBundle\Migration\AbstractMigration::class));
    }

    public function testRegistersCompilerPass(): void
    {
        $bundle = new CcaContaoPolyfill49Bundle();

        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container
            ->expects($this->once())
            ->method('addCompilerPass')
            ->withConsecutive(
                [$this->isInstanceOf(TaggedMigrationsPass::class)]
            )
            ->willReturn($container);

        $bundle->build($container);
    }
}
