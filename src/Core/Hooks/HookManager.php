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
		$this->addAction('init', [$this->container->get('security'), 'setFileEditPermissions'], 1);
		$this->addAction('admin_head', [$this->container->get('security'), 'hideUpdateNotice'], 1);
		$this->addAction('wp_head', [$this->container->get('security'), 'removeAdjacentPostsLink'], 10);
		$this->addFilter('user_row_actions', [$this->container->get('security'), 'removeUserDeleteAction'], 10, 1);
		$this->addFilter('bulk_actions-users', [$this->container->get('security'), 'removeUserDeleteAction'], 10, 1);
		$this->addAction('user_register', [$this->container->get('security'), 'handleNewUserRegistration'], 10, 1);
		$this->addAction('init', [$this->container->get('security'), 'ensureSupportUserExists'], 5);

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

		// Content lock hooks
		$this->addFilter('map_meta_cap', [$this->container->get('content_lock'), 'blockPostMetaCapabilities'], 10, 4);
		$this->addFilter('user_has_cap', [$this->container->get('content_lock'), 'blockContentCapabilities'], 10, 4);
		$this->addFilter('post_row_actions', [$this->container->get('content_lock'), 'removePostRowActions'], 10, 2);
		$this->addFilter('page_row_actions', [$this->container->get('content_lock'), 'removePostRowActions'], 10, 2);
		$this->addFilter('media_row_actions', [$this->container->get('content_lock'), 'removePostRowActions'], 10, 2);
		$this->addAction('admin_bar_menu', [$this->container->get('content_lock'), 'removeAdminBarContentActions'], 999);
		$this->addAction('admin_init', [$this->container->get('content_lock'), 'blockEditorScreens'], 1);
		$this->addAction('admin_notices', [$this->container->get('content_lock'), 'showAdminNotice'], 10);
		$this->addFilter('rest_pre_dispatch', [$this->container->get('content_lock'), 'blockRestContentMutations'], 10, 3);

		// Gravity Forms fix hooks
		$this->addAction('init', [$this->container->get('gravityforms'), 'fixGravityFormsOptions'], 10);
		$this->addAction('init', [$this->container->get('gravityforms'), 'ensureEditorCanViewEntries'], 11);

		// Indexing disallow hooks
		$this->addAction('init', [$this->container->get('security'), 'setupIndexingDisallow'], 10);
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
