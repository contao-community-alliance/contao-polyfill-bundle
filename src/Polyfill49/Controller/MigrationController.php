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

namespace ContaoCommunityAlliance\Polyfills\Polyfill49\Controller;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Migration\MigrationCollection;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\Environment;
use Contao\Validator;
use ContaoCommunityAlliance\Polyfills\Polyfill49\Installation\InstallTool;
use Doctrine\DBAL\DBALException;
use Patchwork\Utf8;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * The migration controller.
 */
class MigrationController
{
    use ContainerAwareTrait;

    /**
     * The twig context.
     *
     * @var array
     */
    private $context = [
        'has_admin' => false,
        'hide_admin' => false,
        'sql_message' => '',
    ];

    /**
     * Invoke the controller.
     *
     * @return Response|null
     *
     * @throws ResponseException Throws the response.
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __invoke(): ?Response
    {
        if ($this->container->has('contao.framework')) {
            $this->container->get('contao.framework')->initialize();
        }

        if (!$this->shouldAbstain() || !($installTool = $this->container->get(InstallTool::class))) {
            return null;
        }

        if (!$this->canLoginInstallTool()
            || !$this->container->get('contao.install_tool_user')->isAuthenticated()
            || !$installTool->canConnectToDatabase($this->getContainerParameter('database_name'))
        ) {
            return null;
        }

        $this->warmUpSymfonyCache();

        if ($installTool->hasOldDatabase()
            || $this->installToolHasConfigurationError()
            || !$this->runDatabaseUpdates()
        ) {
            return null;
        }

        if (null !== ($response = $this->adjustDatabaseTables())) {
            throw new ResponseException($response);
        }

        if (null !== ($response = $this->importExampleWebsite())) {
            throw new ResponseException($response);
        }

        if (null !== ($response = $this->createAdminUser())) {
            throw new ResponseException($response);
        }

        throw new ResponseException($this->render('main.html.twig', $this->context));
    }

    /**
     * Renders a form to adjust the database tables.
     *
     * @return RedirectResponse|null
     *
     * @throws \RuntimeException Throws if the request stack did not contain a request.
     */
    private function adjustDatabaseTables(): ?RedirectResponse
    {
        $this->container->get(InstallTool::class)->handleRunOnce();

        $installer = $this->container->get('contao.installer');

        $this->context['sql_form'] = $installer->getCommands();

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        if ('tl_database_update' !== $request->request->get('FORM_SUBMIT')) {
            return null;
        }

        $sql = $request->request->get('sql');

        if (!empty($sql) && \is_array($sql)) {
            foreach ($sql as $hash) {
                $installer->execCommand($hash);
            }
        }

        return $this->getRedirectResponse();
    }

