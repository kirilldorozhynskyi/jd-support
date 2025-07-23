<?php

namespace JdSupport\Core\Activation;

/**
 * Activation Manager
 *
 * @package JdSupport\Core\Activation
 */
class ActivationManager
{
	/**
	 * Default plugin options
	 */
	private const DEFAULT_OPTIONS = [
		'jd_permissions_mode' => 'no',
		'jd_remove_wp' => 'yes',
		'jd_remove_wp_core' => 'yes',
		'jd_remove_comm' => 'no',
		'jd_add_logo' => 'yes',
		'jd_custom_footer' => 'yes',
		'jd_svg_support' => 'yes',
		'jd_cache' => 'no',
		'jd_dark_mode' => 'no',
		'jd_hide_update' => 'no',
	];

	/**
	 * Handle plugin activation
	 */
	public static function activate(): void
	{
		self::createDefaultOptions();
		self::setupIndexingDisallow();
	}

	/**
	 * Handle plugin deactivation
	 */
	public static function deactivate(): void
	{
		// Clean up if needed
		// For now, we keep options for safety
	}

	/**
	 * Create default options
	 */
	private static function createDefaultOptions(): void
	{
		foreach (self::DEFAULT_OPTIONS as $option => $value) {
			if (get_option($option) === false) {
				add_option($option, $value);
			}
		}
	}

	/**
	 * Setup indexing disallow functionality
	 */
	private static function setupIndexingDisallow(): void
	{
		if (defined('DISALLOW_INDEXING') && DISALLOW_INDEXING === true) {
			add_action('pre_option_blog_public', '__return_zero');

			add_action('admin_init', function () {
				if (!apply_filters('roots/bedrock/disallow_indexing_admin_notice', true)) {
					return;
				}

				add_action('admin_notices', function () {
					$message = sprintf(
						__('%1$s Search engine indexing has been discouraged because the current environment is %2$s.', 'roots'),
						'<strong>justDev:</strong>',
						'<code>' . WP_ENV . '</code>',
					);
					echo "<div class='notice notice-warning'><p>{$message}</p></div>";
				});
			});
		}
	}

	/**
	 * Get default options
	 */
	public static function getDefaultOptions(): array
	{
		return self::DEFAULT_OPTIONS;
	}
}
