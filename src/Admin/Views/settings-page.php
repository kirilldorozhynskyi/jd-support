<?php
/**
 * Settings page view
 *
 * @package JdSupport\Admin\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
} ?>

<div class="wrap">
    <h1><?php _e('justDev Settings', 'jd_support'); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields('jd_plugin-settings-group'); ?>
        <?php do_settings_sections('jd_plugin-settings-group'); ?>

        <table class="form-table">
            <?php if (strpos($email, $domain) !== false): ?>
                <tr valign="top">
                    <th scope="row">
                        <h2><?php _e('Basic Options', 'jd_support'); ?></h2>
                    </th>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('File Edit Permissions', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_permissions_mode">
                            <option value="yes" <?php selected(get_option('jd_permissions_mode'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_permissions_mode'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Remove WP Version', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_remove_wp">
                            <option value="yes" <?php selected(get_option('jd_remove_wp'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_remove_wp'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Remove WP Version from Admin', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_remove_wp_core">
                            <option value="yes" <?php selected(get_option('jd_remove_wp_core'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_remove_wp_core'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Add Custom Logo', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_add_logo">
                            <option value="yes" <?php selected(get_option('jd_add_logo'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_add_logo'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Custom Footer Text', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_custom_footer">
                            <option value="yes" <?php selected(get_option('jd_custom_footer'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_custom_footer'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('SVG Support', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_svg_support">
                            <option value="yes" <?php selected(get_option('jd_svg_support'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_svg_support'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Cache Rules', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_cache">
                            <option value="yes" <?php selected(get_option('jd_cache'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_cache'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Dark Mode', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_dark_mode">
                            <option value="yes" <?php selected(get_option('jd_dark_mode'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_dark_mode'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Hide Update Notice', 'jd_support'); ?></th>
                    <td>
                        <select name="jd_hide_update">
                            <option value="yes" <?php selected(get_option('jd_hide_update'), 'yes'); ?>>
                                <?php _e('Yes', 'jd_support'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('jd_hide_update'), 'no'); ?>>
                                <?php _e('No', 'jd_support'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            <?php else: ?>
                <tr valign="top">
                    <th scope="row">
                        <p><?php _e('Access denied. This plugin is only available for justDev users.', 'jd_support'); ?></p>
                    </th>
                </tr>
            <?php endif; ?>
        </table>

        <?php if (strpos($email, $domain) !== false): ?>
            <?php submit_button(); ?>
        <?php endif; ?>
    </form>
</div>
