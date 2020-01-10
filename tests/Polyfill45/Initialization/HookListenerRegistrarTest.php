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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Leo Feyer <github@contao.org>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\Initialization;

use ContaoCommunityAlliance\Polyfills\Polyfill45\Initialization\HookListenerRegistrar;
use PHPUnit\Framework\TestCase;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\Initialization\HookListenerRegistrar
 */
class HookListenerRegistrarTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testRegistersTheHookServices(): void
    {
        $GLOBALS['TL_HOOKS'] = [
            'getPageLayout'    => [
                [
                    'test.listener.c',
                    'onGetPageLayout'
                ],
            ],
            'generatePage'     => [
                [
                    'test.listener.c',
                    'onGeneratePage'
                ],
            ],
            'parseTemplate'    => [
                [
                    'test.listener.c',
                    'onParseTemplate'
                ],
            ],
            'isVisibleElement' => [
                [
                    'test.listener.c',
                    'onIsVisibleElement'
                ],
            ],
        ];

        $listeners = [
            'getPageLayout'    => [
                10 => [
                    [
                        'test.listener.a',
                        'onGetPageLayout'
                    ],
                ],
                0  => [
                    [
                        'test.listener.b',
                        'onGetPageLayout'
                    ],
                ],
            ],
            'generatePage'     => [
                0   => [
                    [
                        'test.listener.b',
                        'onGeneratePage'
                    ],
                ],
                -10 => [
                    [
                        'test.listener.a',
                        'onGeneratePage'
                    ],
                ],
            ],
            'parseTemplate'    => [
                10 => [
                    [
                        'test.listener.a',
                        'onParseTemplate'
                    ],
                ],
            ],
            'isVisibleElement' => [
                -10 => [
                    [
                        'test.listener.a',
                        'onIsVisibleElement'
                    ],
                ],
            ],
        ];

        $registrar = new HookListenerRegistrar($listeners);
        $registrar->registerHookListeners();

        $this->assertArrayHasKey('TL_HOOKS', $GLOBALS);
        $this->assertArrayHasKey('getPageLayout', $GLOBALS['TL_HOOKS']);
        $this->assertArrayHasKey('generatePage', $GLOBALS['TL_HOOKS']);
        $this->assertArrayHasKey('parseTemplate', $GLOBALS['TL_HOOKS']);
        $this->assertArrayHasKey('isVisibleElement', $GLOBALS['TL_HOOKS']);

        // Test hooks with high priority are added before low and legacy hooks
        // Test legacy hooks are added before hooks with priority 0
        $this->assertSame(
            [
                [
                    'test.listener.a',
                    'onGetPageLayout'
                ],
                [
                    'test.listener.c',
                    'onGetPageLayout'
                ],
                [
                    'test.listener.b',
                    'onGetPageLayout'
                ],
            ],
            $GLOBALS['TL_HOOKS']['getPageLayout']
        );

        // Test hooks with negative priority are added at the end
        $this->assertSame(
            [
                [
                    'test.listener.c',
                    'onGeneratePage'
                ],
                [
                    'test.listener.b',
                    'onGeneratePage'
                ],
                [
                    'test.listener.a',
                    'onGeneratePage'
                ],
            ],
            $GLOBALS['TL_HOOKS']['generatePage']
        );

        // Test legacy hooks are kept when adding only hook listeners with high priority.
        $this->assertSame(
            [
                [
                    'test.listener.a',
                    'onParseTemplate'
                ],
                [
                    'test.listener.c',
                    'onParseTemplate'
                ],
            ],
            $GLOBALS['TL_HOOKS']['parseTemplate']
        );

        // Test legacy hooks are kept when adding only hook listeners with low priority.
        $this->assertSame(
            [
                [
                    'test.listener.c',
                    'onIsVisibleElement'
                ],
                [
                    'test.listener.a',
                    'onIsVisibleElement'
                ],
            ],
            $GLOBALS['TL_HOOKS']['isVisibleElement']
        );
    }
}
