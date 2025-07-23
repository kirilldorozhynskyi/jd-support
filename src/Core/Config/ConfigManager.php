<?php

namespace JdSupport\Core\Config;

use JdSupport\Core\Container\Container;

/**
 * Configuration Manager
 *
 * @package JdSupport\Core\Config
 */
class ConfigManager
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var array
	 */
	private $config = [];

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->loadConfig();
	}

	/**
	 * Load configuration
	 */
	private function loadConfig(): void
	{
		$this->config = [
			'permissions_mode' => get_option('jd_permissions_mode', 'no'),
			'remove_wp' => get_option('jd_remove_wp', 'yes'),
			'remove_wp_core' => get_option('jd_remove_wp_core', 'yes'),
			'remove_comm' => get_option('jd_remove_comm', 'no'),
			'add_logo' => get_option('jd_add_logo', 'yes'),
			'custom_footer' => get_option('jd_custom_footer', 'yes'),
			'svg_support' => get_option('jd_svg_support', 'yes'),
			'cache' => get_option('jd_cache', 'no'),
			'dark_mode' => get_option('jd_dark_mode', 'no'),
			'hide_update' => get_option('jd_hide_update', 'no'),
		];
	}

	/**
	 * Get configuration value
	 */
	public function get(string $key, $default = null)
	{
		return $this->config[$key] ?? $default;
	}

	/**
	 * Set configuration value
	 */
	public function set(string $key, $value): void
	{
		$this->config[$key] = $value;
		update_option('jd_' . $key, $value);
	}

	/**
	 * Get all configuration
	 */
	public function getAll(): array
	{
		return $this->config;
	}

	/**
	 * Check if option is enabled
	 */
	public function isEnabled(string $key): bool
	{
		return $this->get($key) === 'yes';
	}

	/**
	 * Check if option is disabled
	 */
	public function isDisabled(string $key): bool
	{
		return $this->get($key) === 'no';
	}

	/**
	 * Reload configuration
	 */
	public function reload(): void
	{
		$this->loadConfig();
	}
}
