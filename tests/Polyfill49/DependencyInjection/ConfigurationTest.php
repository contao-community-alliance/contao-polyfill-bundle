<?php

/**
 * This file is part of contao-community-alliance/contao-polyfill-bundle.
 *
 * (c) 2019-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/contao-polyfill-bundle
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill49\DependencyInjection;

use ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill49\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(Configuration::class, new Configuration());
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
                    'migration' => true
                ],
                'input'    => [
                ]
            ],
            'migration active' => [
                'expected' => [
                    'migration' => true
                ],
                'input'    => [
                    'migration' => true
                ]
            ],
            'migration disabled' => [
                'expected' => [
                    'migration' => false
                ],
                'input'    => [
                    'migration' => false
                ]
            ]
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

        self::assertSame($expected, $processor->processConfiguration($configuration, [$input]));
    }
}
