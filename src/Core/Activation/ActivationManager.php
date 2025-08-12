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
	 * Get default options
	 */
	public static function getDefaultOptions(): array
	{
		return self::DEFAULT_OPTIONS;
	}
}
