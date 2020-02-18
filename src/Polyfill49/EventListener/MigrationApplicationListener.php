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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\EventListener;

use Contao\CoreBundle\Migration\MigrationCollection;
use Contao\InstallationBundle\Event\InitializeApplicationEvent;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Controller\MigrationController;

/**
 * The listener for run the migration controller.
 */
final class MigrationApplicationListener
{
    /**
     * The migration controller.
     *
     * @var MigrationController
     */
    private $controller;

    /**
     * The migration collection.
     *
     * @var MigrationCollection
     */
    private $migrations;

    /**
     * MigrationApplicationListener constructor.
     *
     * @param MigrationController $controller The migration controller.
     * @param MigrationCollection $migrations The migration collection.
     */
    public function __construct(MigrationController $controller, MigrationCollection $migrations)
    {
        $this->controller = $controller;
        $this->migrations = $migrations;
    }

    /**
     * Invoke the listener.
     *
     * @param InitializeApplicationEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(InitializeApplicationEvent $event): void
    {
        if (!$this->hasPendingMigrations()) {
            return;
        }

        $this->controller->__invoke();
    }

    /**
     * Has the migration collection not empty pending.
     *
     * @return bool
     */
    private function hasPendingMigrations(): bool
    {
        return !empty(\iterator_to_array($this->migrations->getPendingNames()));
    }
}
