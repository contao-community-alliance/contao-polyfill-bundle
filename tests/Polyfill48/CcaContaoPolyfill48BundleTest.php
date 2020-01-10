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

declare(strict_types = 1);

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill48;

use ContaoCommunityAlliance\Polyfills\Polyfill48\CcaContaoPolyfill48Bundle;
use PHPUnit\Framework\TestCase;

/**
 * Test.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill48\CcaContaoPolyfill48Bundle
 */
class CcaContaoPolyfill48BundleTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CcaContaoPolyfill48Bundle::class, new CcaContaoPolyfill48Bundle());
    }
}