    /**
     * Renders a form to import the example website.
     *
     * @return RedirectResponse|null
     *
     * @throws \RuntimeException Throws if the request stack did not contain a request.
     */
    private function importExampleWebsite(): ?RedirectResponse
    {
        $installTool = $this->container->get(InstallTool::class);
        $templates   = $installTool->getTemplates();

        $this->context['templates'] = $templates;

        if ($installTool->getConfig('exampleWebsite')) {
            $this->context['import_date'] = date('Y-m-d H:i', $installTool->getConfig('exampleWebsite'));
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        if ('tl_template_import' !== $request->request->get('FORM_SUBMIT')) {
            return null;
        }

        $template = $request->request->get('template');

        if ('' === $template || !\in_array($template, $templates, true)) {
            $this->context['import_error'] = $this->trans('import_empty_source');

            return null;
        }

        try {
            $installTool->importTemplate($template, '1' === $request->request->get('preserve'));
        } catch (DBALException $e) {
            $installTool->persistConfig('exampleWebsite', null);
            $installTool->logException($e);

            $this->context['import_error'] = $this->trans('import_exception');

            return null;
        }

        $installTool->persistConfig('exampleWebsite', \time());

        return $this->getRedirectResponse();
    }

    /**
     * Creates an admin user.
     *
     * @return RedirectResponse|null
     *
     * @throws \RuntimeException Throws if the request stack did not contain a request.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function createAdminUser(): ?RedirectResponse
    {
        $installTool = $this->container->get(InstallTool::class);

        if (!$installTool->hasTable('tl_user')) {
            $this->context['hide_admin'] = true;

            return null;
        }

        if ($installTool->hasAdminUser()) {
            $this->context['has_admin'] = true;

            return null;
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        if ('tl_admin' !== $request->request->get('FORM_SUBMIT')) {
            return null;
        }

        $username     = $request->request->get('username');
        $name         = $request->request->get('name');
        $email        = $request->request->get('email');
        $password     = $request->request->get('password');
        $confirmation = $request->request->get('confirmation');

        $this->context['admin_username_value']     = $username;
        $this->context['admin_name_value']         = $name;
        $this->context['admin_email_value']        = $email;
        $this->context['admin_password_value']     = $password;
        $this->context['admin_confirmation_value'] = $confirmation;

        // All fields are mandatory
        if ('' === $username || '' === $name || '' === $email || '' === $password) {
            $this->context['admin_error'] = $this->trans('admin_error');

            return null;
        }

        // Do not allow special characters in usernames
        if (!Validator::isExtendedAlphanumeric($username)) {
            $this->context['admin_username_error'] = $this->trans('admin_error_extnd');

            return null;
        }

        // The username must not contain whitespace characters (see #4006)
        if (false !== strpos($username, ' ')) {
            $this->context['admin_username_error'] = $this->trans('admin_error_no_space');

            return null;
        }

        // Validate the e-mail address (see #6003)
        if (!Validator::isEmail($email)) {
            $this->context['admin_email_error'] = $this->trans('admin_error_email');

            return null;
        }

        // The passwords do not match
        if ($password !== $confirmation) {
            $this->context['admin_password_error'] = $this->trans('admin_error_password_match');

            return null;
        }

        $minlength = $installTool->getConfig('minPasswordLength');

        // The password is too short
        if (Utf8::strlen($password) < $minlength) {
            $this->context['admin_password_error'] = \sprintf($this->trans('password_too_short'), $minlength);

            return null;
        }

        // Password and username are the same
        if ($password === $username) {
            $this->context['admin_password_error'] = \sprintf($this->trans('admin_error_password_user'), $minlength);

            return null;
        }

        $installTool->persistConfig('adminEmail', $email);

        $installTool->persistAdminUser(
            $username,
            $name,
            $email,
            $password,
            $request->getLocale()
        );

        return $this->getRedirectResponse();
    }

    /**
     * Run the database updates.
     *
     * @return bool
     */
    private function runDatabaseUpdates(): bool
    {
        $messages = [];

        /** @var MigrationResult $migrationResult */
        foreach ($this->container->get(MigrationCollection::class)->run() as $migrationResult) {
            $messages[] = $migrationResult->getMessage();
        }
        if (!\count($messages)) {
            return false;
        }

        $this->context['sql_message'] = \implode('<br>', \array_map('htmlspecialchars', $messages));

        return true;
    }

    /**
     * Renders a template.
     *
     * @param string $name    The template name.
     * @param array  $context The context.
     *
     * @return Response
     */
    private function render(string $name, array $context = []): Response
    {
        return new Response(
            $this->container->get('twig')->render(
                '@ContaoInstallation/' . $name,
                $this->addDefaultsToContext($context)
            )
        );
    }

    /**
     * Adds the default values to the context.
     *
     * @param array $context The context.
     *
     * @return array
     *
     * @throws \RuntimeException Throws if the request stack did not contain a request.
     */
    private function addDefaultsToContext(array $context): array
    {
        $context = \array_merge($this->context, $context);

        if (!isset($context['request_token'])) {
            $context['request_token'] = $this->getRequestToken();
        }

        if (!isset($context['language'])) {
            $context['language'] = $this->container->get('translator')->getLocale();
        }

        if (!isset($context['ua'])) {
            $context['ua'] = $this->getUserAgentString();
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if (!isset($context['path'])) {
            if (null === $request) {
                throw new \RuntimeException('The request stack did not contain a request');
            }

            $context['path'] = $request->getBasePath();
            $context['host'] = $request->getHost();
        }

        return $context;
    }

    /**
     * Returns the request token.
     *
     * @return string
     */
    private function getRequestToken(): string
    {
        $tokenName = $this->getContainerParameter('contao.csrf_token_name');

        if (null === $tokenName) {
            return '';
        }

        if ($this->container->has('contao.csrf.token_manager')) {
            $tokenManager = $this->container->get('contao.csrf.token_manager');
        } else {
            $tokenManager = $this->container->get('security.csrf.token_manager');
        }

        return $tokenManager->getToken($tokenName)->getValue();
    }

    /**
     * Returns the user agent string.
     *
     * @return string
     */
    private function getUserAgentString(): string
    {
        if (!$this->container->has('contao.framework') || !$this->container->get('contao.framework')->isInitialized()) {
            return '';
        }

        return Environment::get('agent')->class;
    }

    /**
     * Translate a key.
     *
     * @param string $key The translation key.
     *
     * @return string
     */
    private function trans($key)
    {
        return $this->container->get('translator')->trans($key);
    }

    /**
     * Returns a redirect response to reload the page.
     *
     * @return RedirectResponse
     *
     * @throws \RuntimeException Throws if the request stack did not contain a request.
     */
    private function getRedirectResponse(): RedirectResponse
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('The request stack did not contain a request');
        }

        return new RedirectResponse($request->getRequestUri());
    }

