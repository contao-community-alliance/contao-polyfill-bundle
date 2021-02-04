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

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\EventListener;

use Contao\CoreBundle\Migration\MigrationCollection;
use Contao\InstallationBundle\Event\InitializeApplicationEvent;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Controller\MigrationController;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;

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
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * MigrationApplicationListener constructor.
     *
     * @param MigrationController $controller The migration controller.
     * @param MigrationCollection $migrations The migration collection.
     * @param Connection          $connection The database connection.
     */
    public function __construct(
        MigrationController $controller,
        MigrationCollection $migrations,
        Connection $connection
    ) {
        $this->controller = $controller;
        $this->migrations = $migrations;
        $this->connection = $connection;
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
        if (!$this->hasDatabase() || !$this->hasPendingMigrations()) {
            return;
        }

        $this->controller->__invoke();
    }

    /**
     * Check if the database is configured.
     *
     * @return bool
     */
    private function hasDatabase(): bool
    {
        try {
            $this->connection->getDatabase();
            return true;
        } catch (ConnectionException $exception) {
            return false;
        }
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
