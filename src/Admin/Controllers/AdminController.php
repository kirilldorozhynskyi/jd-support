<?php

namespace JdSupport\Admin\Controllers;

use JdSupport\Core\Container\Container;

/**
 * Admin Controller
 *
 * @package JdSupport\Admin\Controllers
 */
class AdminController
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Register admin hooks
	 */
	public function registerHooks(): void
	{
		add_action('admin_menu', [$this, 'addPluginPage']);
		add_action('admin_init', [$this, 'addPluginPageSettings']);
	}

	/**
	 * Add plugin settings page
	 */
	public function addPluginPage(): void
	{
		add_options_page('Settings justDev Support', 'j|D Support', 'manage_options', 'jd_support', [$this, 'optionsPageOutput']);
	}

	/**
	 * Register plugin settings
	 */
	public function addPluginPageSettings(): void
	{
		register_setting('jd_plugin-settings-group', 'jd_permissions_mode');
		register_setting('jd_plugin-settings-group', 'jd_remove_wp');
		register_setting('jd_plugin-settings-group', 'jd_remove_wp_core');
		register_setting('jd_plugin-settings-group', 'jd_remove_comm');
		register_setting('jd_plugin-settings-group', 'jd_add_logo');
		register_setting('jd_plugin-settings-group', 'jd_custom_footer');
		register_setting('jd_plugin-settings-group', 'jd_svg_support');
		register_setting('jd_plugin-settings-group', 'jd_cache');
		register_setting('jd_plugin-settings-group', 'jd_dark_mode');
		register_setting('jd_plugin-settings-group', 'jd_hide_update');
	}

	/**
	 * Output settings page
	 */
	public function optionsPageOutput(): void
	{
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;
		$domain = 'justdev.org';
		?>
		<div class="wrap">
			<h1><?php _e('justDev settings', 'jd_support'); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields('jd_plugin-settings-group'); ?>
				<?php do_settings_sections('jd_plugin-settings-group'); ?>
				<table class="form-table">
					<?php if (strpos($email, $domain) !== false): ?>

					<tr valign="top">
						<th scope="row">
							<h2><?php _e('Basic options', 'jd_support'); ?></h2>
						</th>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('File edit permissions', 'jd_support'); ?></th>
						<td>
							<select name="jd_permissions_mode" value="<?php echo esc_attr(get_option('jd_permissions_mode')); ?>">
								<option value="yes" <?php if (get_option('jd_permissions_mode') == 'yes'): ?>selected<?php endif; ?>>
									<?php _e('Yes', 'jd_support'); ?></option>
								<option value="no" <?php if (get_option('jd_permissions_mode') == 'no'): ?>selected<?php endif; ?>>
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
								<option value="yes" <?php if (get_option('jd_remove_wp_core') == 'yes'): ?>selected<?php endif; ?>>
									<?php _e('Yes', 'jd_support'); ?></option>
								<option value="no" <?php if (get_option('jd_remove_wp_core') == 'no'): ?>selected<?php endif; ?>>
									<?php _e('No', 'jd_support'); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Remove comments', 'jd_support'); ?></th>
						<td>
							<select name="jd_remove_comm" value="<?php echo esc_attr(get_option('jd_remove_comm')); ?>">
								<option value="yes" <?php if (get_option('jd_remove_comm') == 'yes'): ?>selected<?php endif; ?>>
									<?php _e('Yes', 'jd_support'); ?></option>
								<option value="no" <?php if (get_option('jd_remove_comm') == 'no'): ?>selected<?php endif; ?>>
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

					<tr valign="top">
						<th scope="row"><?php _e('Cache', 'jd_support'); ?></th>
						<td>
							<select name="jd_cache" value="<?php echo esc_attr(get_option('jd_cache')); ?>">
								<option value="no" <?php if (get_option('jd_cache') == 'no'): ?>selected<?php endif; ?>>
									<?php _e('No', 'jd_support'); ?></option>
								<option value="yes" <?php if (get_option('jd_cache') == 'yes'): ?>selected<?php endif; ?>>
									<?php _e('Yes', 'jd_support'); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Hide update notifications', 'jd_support'); ?></th>
						<td>
							<select name="jd_hide_update" value="<?php echo esc_attr(get_option('jd_hide_update')); ?>">
								<option value="yes" <?php if (get_option('jd_hide_update') == 'yes'): ?>selected<?php endif; ?>>
									<?php _e('Yes', 'jd_support'); ?></option>
								<option value="no" <?php if (get_option('jd_hide_update') == 'no'): ?>selected<?php endif; ?>>
									<?php _e('No', 'jd_support'); ?></option>
							</select>
						</td>
					</tr>

					<?php else: ?>

					<tr valign="top">
						<th scope="row">
							<h2><?php _e('Access Denied', 'jd_support'); ?></h2>
						</th>
					</tr>
					<tr valign="top">
						<td colspan="2">
							<p><?php _e('This plugin is only available for justDev team members.', 'jd_support'); ?></p>
						</td>
					</tr>

					<?php endif; ?>

				</table>

				<?php submit_button(); ?>

			</form>
		</div>
		<?php
	}
} 