<?php

namespace JDEV;

/**
 * Simple cache helper that prefers WordPress Object Cache,
 * with a transient fallback. Useful to reuse across theme classes.
 */
class Cache
{
	/**
	 * Cache wrapper: tries object cache first, then transient; computes on miss.
	 *
	 * @param string   $key      Cache key without group prefix
	 * @param callable $callback Function to compute the value on cache miss
	 * @param int      $ttl      Time to live in seconds
	 * @param string   $group    Cache group (used for wp_cache and transient prefix)
	 * @return mixed             Cached or computed value
	 */
	public static function getCached(string $key, callable $callback, int $ttl = 300, string $group = 'inertia')
	{
		if (class_exists(\JdSupport\Cache::class)) {
			return \JdSupport\Cache::getCached($key, $callback, $ttl, $group);
		}
		// Always read fresh theme data outside production.
		$environment = defined('WP_ENV') ? WP_ENV : (function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production');
		if (in_array($environment, ['local', 'development', 'staging'], true)) {
			return $callback();
		}

		// 1) Try Object Cache
		$value = wp_cache_get($key, $group);
		if (false !== $value) {
			return $value;
		}

		// 2) Try Transient
		$transientKey = $group . '_' . $key;
		$value = get_transient($transientKey);
		if (false !== $value) {
			wp_cache_set($key, $value, $group, $ttl);
			return $value;
		}

		// 3) Compute and store
		$value = $callback();

		if ($value !== null) {
			wp_cache_set($key, $value, $group, $ttl);
			set_transient($transientKey, $value, $ttl);
		}

		return $value;
	}

	/**
	 * Clear one group, or both groups owned by the theme.
	 */
	public static function clear(?string $group = null): void
	{
		if (class_exists(\JdSupport\Cache::class)) {
			\JdSupport\Cache::clear($group);
			return;
		}
		// очистка Object Cache
		wp_cache_flush();

		// Delete through the options API so its in-memory cache stays consistent too.
		global $wpdb;
		foreach ($group === null ? ['inertia', 'theme'] : [$group] as $cacheGroup) {
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
	}
}
