<?php

namespace JdSupport\Services;

use JdSupport\Core\Container\Container;
use JdSupport\Core\Config\ConfigManager;

/**
 * Admin Service
 *
 * @package JdSupport\Services
 */
class AdminService
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
	 * Reset admin logo
	 */
	public function resetAdminLogo(): void
	{
		if (!$this->config->isEnabled('add_logo')) {
			return;
		}

		remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
	}

	/**
	 * Add custom logo to admin bar
	 */
	public function addCustomLogo($wp_admin_bar): void
	{
		if (!$this->config->isEnabled('add_logo')) {
			return;
		}

		$wp_admin_bar->add_menu([
			'id' => 'wp-logo',
			'title' => '<img style="max-width:20px;height:auto;padding: 7px 0;" src="' . plugin_dir_url(dirname(__DIR__)) . 'images/jd_white.svg" alt="" >',
			'href' => 'https://justdev.org',
			'meta' => [
				'title' => 'justDev',
				'target' => '_blank',
			],
		]);
	}

	/**
	 * Modify footer text
	 */
	public function modifyFooterText(): string
	{
		if (!$this->config->isEnabled('custom_footer')) {
			return '';
		}

		return 'Developed by <a href="https://justdev.org" target="_blank">justDev</a>.';
	}
}
