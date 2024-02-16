<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       justdev.org
 * @since      0.0.4
 *
 * @package    Jd_support
 * @subpackage Jd_support/admin
 */

// create custom plugin settings menu
add_action('admin_menu', 'add_plugin_page');

function add_plugin_page()
{
	//create new top-level menu
	add_options_page('Sittings justDev Support', 'j|D Support', 'manage_options', 'jd_support', 'jd_options_page_output');

	//call register settings function
	add_action('admin_init', 'add_plugin_page_settings');
}

function add_plugin_page_settings()
{
	//register our settings
	register_setting('jd_plugin-settings-group', 'jd_permissions_mode');
	register_setting('jd_plugin-settings-group', 'jd_remove_wp');
	register_setting('jd_plugin-settings-group', 'jd_remove_wp_core');
	register_setting('jd_plugin-settings-group', 'jd_remove_comm');
	register_setting('jd_plugin-settings-group', 'jd_add_logo');
	register_setting('jd_plugin-settings-group', 'jd_custom_footer');
	register_setting('jd_plugin-settings-group', 'jd_svg_support');
	register_setting('jd_plugin-settings-group', 'jd_dark_mode');
	register_setting('jd_plugin-settings-group', 'jd_hide_update');
}

function jd_options_page_output()
{
	$current_user = wp_get_current_user();
	$email = $current_user->user_email;
	$domaine = 'justdev.org';
	?>
<div class="wrap">
    <h1><?php _e('justDev settings', 'jd_support'); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields('jd_plugin-settings-group'); ?>
        <?php do_settings_sections('jd_plugin-settings-group'); ?>
        <table class="form-table">
            <?php if (strpos($email, $domaine) !== false): ?>

            <tr valign="top">
                <th scope="row">
                    <h2><?php _e('Basic options', 'jd_support'); ?></h2>
                </th>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('File edit permissions', 'jd_support'); ?></th>
                <td>
                    <select name="jd_permissions_mode"
                        value="<?php echo esc_attr(get_option('jd_permissions_mode')); ?>">
                        <option value="yes"
                            <?php if (get_option('jd_permissions_mode') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no"
                            <?php if (get_option('jd_permissions_mode') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Remove WP Version', 'jd_support'); ?></th>
                <td>
                    <select name="jd_remove_wp" value="<?php echo esc_attr(get_option('jd_remove_wp')); ?>">
                        <option value="yes" <?php if (get_option('jd_remove_wp') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no" <?php if (get_option('jd_remove_wp') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Remove WP Version from admin', 'jd_support'); ?></th>
                <td>
                    <select name="jd_remove_wp_core" value="<?php echo esc_attr(get_option('jd_remove_wp_core')); ?>">
                        <option value="yes"
                            <?php if (get_option('jd_remove_wp_core') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no" <?php if (get_option('jd_remove_wp_core') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Add jD logo', 'jd_support'); ?></th>
                <td>
                    <select name="jd_add_logo" value="<?php echo esc_attr(get_option('jd_add_logo')); ?>">
                        <option value="yes" <?php if (get_option('jd_add_logo') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no" <?php if (get_option('jd_add_logo') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Add custom footer to admin panel', 'jd_support'); ?></th>
                <td>
                    <select name="jd_custom_footer" value="<?php echo esc_attr(get_option('jd_custom_footer')); ?>">
                        <option value="yes" <?php if (get_option('jd_custom_footer') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no" <?php if (get_option('jd_custom_footer') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Add SVG Support', 'jd_support'); ?></th>
                <td>
                    <select name="jd_svg_support" value="<?php echo esc_attr(get_option('jd_svg_support')); ?>">
                        <option value="yes" <?php if (get_option('jd_svg_support') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no" <?php if (get_option('jd_svg_support') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><?php _e('Dark Mode', 'jd_support'); ?> - BETA</th>
                <td>
                    <select name="jd_dark_mode" value="<?php echo esc_attr(get_option('jd_dark_mode')); ?>">
                        <option value="yes" <?php if (get_option('jd_dark_mode') == 'yes'): ?>selected<?php endif; ?>>
                            <?php _e('Yes', 'jd_support'); ?></option>
                        <option value="no" <?php if (get_option('jd_dark_mode') == 'no'): ?>selected<?php endif; ?>>
                            <?php _e('No', 'jd_support'); ?></option>
                    </select>
                </td>
            </tr>

            <?php endif; ?>

        </table>

        <?php submit_button(); ?>

    </form>
</div>
<?php
}
