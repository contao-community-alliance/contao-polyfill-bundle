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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Leo Feyer <github@contao.org>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\Polyfill\Controller;

/**
 * This register the hook listeners.
 */
class RegisterHookListenersController
{
    /**
     * The hook listeners.
     *
     * @var array
     */
    private $hookListeners;

    /**
     * The constructor.
     *
     * @param array $hookListeners The hook listeners.
     */
    public function __construct(array $hookListeners)
    {
        $this->hookListeners = $hookListeners;
    }

    /**
     * Registers the hooks listeners in the global array.
     *
     * @return void
     */
    public function registerHookListeners(): void
    {
        foreach ($this->hookListeners as $hookName => $priorities) {
            if (isset($GLOBALS['TL_HOOKS'][$hookName]) && \is_array($GLOBALS['TL_HOOKS'][$hookName])) {
                if (isset($priorities[0])) {
                    $priorities[0] = \array_merge($GLOBALS['TL_HOOKS'][$hookName], $priorities[0]);
                } else {
                    $priorities[0] = $GLOBALS['TL_HOOKS'][$hookName];
                    \krsort($priorities);
                }
            }

            $GLOBALS['TL_HOOKS'][$hookName] = \array_merge(...$priorities);
        }
    }
}
