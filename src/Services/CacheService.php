<?php

namespace JdSupport\Services;

use JdSupport\Core\Container\Container;
use JdSupport\Cache;

class CacheService
{
	public function __construct(Container $container) {}

	public function clearContentCache(): void
	{
		Cache::clear();
	}

	public function clearOptionCache(string $option): void
	{
		if (strpos($option, '_transient_') === 0 || strpos($option, '_site_transient_') === 0) {
			return;
		}
		Cache::clear();
	}

	public static function stripLegacyHtaccessRules(string $content): string
	{
		return preg_replace('/^# BEGIN Cache Rules\r?\n.*?^# END Cache Rules[^\S\r\n]*(?:\r?\n|$)/ms', '', $content);
	}

	/**
	 * Remove only the old generated block, once, after an administrator visits.
	 * Existing backups are never overwritten. Failed cleanup remains retryable.
	 */
	public function removeLegacyHtaccessRules(): void
	{
		if (!current_user_can('manage_options') || get_option('jd_support_htaccess_cache_removed_22')) {
			return;
		}
		$file = ABSPATH . '.htaccess';
		if (file_exists($file)) {
			$content = @file_get_contents($file);
			if ($content === false) {
				$this->cleanupNotice();
				return;
			}
			$cleaned = self::stripLegacyHtaccessRules($content);
			if ($cleaned !== $content) {
				// Store the backup in WP options, never as a publicly downloadable file.
				$backup = 'jd_support_legacy_htaccess_backup_22';
				if (get_option($backup, null) === null && !add_option($backup, $content, '', false)) {
					$this->cleanupNotice();
					return;
				}
				if (get_option($backup) !== $content || @file_put_contents($file, $cleaned, LOCK_EX) !== strlen($cleaned)) {
					$this->cleanupNotice();
					return;
				}
			}
		}
		update_option('jd_support_htaccess_cache_removed_22', 1, false);
	}

	private function cleanupNotice(): void
	{
		add_action('admin_notices', static function () {
			echo '<div class="notice notice-warning"><p>' .
				esc_html__(
					'jD Support: remove the legacy BEGIN/END Cache Rules block from .htaccess manually. Check file permissions. The saved backup is in the jd_support_legacy_htaccess_backup_22 option. Other rules must be preserved.',
					'jd_support',
				) .
				'</p></div>';
		});
	}
}
