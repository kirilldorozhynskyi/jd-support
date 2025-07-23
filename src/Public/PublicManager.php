<?php

namespace JdSupport\Public;

use JdSupport\Core\Container\Container;
use JdSupport\Public\Controllers\AssetsController;

/**
 * Public Manager
 *
 * @package JdSupport\Public
 */
class PublicManager
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var AssetsController
	 */
	private $assetsController;

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
		$this->assetsController = new AssetsController($this->container);
	}

	/**
	 * Register public hooks
	 */
	public function registerHooks(): void
	{
		$this->assetsController->registerHooks();
	}
}
