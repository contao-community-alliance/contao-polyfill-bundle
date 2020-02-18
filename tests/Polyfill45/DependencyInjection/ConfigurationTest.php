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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\DependencyInjection;

use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(Configuration::class, new Configuration());
    }

    /**
     * Data provider for testConfigurationProcessing()
     *
     * @return array
     */
    public function configurationProcessProvider(): array
    {
        return [
            'empty' => [
                'expected' => [
                    'tagged_hooks' => true,
                    'asset' => true,
                ],
                'input'    => [
                ]
            ],
            'tagged_hooks active' => [
                'expected' => [
                    'tagged_hooks' => true,
                    'asset' => false,
                ],
                'input'    => [
                    'tagged_hooks' => true,
                    'asset' => false,
                ]
            ],
            'asset active' => [
                'expected' => [
                    'tagged_hooks' => false,
                    'asset' => true,
                ],
                'input'    => [
                    'tagged_hooks' => false,
                    'asset' => true,
                ]
            ],
            'tagged_hooks and asset active' => [
                'expected' => [
                    'tagged_hooks' => true,
                    'asset' => true,
                ],
                'input'    => [
                    'tagged_hooks' => true,
                    'asset' => true,
                ]
            ],
            'tagged_hooks disabled' => [
                'expected' => [
                    'tagged_hooks' => false,
                    'asset' => true,
                ],
                'input'    => [
                    'tagged_hooks' => false,
                    'asset' => true,
                ]
            ],
            'tagged_hooks and asset disabled' => [
                'expected' => [
                    'tagged_hooks' => false,
                    'asset' => false,
                ],
                'input'    => [
                    'tagged_hooks' => false,
                    'asset' => false,
                ]
            ],
        ];
    }

    /**
     * Test the configuration processing.
     *
     * @param array $expected The expected result.
     * @param array $input    The config input.
     *
     * @return void
     *
     * @dataProvider configurationProcessProvider
     */
    public function testConfigurationProcessing(array $expected, array $input): void
    {
        $configuration = new Configuration();
        $processor     = new Processor();

        $this->assertSame($expected, $processor->processConfiguration($configuration, [$input]));
    }
}
