<?php

namespace JdSupport\Core;

use JdSupport\Core\Container\Container;
use JdSupport\Core\Hooks\HookManager;
use JdSupport\Core\Config\ConfigManager;
use JdSupport\Admin\AdminManager;

/**
 * Main Plugin Class
 *
 * @package JdSupport\Core
 */
class Plugin
{
	/**
	 * Plugin version
	 */
	const VERSION = JD_SUPPORT_VERSION;

	/**
	 * Plugin name
	 */
	const PLUGIN_NAME = JD_SUPPORT_PLUGIN_NAME;

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var HookManager
	 */
	private $hookManager;

	/**
	 * @var ConfigManager
	 */
	private $configManager;

	/**
	 * @var AdminManager
	 */
	private $adminManager;

	/**
	 * Plugin constructor
	 */
	public function __construct()
	{
		$this->initContainer();
		$this->initManagers();
		$this->registerHooks();
	}

	/**
	 * Initialize dependency injection container
	 */
	private function initContainer(): void
	{
		$this->container = new Container();
		$this->container->registerServices();
		$this->container->register('plugin', self::class);
		$this->container->setInstance('plugin', $this);
	}

	/**
	 * Initialize managers
	 */
	private function initManagers(): void
	{
		$this->configManager = new ConfigManager($this->container);
		$this->hookManager = new HookManager($this->container);
		$this->adminManager = new AdminManager($this->container);
	}

	/**
	 * Register all hooks
	 */
	private function registerHooks(): void
	{
		$this->hookManager->registerCoreHooks();
		$this->adminManager->registerHooks();
	}

	/**
	 * Run the plugin
	 */
	public function run(): void
	{
		$this->hookManager->run();
	}

	/**
	 * Get container
	 */
	public function getContainer(): Container
	{
		return $this->container;
	}

	/**
	 * Get plugin version
	 */
	public function getVersion(): string
	{
		return self::VERSION;
	}

	/**
	 * Get plugin name
	 */
	public function getPluginName(): string
	{
		return self::PLUGIN_NAME;
	}
}
