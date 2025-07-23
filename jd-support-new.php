<?php
/**
 * justDev Support Plugin
 *
 * @link              justdev.org
 * @since             1.1.8
 * @package           JdSupport
 *
 * @wordpress-plugin
 * Plugin Name:       justDev Support
 * Plugin URI:        justdev.org
 * Description:       Plugin for dev tools with modern architecture.
 * Version:           1.1.8
 * Author:            Kyrylo Dorozhynskyi | justDev
 * Author URI:        justdev.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jd_support
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('WPINC')) {
	die();
}

// Define plugin constants
define('JD_SUPPORT_VERSION', '1.1.8');
define('JD_SUPPORT_PLUGIN_NAME', 'jd_support');
define('JD_SUPPORT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('JD_SUPPORT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
	// Convert namespace to file path
	$prefix = 'JdSupport\\';
	$base_dir = JD_SUPPORT_PLUGIN_PATH . 'src/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});

// Activation hook
register_activation_hook(__FILE__, function () {
	// Create default options
	$default_options = [
		'jd_permissions_mode' => 'no',
		'jd_remove_wp' => 'yes',
		'jd_remove_wp_core' => 'yes',
		'jd_remove_comm' => 'no',
		'jd_add_logo' => 'yes',
		'jd_custom_footer' => 'yes',
		'jd_svg_support' => 'yes',
		'jd_cache' => 'no',
		'jd_dark_mode' => 'no',
		'jd_hide_update' => 'no',
	];

	foreach ($default_options as $option => $value) {
		if (get_option($option) === false) {
			add_option($option, $value);
		}
	}
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
	// Clean up if needed
});

// Initialize plugin
add_action('plugins_loaded', function () {
	$plugin = new \JdSupport\Core\Plugin();
	$plugin->run();
});

// Handle indexing disallow
if (defined('DISALLOW_INDEXING') && DISALLOW_INDEXING === true) {
	add_action('pre_option_blog_public', '__return_zero');

	add_action('admin_init', function () {
		if (!apply_filters('roots/bedrock/disallow_indexing_admin_notice', true)) {
			return;
		}

		add_action('admin_notices', function () {
			$message = sprintf(
				__('%1$s Search engine indexing has been discouraged because the current environment is %2$s.', 'roots'),
				'<strong>justDev:</strong>',
				'<code>' . WP_ENV . '</code>',
			);
			echo "<div class='notice notice-warning'><p>{$message}</p></div>";
		});
	});
}
