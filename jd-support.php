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
 * Version:           2.0.2
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
define('JD_SUPPORT_VERSION', '2.0.2');
define('JD_SUPPORT_PLUGIN_NAME', 'jd_support');
define('JD_SUPPORT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('JD_SUPPORT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Simple autoloader
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

// Initialize plugin
add_action('plugins_loaded', function () {
	$plugin = new \JdSupport\Core\Plugin();
	$plugin->run();
});

// Register activation/deactivation hooks
register_activation_hook(__FILE__, [\JdSupport\Core\Activation\ActivationManager::class, 'activate']);
register_deactivation_hook(__FILE__, [\JdSupport\Core\Activation\ActivationManager::class, 'deactivate']);
