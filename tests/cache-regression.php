<?php
// Standalone regression harness: php tests/cache-regression.php production [legacy|persistent]
// WordPress storage/hook doubles; does not load or modify a real site's database.
define('WP_ENV', $argv[1] ?? 'production');
$legacy = ($argv[2] ?? '') === 'legacy';
$persistent = ($argv[2] ?? '') === 'persistent';
$objects = $options = $hooks = [];
$flushes = 0;
function check($condition, $message)
{
	if (!$condition) {
		throw new RuntimeException($message);
	}
}
function add_action($hook, $callback, $priority = 10, $args = 1)
{
	$GLOBALS['hooks'][$hook][$priority][] = [$callback, $args];
}
function add_filter($hook, $callback, $priority = 10, $args = 1)
{
	add_action($hook, $callback, $priority, $args);
}
function do_action($hook, ...$args)
{
	$callbacks = $GLOBALS['hooks'][$hook] ?? [];
	ksort($callbacks);
	foreach ($callbacks as $items) {
		foreach ($items as [$callback, $accepted]) {
			$callback(...array_slice($args, 0, $accepted));
		}
	}
}
function apply_filters($hook, $value)
{
	foreach ($GLOBALS['hooks'][$hook] ?? [] as $items) {
		foreach ($items as [$callback]) {
			$value = $callback($value);
		}
	}
	return $value;
}
function wp_cache_get($key, $group, $force = false, &$found = null)
{
	$found = array_key_exists($key, $GLOBALS['objects'][$group] ?? []);
	return $found ? $GLOBALS['objects'][$group][$key] : false;
}
function wp_cache_set($key, $value, $group, $ttl)
{
	$GLOBALS['objects'][$group][$key] = $value;
}
function wp_cache_flush()
{
	$GLOBALS['objects'] = [];
	$GLOBALS['flushes']++;
}
function get_option($key, $default = false)
{
	return $GLOBALS['options'][$key] ?? $default;
}
function current_user_can($capability)
{
	return true;
}
function add_option($key, $value, $deprecated = '', $autoload = null)
{
	if (array_key_exists($key, $GLOBALS['options'])) {
		return false;
	}
	update_option($key, $value);
	return true;
}
function update_option($key, $value)
{
	$exists = array_key_exists($key, $GLOBALS['options']);
	$GLOBALS['options'][$key] = $value;
	do_action($exists ? 'updated_option' : 'added_option', $key);
}
function delete_option($key)
{
	if (array_key_exists($key, $GLOBALS['options'])) {
		unset($GLOBALS['options'][$key]);
		do_action('deleted_option', $key);
	}
}
function get_transient($key)
{
	return $GLOBALS['persistent'] ? wp_cache_get($key, 'transient') : get_option('_transient_' . $key);
}
function set_transient($key, $value, $ttl)
{
	if ($GLOBALS['persistent']) {
		wp_cache_set($key, $value, 'transient', $ttl);
		return;
	}
	update_option('_transient_' . $key, $value);
	update_option('_transient_timeout_' . $key, time() + $ttl);
}
$wpdb = new class {
	public $options = 'wp_options';
	public function esc_like($value)
	{
		return addcslashes($value, '_%\\');
	}
	public function prepare($sql, ...$args)
	{
		return $args;
	}
	public function get_col($patterns)
	{
		return array_values(
			array_filter(array_keys($GLOBALS['options']), static function ($key) use ($patterns) {
				foreach ($patterns as $pattern) {
					if (strpos($key, stripslashes(substr($pattern, 0, -1))) === 0) {
						return true;
					}
				}
				return false;
			}),
		);
	}
};
if (!$legacy) {
	spl_autoload_register(static function ($class) {
		if (strpos($class, 'JdSupport\\') === 0) {
			require __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, 10)) . '.php';
		}
	});
	$container = new JdSupport\Core\Container\Container();
	$container->registerServices();
	$manager = new JdSupport\Core\Hooks\HookManager($container);
	$manager->registerCoreHooks();
	$manager->run();
}
require __DIR__ . '/../stubs/JDEVCache.php';
$calls = 0;
$callback = static function () use (&$calls) {
	return ++$calls;
};
$first = JDEV\Cache::getCached('shared', $callback, 300, 'theme');
$second = JDEV\Cache::getCached('shared', $callback, 300, 'theme');
$bypass = in_array(WP_ENV, ['local', 'development', 'staging'], true);
// The legacy helper originally recognizes development and staging only.
check($first === 1 && $second === ($bypass ? 2 : 1), 'Environment cache behavior');
if ($bypass) {
	check(!$options && !$objects, 'Non-production wrote cache');
	echo 'PASS: ' . WP_ENV . " bypass\n";
	exit();
}
if (!$legacy) {
	check(JdSupport\Cache::getCached('shared', $callback, 300, 'theme') === 1, 'Plugin cannot read theme cache');
	JdSupport\Cache::clear();
	check(JDEV\Cache::getCached('shared', $callback, 300, 'theme') === 2, 'Plugin did not clear theme cache');
	JDEV\Cache::clear();
	check(JdSupport\Cache::getCached('shared', $callback, 300, 'theme') === 3, 'Theme did not clear plugin cache');
	check(
		JdSupport\Cache::getCached('false', static function () {
			return false;
		}) === false,
		'False result',
	);
	check(
		JdSupport\Cache::getCached('false', static function () {
			throw new RuntimeException('False cache miss');
		}) === false,
		'False object-cache hit',
	);
	JdSupport\Cache::getCached('null', static function () {
		return null;
	});
	check(!array_key_exists('null', $objects['inertia'] ?? []), 'Null was cached');
}
// Legacy-format DB entries, including orphan timeouts, remain clearable with Redis enabled.
$options['_transient_theme_old'] = 'old';
$options['_transient_timeout_theme_orphan'] = 123;
$options['_transient_inertia_old'] = 'old';
$options['_transient_themeX_keep'] = 'keep';
$options['_transient_other_keep'] = 'keep';
JDEV\Cache::clear('theme');
check(!isset($options['_transient_theme_old']) && !isset($options['_transient_timeout_theme_orphan']), 'Theme rows survived');
check(isset($options['_transient_inertia_old']), 'Explicit group removed inertia');
JDEV\Cache::clear();
check(!isset($options['_transient_inertia_old']), 'Default groups not cleared');
check(isset($options['_transient_themeX_keep'], $options['_transient_other_keep']), 'Foreign DB cache removed');
if (!$legacy) {
	$before = $flushes;
	$rules = "# BEGIN Cache Rules\nExpiresActive On\n# END Cache Rules\n";
	$other = "# BEGIN WordPress\nRewriteEngine On\n# END WordPress\n";
	check(JdSupport\Services\CacheService::stripLegacyHtaccessRules($rules . $other . $rules) === $other, 'Cleanup removed other rules');
	check(
		JdSupport\Services\CacheService::stripLegacyHtaccessRules(str_replace("\n", "\r\n", $rules . $other)) === str_replace("\n", "\r\n", $other),
		'CRLF cleanup',
	);
	check(
		JdSupport\Services\CacheService::stripLegacyHtaccessRules("# BEGIN Cache Rules\nUnclosed") === "# BEGIN Cache Rules\nUnclosed",
		'Unclosed block was removed',
	);
	$temp = sys_get_temp_dir() . '/jd-support-cache-test-' . bin2hex(random_bytes(8));
	mkdir($temp);
	define('ABSPATH', $temp . '/');
	try {
		file_put_contents($temp . '/.htaccess', $rules . $other);
		$container->get('cache')->removeLegacyHtaccessRules();
		check(file_get_contents($temp . '/.htaccess') === $other, 'Legacy block not removed');
		check(get_option('jd_support_legacy_htaccess_backup_22') === $rules . $other, 'Backup not preserved');
		check(get_option('jd_support_htaccess_cache_removed_22') === 1, 'Cleanup not marked complete');
		$container->get('cache')->removeLegacyHtaccessRules();
		check(file_get_contents($temp . '/.htaccess') === $other, 'Repeated cleanup changed file');
	} finally {
		unlink($temp . '/.htaccess');
		rmdir($temp);
	}
	$before = $flushes;
	set_transient('theme_write', 'one', 300);
	set_transient('theme_write', 'two', 300);
	delete_option('_transient_theme_write');
	check($flushes === $before, 'Transient maintenance triggered invalidation');
	foreach (['save_post', 'deleted_post', 'wp_update_nav_menu', 'acf/save_post', 'added_option', 'updated_option', 'deleted_option'] as $hook) {
		$before = $flushes;
		do_action($hook, 'content');
		check($flushes === $before + 1, 'Missing invalidation: ' . $hook);
	}
	check(isset($hooks['acf/save_post'][20]), 'ACF callback must run after save');
	add_filter('jd_support/cache/groups', static function ($groups) {
		$groups[] = 'project';
		return $groups;
	});
	$options['_transient_project_old'] = 'old';
	add_action('jd_support/cache/cleared', static function () {
		JDEV\Cache::clear();
	});
	$before = $flushes;
	do_action('jd_support/cache/clear');
	check($flushes === $before + 1 && !isset($options['_transient_project_old']), 'Custom groups or recursion guard');
}
echo 'PASS: ' . ($legacy ? 'plugin-disabled fallback' : ($persistent ? 'persistent cache' : 'DB fallback')) . ", bidirectional cache and hooks\n";
