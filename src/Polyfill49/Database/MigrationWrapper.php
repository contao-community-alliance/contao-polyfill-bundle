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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Database;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\InstallationBundle\Database\AbstractVersionUpdate;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration wrapper is for the Contao version updater.
 */
final class MigrationWrapper implements MigrationInterface
{
    /**
     * The Contao version updater.
     *
     * @var AbstractVersionUpdate
     */
    private $updater;

    /**
     * MigrationWrapper constructor.
     *
     * @param ContainerInterface $container  The service container.
     * @param Connection         $connection The database connection.
     * @param string             $class      The class of the Contao version updater.
     */
    public function __construct(ContainerInterface $container, Connection $connection, string $class)
    {
        $this->updater = new $class($connection);
        $this->updater->setContainer($container);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return \get_class($this->updater);
    }

    /**
     * {@inheritDoc}
     */
    public function shouldRun(): bool
    {
        return $this->updater->shouldBeRun();
    }

    /**
     * {@inheritDoc}
     */
    public function run(): MigrationResult
    {
        $successful = true;
        try {
            $this->updater->run();
        } catch (\Exception $exception) {
            $successful = false;
        }

        if (!$this->updater->hasMessage()) {
            $message = $this->getName() . ' ' . ($successful ? 'executed successfully' : 'execution failed');
        } else {
            $message = $this->updater->getMessage();
        }

        return new MigrationResult($successful, $message);
    }
}
