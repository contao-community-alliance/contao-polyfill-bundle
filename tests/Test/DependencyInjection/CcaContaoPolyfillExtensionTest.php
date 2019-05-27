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

namespace ContaoCommunityAlliance\Polyfill\Test\DependencyInjection;

use ContaoCommunityAlliance\Polyfill\DependencyInjection\CcaContaoPolyfillExtension;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class CcaContaoPolyfillExtensionTest
 *
 * @covers \ContaoCommunityAlliance\Polyfill\DependencyInjection\CcaContaoPolyfillExtension
 */
class CcaContaoPolyfillExtensionTest extends TestCase
{
    public function dataProviderLoad()
    {
        return [
            ['4.5.0', ['cca.contao_polyfill.controller.register_hook_listener']]
        ];
    }

    /**
     * @dataProvider dataProviderLoad
     */
    public function testLoad($testVersion, $testServices)
    {
        try {
            $version = \ltrim(\strstr(Versions::getVersion('contao/core-bundle'), '@', true), 'v');
        } catch (\OutOfBoundsException $e) {
            $version = \ltrim(\strstr(Versions::getVersion('contao/contao'), '@', true), 'v');
        }

        $services  = (\version_compare($version, $testVersion, '<')) ? $testServices : [];
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->exactly(\count($services)))
            ->method('setDefinition')
            ->with(
                $this->callback(
                    function ($id) use ($services) {
                        return \in_array($id, $services, true);
                    }
                )
            );

        $extension = new CcaContaoPolyfillExtension();
        $extension->load([], $container);
    }
}
