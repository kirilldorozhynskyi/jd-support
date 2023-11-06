<?php
/**
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              justdev.org
 * @since             0.0.2
 * @package           jd_support
 *
 * @wordpress-plugin
 * Plugin Name:       justDev Support
 * Plugin URI:        justdev.org
 * Description:       Plugin for dev tools.
 * Version: 1.0.0
 * Author:            Kyrylo Dorozhynskyi | justDev
 * Author URI:        justdev.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jd_support
 * Domain Path:       /languages
 */

function hide_update_notice()
{
  if (!current_user_can("update_core")) {
    remove_action("admin_notices", "update_nag", 3);
  }
}
add_action("admin_head", "hide_update_notice", 1);

remove_action("wp_head", "adjacent_posts_rel_link_wp_head", 10, 0);

function theme_remove_user_delete($actions)
{
  unset($actions["delete"]);
  return $actions;
}
add_filter("user_row_actions", "theme_remove_user_delete", 10, 1);
add_filter("bulk_actions-users", "theme_remove_user_delete", 10, 1);

// If this file is called directly, abort.
if (!defined("WPINC")) {
  die();
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define("PLUGIN_NAME_VERSION", "1.1.1");

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jd_support-activator.php
 */
function activate_jd_support()
{
  require_once plugin_dir_path(__FILE__) .
    "includes/class-jd_support-activator.php";
  Jd_support_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jd_support-deactivator.php
 */
function deactivate_jd_support()
{
  require_once plugin_dir_path(__FILE__) .
    "includes/class-jd_support-deactivator.php";
  Jd_support_Deactivator::deactivate();
}

register_activation_hook(__FILE__, "activate_jd_support");
register_deactivation_hook(__FILE__, "deactivate_jd_support");

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . "includes/class-jd_support.php";

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_jd_support()
{
  $plugin = new Jd_support();
  $plugin->run();
}
run_jd_support();

remove_action("wp_head", "adjacent_posts_rel_link_wp_head", 10, 0);

if (!defined("DISALLOW_INDEXING") || DISALLOW_INDEXING !== true) {
  return;
}

add_action("pre_option_blog_public", "__return_zero");

add_action("admin_init", function () {
  if (!apply_filters("roots/bedrock/disallow_indexing_admin_notice", true)) {
    return;
  }

  add_action("admin_notices", function () {
    $message = sprintf(
      __(
        '%1$s Search engine indexing has been discouraged because the current environment is %2$s.',
        "roots"
      ),
      "<code>" . WP_ENV . "</code>"
    );
    echo "<div class='notice notice-warning'><p>{$message}</p></div>";
  });
});