    /**
     * Warms up the Symfony cache.
     *
     * The method runs the optional cache warmers, because the cache will only
     * have the non-optional stuff at this time.
     *
     * @return void
     */
    private function warmUpSymfonyCache(): void
    {
        $cacheDir = $this->getContainerParameter('kernel.cache_dir');

        if (\file_exists($cacheDir . '/contao/config/config.php')) {
            return;
        }

        $warmer = $this->container->get('cache_warmer');

        if (!$this->getContainerParameter('kernel.debug')) {
            $warmer->enableOptionalWarmers();
        }

        $warmer->warmUp($cacheDir);

        if (\function_exists('opcache_reset')) {
            \opcache_reset();
        }

        if (\function_exists('apc_clear_cache') && !ini_get('apc.stat')) {
            \apc_clear_cache();
        }
    }

    /**
     * Install tool has configuration error.
     *
     * @return bool
     */
    private function installToolHasConfigurationError(): bool
    {
        $reflection = new \ReflectionClass(InstallTool::class);
        if (!$reflection->hasMethod('hasConfigurationError')) {
            return false;
        }

        $installTool = $this->container->get(InstallTool::class);
        return $installTool->hasConfigurationError($this->context);
    }

    /**
     * Returns a parameter from the container.
     *
     * @param string $name The parameter name.
     *
     * @return mixed
     */
    private function getContainerParameter($name): ?string
    {
        if ($this->container->hasParameter($name)) {
            return $this->container->getParameter($name);
        }

        return null;
    }

    /**
     * Can login in the install tool.
     *
     * @return bool
     */
    private function canLoginInstallTool(): bool
    {
        $installTool = $this->container->get(InstallTool::class);

        return !$installTool->isLocked()
               && $installTool->canWriteFiles()
               && !$installTool->shouldAcceptLicense()
               && $installTool->getConfig('installPassword');
    }

    /**
     * Should abstain.
     *
     * @return bool
     */
    private function shouldAbstain(): bool
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        if ('contao_install' !== $request->attributes->get('_route')) {
            return false;
        }

        $pending = \iterator_to_array($this->container->get(MigrationCollection::class)->getPendingNames());

        return !empty($pending);
    }
}
