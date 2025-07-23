<?php

namespace JdSupport\Services;

use JdSupport\Core\Container\Container;
use JdSupport\Core\Config\ConfigManager;

/**
 * SVG Service
 *
 * @package JdSupport\Services
 */
class SvgService
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
	 * Add SVG support to uploads
	 */
	public function addSvgSupport($file_types): array
	{
		if (!$this->config->isEnabled('svg_support')) {
			return $file_types;
		}

		$new_filetypes = [];
		$new_filetypes['svg'] = 'image/svg+xml';

		return array_merge($file_types, $new_filetypes);
	}
}
