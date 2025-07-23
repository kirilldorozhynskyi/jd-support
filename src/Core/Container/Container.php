<?php

namespace JdSupport\Core\Container;

use JdSupport\Services\SecurityService;
use JdSupport\Services\AdminService;
use JdSupport\Services\CacheService;
use JdSupport\Services\SvgService;
use JdSupport\Services\VersionService;
use JdSupport\Core\Config\ConfigManager;

/**
 * Dependency Injection Container
 *
 * @package JdSupport\Core\Container
 */
class Container
{
	/**
	 * @var array
	 */
	private $services = [];

	/**
	 * @var array
	 */
	private $instances = [];

	/**
	 * Register all services
	 */
	public function registerServices(): void
	{
		$this->register('config', ConfigManager::class);
		$this->register('security', SecurityService::class);
		$this->register('admin', AdminService::class);
		$this->register('cache', CacheService::class);
		$this->register('svg', SvgService::class);
		$this->register('version', VersionService::class);
	}

	/**
	 * Register a service
	 */
	public function register(string $name, string $class): void
	{
		$this->services[$name] = $class;
	}

	/**
	 * Get a service instance
	 */
	public function get(string $name)
	{
		if (!isset($this->instances[$name])) {
			if (!isset($this->services[$name])) {
				throw new \Exception("Service '{$name}' not found");
			}

			$class = $this->services[$name];
			$this->instances[$name] = new $class($this);
		}

		return $this->instances[$name];
	}

	/**
	 * Check if service exists
	 */
	public function has(string $name): bool
	{
		return isset($this->services[$name]);
	}

	/**
	 * Set an instance directly
	 */
	public function setInstance(string $name, $instance): void
	{
		$this->instances[$name] = $instance;
	}

	/**
	 * Get all registered services
	 */
	public function getServices(): array
	{
		return $this->services;
	}
}
