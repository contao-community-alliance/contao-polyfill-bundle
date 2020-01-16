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

namespace ContaoCommunityAlliance\Polyfills\Polyfill48\Controller;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Migration\MigrationCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * The migration controller.
 */
class MigrationController
{
    /**
     * The collection of migrations.
     *
     * @var MigrationCollection
     */
    private $migrations;

    /**
     * The constructor.
     *
     * @param MigrationCollection $migrations The collection of migrations.
     */
    public function __construct(MigrationCollection $migrations)
    {
        $this->migrations = $migrations;
    }

    /**
     * Invoke the controller.
     *
     * @return void
     *
     * @throws ResponseException Throws a response with the processed migrations.
     */
    public function __invoke(): void
    {
        if (!$this->shouldAbstain()) {
            throw new ResponseException($this->process());
        }
    }

    /**
     * Process the controller.
     *
     * @return Response
     */
    private function process(): Response
    {
        $messages = [];

        foreach ($this->migrations->run() as $migrationResult) {
            $messages[] = $migrationResult->getMessage();
        }

        return new Response(\implode('<br>', $messages));
    }

    /**
     * Should abstain.
     *
     * @return bool
     */
    private function shouldAbstain(): bool
    {
        if ('cli' === PHP_SAPI) {
            return true;
        }

        $pending   = \iterator_to_array($this->migrations->getPendingNames());
        $pending[] = 'muh';

        return empty($pending);
    }
}
