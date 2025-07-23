<?php

namespace JdSupport\Services;

use JdSupport\Core\Container\Container;
use JdSupport\Core\Config\ConfigManager;

/**
 * Version Service
 *
 * @package JdSupport\Services
 */
class VersionService
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
	}

	/**
	 * Remove WordPress generator
	 */
	public function removeGenerator(): void
	{
		if (!$this->config->isEnabled('remove_wp')) {
			return;
		}

		remove_action('wp_head', 'wp_generator');
	}

	/**
	 * Return empty string for generator
	 */
	public function returnEmptyString(): string
	{
		if (!$this->config->isEnabled('remove_wp')) {
			return '';
		}

		return '';
	}

	/**
	 * Remove version from assets
	 */
	public function removeVersionFromAssets($src): string
	{
		if (!$this->config->isEnabled('remove_wp')) {
			return $src;
		}

		if (strpos($src, 'ver=' . get_bloginfo('version'))) {
			$src = remove_query_arg('ver', $src);
		}

		return $src;
	}

	/**
	 * Remove core version from admin
	 */
	public function removeCoreVersion(): void
	{
		if (!$this->config->isEnabled('remove_wp_core')) {
			return;
		}

		remove_filter('update_footer', 'core_update_footer');
	}
}
