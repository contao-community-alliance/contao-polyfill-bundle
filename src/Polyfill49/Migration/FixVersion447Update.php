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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Migration;

use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This fix the version update.
 */
class FixVersion447Update extends AbstractMigration
{
    /**
     * The connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The constructor.
     *
     * @param Connection $connection The connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Contao 4.4.7 Update fix for create pid_email index';
    }

    /**
     * {@inheritDoc}
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_newsletter_recipients'])) {
            return false;
        }

        $columns = $schemaManager->listTableIndexes('tl_newsletter_recipients');

        return !isset($columns['pid_email']);
    }

    /**
     * {@inheritDoc}
     */
    public function run(): MigrationResult
    {
        $this->connection->query('CREATE UNIQUE INDEX pid_email ON tl_newsletter_recipients (pid, email)');

        return $this->createResult(true);
    }
}
