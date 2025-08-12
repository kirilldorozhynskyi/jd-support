<?php

namespace JdSupport\Core\Hooks;

use JdSupport\Core\Container\Container;

/**
 * Hook Manager
 *
 * @package JdSupport\Core\Hooks
 */
class HookManager
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var array
	 */
	private $hooks = [];

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Register core hooks
	 */
	public function registerCoreHooks(): void
	{
		// Security hooks
		$this->addAction('admin_head', [$this->container->get('security'), 'hideUpdateNotice'], 1);
		$this->addAction('wp_head', [$this->container->get('security'), 'removeAdjacentPostsLink'], 10);
		$this->addFilter('user_row_actions', [$this->container->get('security'), 'removeUserDeleteAction'], 10, 1);
		$this->addFilter('bulk_actions-users', [$this->container->get('security'), 'removeUserDeleteAction'], 10, 1);

		// Version removal hooks
		$this->addAction('wp_head', [$this->container->get('version'), 'removeGenerator'], 10);
		$this->addFilter('the_generator', [$this->container->get('version'), 'returnEmptyString'], 10);
		$this->addFilter('style_loader_src', [$this->container->get('version'), 'removeVersionFromAssets'], 9999);
		$this->addFilter('script_loader_src', [$this->container->get('version'), 'removeVersionFromAssets'], 9999);
		$this->addAction('admin_menu', [$this->container->get('version'), 'removeCoreVersion'], 10);

		// Admin customization hooks
		$this->addAction('add_admin_bar_menus', [$this->container->get('admin'), 'resetAdminLogo'], 10);
		$this->addAction('admin_bar_menu', [$this->container->get('admin'), 'addCustomLogo'], 10);
		$this->addFilter('admin_footer_text', [$this->container->get('admin'), 'modifyFooterText'], 10);

		// SVG support hooks
		$this->addAction('upload_mimes', [$this->container->get('svg'), 'addSvgSupport'], 10);

		// Cache hooks
		$this->addAction('init', [$this->container->get('cache'), 'updateHtaccessRules'], 10);

		// Indexing disallow hooks
		$this->addAction('admin_init', [$this->container->get('security'), 'setupIndexingDisallow'], 10);
	}

	/**
	 * Add action hook
	 */
	public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
	{
		$this->hooks[] = [
			'type' => 'action',
			'hook' => $hook,
			'callback' => $callback,
			'priority' => $priority,
			'accepted_args' => $acceptedArgs,
		];
	}

	/**
	 * Add filter hook
	 */
	public function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
	{
		$this->hooks[] = [
			'type' => 'filter',
			'hook' => $hook,
			'callback' => $callback,
			'priority' => $priority,
			'accepted_args' => $acceptedArgs,
		];
	}

	/**
	 * Run all hooks
	 */
	public function run(): void
	{
		foreach ($this->hooks as $hook) {
			if ($hook['type'] === 'action') {
				add_action($hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args']);
			} else {
				add_filter($hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args']);
			}
		}
	}

	/**
	 * Get all registered hooks
	 */
	public function getHooks(): array
	{
		return $this->hooks;
	}
}
