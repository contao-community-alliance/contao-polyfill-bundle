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
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Leo Feyer <github@contao.org>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\Polyfills\Test\Polyfill45\EventListener\InsertTags;

use ContaoCommunityAlliance\Polyfills\Polyfill45\EventListener\InsertTags\AssetListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;

/**
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\EventListener\InsertTags\AssetListener
 */
class AssetListenerTest extends TestCase
{
    public function testReplacesInsertTagsWithPackageName(): void
    {
        $packages = $this->createMock(Packages::class);
        $packages
            ->expects(self::once())
            ->method('getUrl')
            ->with('foo/bar', 'package')
            ->willReturn('/foo/bar')
        ;

        $listener = new AssetListener($packages);

        self::assertSame('foo/bar', $listener->onReplaceInsertTags('asset::foo/bar::package'));
    }

    public function testReplacesInsertTagsWithoutPackageName(): void
    {
        $packages = $this->createMock(Packages::class);
        $packages
            ->expects(self::once())
            ->method('getUrl')
            ->with('foo/bar', null)
            ->willReturn('/foo/bar')
        ;

        $listener = new AssetListener($packages);

        self::assertSame('foo/bar', $listener->onReplaceInsertTags('asset::foo/bar'));
    }

    public function testIgnoresOtherInsertTags(): void
    {
        $packages = $this->createMock(Packages::class);
        $packages
            ->expects(self::never())
            ->method('getUrl')
        ;

        $listener = new AssetListener($packages);

        self::assertFalse($listener->onReplaceInsertTags('env::pageTitle'));
    }
}
