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
 * @author     Martin Auswöger <martin@auswoeger.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill48\Factory;

use Contao\CoreBundle\Doctrine\Schema\DcaSchemaProvider;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use ContaoCommunityAlliance\Polyfills\Polyfill48\Database\MigrationInstaller;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This service factory create a new instance of the migration installer.
 */
class ServiceFactory
{
    /**
     * The framework.
     *
     * @var ContaoFramework
     */
    private $framework;

    /**
     * The connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The schema provider.
     *
     * @var DcaSchemaProvider
     */
    private $schemaProvider;

    /**
     * The container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * The constructor.
     *
     * @param ContaoFramework         $framework      The framework.
     * @param Connection              $connection     The connection.
     * @param DcaSchemaProvider       $schemaProvider The schema provider.
     * @param ContainerInterface|null $container      The container.
     */
    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        DcaSchemaProvider $schemaProvider,
        ContainerInterface $container = null
    ) {
        $this->framework      = $framework;
        $this->connection     = $connection;
        $this->schemaProvider = $schemaProvider;

        if (null !== $container) {
            $this->container = $container;
        }
    }

    /**
     * Create the migration installer.
     *
     * @return MigrationInstaller
     */
    public function createMigrationInstaller(): MigrationInstaller
    {
        $this->framework->initialize();
        return new MigrationInstaller($this->connection, $this->schemaProvider, $this->container);
    }
}
