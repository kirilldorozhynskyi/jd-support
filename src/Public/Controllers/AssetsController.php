<?php

namespace JdSupport\Public\Controllers;

use JdSupport\Core\Container\Container;

/**
 * Public Assets Controller
 *
 * @package JdSupport\Public\Controllers
 */
class AssetsController
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Register public assets hooks
	 */
	public function registerHooks(): void
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
	}

	/**
	 * Enqueue public styles
	 */
	public function enqueueStyles(): void
	{
		wp_enqueue_style('jd-support-public', plugin_dir_url(dirname(__DIR__)) . 'assets/css/public.css', [], $this->container->get('plugin')->getVersion());
	}

	/**
	 * Enqueue public scripts
	 */
	public function enqueueScripts(): void
	{
		wp_enqueue_script(
			'jd-support-public',
			plugin_dir_url(dirname(__DIR__)) . 'assets/js/public.js',
			['jquery'],
			$this->container->get('plugin')->getVersion(),
			true,
		);
	}
}
