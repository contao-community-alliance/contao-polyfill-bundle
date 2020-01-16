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

namespace ContaoCommunityAlliance\Polyfills\Polyfill48\Hook;

use ContaoCommunityAlliance\Polyfills\Polyfill48\Controller\MigrationController;

/**
 * The hook for run the migrations.
 */
final class RunMigrationsHook
{
    /**
     * @var MigrationController
     */
    private $controller;

    public function __construct(MigrationController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Invoke the hook.
     *
     * @return array
     */
    public function __invoke(array $sqlCommands): array
    {
        $this->controller->__invoke();

        return $sqlCommands;
    }
}
