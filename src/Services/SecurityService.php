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
}
