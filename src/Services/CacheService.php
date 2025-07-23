<?php

namespace JdSupport\Services;

use JdSupport\Core\Container\Container;
use JdSupport\Core\Config\ConfigManager;

/**
 * Cache Service
 *
 * @package JdSupport\Services
 */
class CacheService
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
	 * Update .htaccess cache rules
	 */
	public function updateHtaccessRules(): void
	{
		if (!$this->config->isEnabled('cache')) {
			return;
		}

		$htaccess_file = ABSPATH . '.htaccess';

		if (!file_exists($htaccess_file)) {
			return;
		}

		$content = file_get_contents($htaccess_file);
		$cache_rules = $this->getCacheRules();

		// Remove existing cache rules
		$content = preg_replace('/# BEGIN Cache Rules.*# END Cache Rules/s', '', $content);

		// Add new cache rules
		$content = $cache_rules . "\n\n" . $content;

		file_put_contents($htaccess_file, $content);
	}

	/**
	 * Get cache rules for .htaccess
	 */
	private function getCacheRules(): string
	{
		return "# BEGIN Cache Rules
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg \"access plus 1 month\"
    ExpiresByType image/jpeg \"access plus 1 month\"
    ExpiresByType image/gif \"access plus 1 month\"
    ExpiresByType image/png \"access plus 1 month\"
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/pdf \"access plus 1 month\"
    ExpiresByType text/javascript \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType application/x-javascript \"access plus 1 month\"
    ExpiresByType application/x-shockwave-flash \"access plus 1 month\"
    ExpiresByType image/x-icon \"access plus 1 year\"
    ExpiresDefault \"access plus 2 days\"
</IfModule>
# END Cache Rules";
	}
}
