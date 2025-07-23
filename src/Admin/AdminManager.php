<?php

namespace JdSupport\Admin;

use JdSupport\Core\Container\Container;
use JdSupport\Admin\Controllers\AdminController;

/**
 * Admin Manager
 *
 * @package JdSupport\Admin
 */
class AdminManager
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var AdminController
	 */
	private $adminController;

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->initControllers();
	}

	/**
	 * Initialize controllers
	 */
	private function initControllers(): void
	{
		$this->adminController = new AdminController($this->container);
	}

	/**
	 * Register admin hooks
	 */
	public function registerHooks(): void
	{
		$this->adminController->registerHooks();
	}
}
