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
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Leo Feyer <github@contao.org>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2019-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/contao-polyfill-bundle/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\Polyfills\Polyfill45\Asset;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The asset context.
 *
 * @covers \ContaoCommunityAlliance\Polyfills\Polyfill45\Asset\ContaoContext
 */
class ContaoContext implements ContextInterface
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The field in the page.
     *
     * @var string
     */
    private $field;

    /**
     * Determine for debug.
     *
     * @var bool
     */
    private $debug;

    /**
     * The constructor.
     *
     * @param RequestStack $requestStack The request stack.
     * @param string       $field        The field in the page.
     * @param bool         $debug        Determine for debug.
     */
    public function __construct(RequestStack $requestStack, string $field, bool $debug = false)
    {
        $this->requestStack = $requestStack;
        $this->field        = $field;
        $this->debug        = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath(): string
    {
        if ($this->debug) {
            return '';
        }

        $request = $this->requestStack->getCurrentRequest();

        if ((null === $request) || ('' === ($staticUrl = $this->getFieldValue($this->getPageModel())))) {
            return '';
        }

        $protocol = $this->isSecure() ? 'https' : 'http';
        $relative = \preg_replace('@https?://@', '', $staticUrl);

        return \sprintf('%s://%s%s', $protocol, $relative, $request->getBasePath());
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure(): bool
    {
        $page = $this->getPageModel();

        if (null !== $page) {
            return (bool) $page->loadDetails()->rootUseSSL;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return false;
        }

        return $request->isSecure();
    }

    /**
     * Returns the base path with a trailing slash if not empty.
     *
     * @return string
     */
    public function getStaticUrl(): string
    {
        if ($path = $this->getBasePath()) {
            return $path . '/';
        }

        return '';
    }

    /**
     * Get the page model.
     *
     * @return PageModel|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getPageModel(): ?\PageModel
    {
        if (isset($GLOBALS['objPage']) && $GLOBALS['objPage'] instanceof \PageModel) {
            return $GLOBALS['objPage'];
        }

        return null;
    }

    /**
     * Returns a field value from the page model.
     *
     * @param PageModel|null $page The page model.
     *
     * @return string
     */
    private function getFieldValue(?\PageModel $page): string
    {
        if (null === $page) {
            return '';
        }

        return (string) $page->{$this->field};
    }
}
