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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Leo Feyer <github@contao.org>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\DependencyInjection\Compiler;

use ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\RegisterHookListenersPass;
use ContaoCommunityAlliance\Polyfills\Polyfill45\Initialization\HookListenerRegistrar;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\DependencyInjection\Compiler\RegisterHookListenersPass
 */
class RegisterHookListenersPassTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testRegistersTheHookListeners(): void
    {
        $attributes = [
            'hook' => 'initializeSystem',
            'method' => 'onInitializeSystem',
            'priority' => 10,
            'private' => false,
        ];

        $definition = new Definition('Test\HookListener');
        $definition->addTag('contao.hook', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        self::assertSame(
            [
                'initializeSystem' => [
                    10 => [
                        ['test.hook_listener', 'onInitializeSystem'],
                    ],
                ],
            ],
            $this->getHookListenersFromDefinition($container)
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testMakesHookListenersPublic(): void
    {
        $attributes = [
            'hook' => 'initializeSystem',
            'method' => 'onInitializeSystem',
        ];

        $definition = new Definition('Test\HookListener');
        $definition->addTag('contao.hook', $attributes);
        $definition->setPublic(false);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener', $definition);

        self::assertFalse($container->findDefinition('test.hook_listener')->isPublic());

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        self::assertTrue($container->findDefinition('test.hook_listener')->isPublic());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGeneratesMethodNameIfNoneGiven(): void
    {
        $attributes = [
            'hook' => 'generatePage',
        ];

        $definition = new Definition('Test\HookListener');
        $definition->addTag('contao.hook', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        self::assertSame(
            [
                'generatePage' => [
                    0 => [
                        ['test.hook_listener', 'onGeneratePage'],
                    ],
                ],
            ],
            $this->getHookListenersFromDefinition($container)
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSetsTheDefaultPriorityIfNoPriorityGiven(): void
    {
        $attributes = [
            'hook' => 'initializeSystem',
            'method' => 'onInitializeSystem',
        ];

        $definition = new Definition('Test\HookListener');
        $definition->addTag('contao.hook', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        self::assertSame(
            [
                'initializeSystem' => [
                    0 => [
                        ['test.hook_listener', 'onInitializeSystem'],
                    ],
                ],
            ],
            $this->getHookListenersFromDefinition($container)
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testHandlesMultipleTags(): void
    {
        $definition = new Definition('Test\HookListener');

        $definition->addTag(
            'contao.hook',
            [
                'hook' => 'initializeSystem',
                'method' => 'onInitializeSystemFirst',
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook' => 'generatePage',
                'method' => 'onGeneratePage',
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook' => 'initializeSystem',
                'method' => 'onInitializeSystemSecond',
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook' => 'parseTemplate',
                'method' => 'onParseTemplate',
            ]
        );

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        self::assertSame(
            [
                'initializeSystem' => [
                    0 => [
                        ['test.hook_listener', 'onInitializeSystemFirst'],
                        ['test.hook_listener', 'onInitializeSystemSecond'],
                    ],
                ],
                'generatePage' => [
                    0 => [
                        ['test.hook_listener', 'onGeneratePage'],
                    ],
                ],
                'parseTemplate' => [
                    0 => [
                        ['test.hook_listener', 'onParseTemplate'],
                    ],
                ],
            ],
            $this->getHookListenersFromDefinition($container)
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSortsTheHooksByPriority(): void
    {
        $definitionA = new Definition('Test\HookListenerA');

        $definitionA->addTag(
            'contao.hook',
            [
                'hook' => 'initializeSystem',
                'method' => 'onInitializeSystem',
                'priority' => 10,
            ]
        );

        $definitionB = new Definition('Test\HookListenerB');

        $definitionB->addTag(
            'contao.hook',
            [
                'hook' => 'initializeSystem',
                'method' => 'onInitializeSystemLow',
                'priority' => 10,
            ]
        );

        $definitionB->addTag(
            'contao.hook',
            [
                'hook' => 'initializeSystem',
                'method' => 'onInitializeSystemHigh',
                'priority' => 100,
            ]
        );

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener.a', $definitionA);
        $container->setDefinition('test.hook_listener.b', $definitionB);

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        self::assertSame(
            [
                'initializeSystem' => [
                    100 => [
                        ['test.hook_listener.b', 'onInitializeSystemHigh'],
                    ],
                    10 => [
                        ['test.hook_listener.a', 'onInitializeSystem'],
                        ['test.hook_listener.b', 'onInitializeSystemLow'],
                    ],
                ],
            ],
            $this->getHookListenersFromDefinition($container)
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNothingIfThereIsNoRegistrar(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->method('hasDefinition')
            ->with(HookListenerRegistrar::class)
            ->willReturn(false)
        ;

        $container
            ->expects(self::never())
            ->method('findTaggedServiceIds')
        ;

        $pass = new RegisterHookListenersPass();
        $pass->process($container);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testDoesNothingIfThereAreNoHooks(): void
    {
        $container = $this->getContainerBuilder();

        $pass = new RegisterHookListenersPass();
        $pass->process($container);

        $definition = $container->getDefinition(HookListenerRegistrar::class);

        self::assertEmpty($definition->getArgument(0));
        self::assertEmpty($container->getParameter('contao.resources_paths'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testFailsIfTheHookAttributeIsMissing(): void
    {
        $definition = new Definition('Test\HookListener');
        $definition->addTag('contao.hook', ['method' => 'onInitializeSystemAfter']);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHookListenersPass();

        $this->expectException(InvalidConfigurationException::class);

        $pass->process($container);
    }

    /**
     * Test.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return array<int,array<int,string[]>>
     */
    private function getHookListenersFromDefinition(ContainerBuilder $container): array
    {
        self::assertSame(
            [\dirname(__DIR__, 4) . '/src/Polyfill45/Resources/tagged_hooks'],
            $container->getParameter('contao.resources_paths')
        );
        self::assertTrue($container->hasDefinition(HookListenerRegistrar::class));

        $definition = $container->getDefinition(HookListenerRegistrar::class);
        $argument   = $definition->getArgument(0);
        self::assertIsArray($argument);

        return $argument;
    }

    /**
     * Returns the container builder with a dummy contao.framework definition.
     *
     * @return ContainerBuilder
     */
    private function getContainerBuilder(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition(HookListenerRegistrar::class, new Definition(HookListenerRegistrar::class, [[]]));
        $container->setParameter('contao.resources_paths', []);

        return $container;
    }
}
