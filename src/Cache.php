<?php

namespace JdSupport;

/**
 * Shared data cache. Storage keys remain compatible with the JDEV theme helper.
 */
class Cache
{
	private static $clearing = false;

	public static function getCached(string $key, callable $callback, int $ttl = 300, string $group = 'inertia')
	{
		$environment = defined('WP_ENV') ? WP_ENV : (function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production');
		if (in_array($environment, ['local', 'development', 'staging'], true)) {
			return $callback();
		}

		$value = wp_cache_get($key, $group, false, $found);
		if ($found) {
			return $value;
		}

		$transientKey = $group . '_' . $key;
		$value = get_transient($transientKey);
		if ($value !== false) {
			wp_cache_set($key, $value, $group, $ttl);
			return $value;
		}

		$value = $callback();
		if ($value !== null) {
			wp_cache_set($key, $value, $group, $ttl);
			set_transient($transientKey, $value, $ttl);
		}
		return $value;
	}

	/**
	 * Clear shared groups or an explicit group, then notify project integrations.
	 * The full object-cache flush intentionally matches the legacy theme behavior.
	 */
	public static function clear(?string $group = null): void
	{
		if (self::$clearing) {
			return;
		}
		$groups = $group === null ? (array) apply_filters('jd_support/cache/groups', ['inertia', 'theme']) : [$group];
		$groups = array_values(
			array_unique(
				array_filter($groups, static function ($value) {
					return is_string($value) && $value !== '';
				}),
			),
		);
		if (!$groups) {
			return;
		}

		self::$clearing = true;
		try {
			wp_cache_flush();
			global $wpdb;
			foreach ($groups as $cacheGroup) {
				$names = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
						$wpdb->esc_like('_transient_' . $cacheGroup . '_') . '%',
						$wpdb->esc_like('_transient_timeout_' . $cacheGroup . '_') . '%',
					),
				);
				foreach ($names as $name) {
					delete_option($name);
				}
			}
			do_action('jd_support/cache/cleared', $groups);
		} finally {
			self::$clearing = false;
		}
	}
}
