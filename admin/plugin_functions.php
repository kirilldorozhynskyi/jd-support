<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       justdev.org
 * @since      0.0.3
 *
 * @package    Jd_support
 * @subpackage Jd_support/admin
 */

// File edit permissions
if (get_option('jd_permissions_mode') == 'no') {
	defined('DISALLOW_FILE_MODS') || define('DISALLOW_FILE_MODS', true);
	defined('DISALLOW_FILE_EDIT') || define('DISALLOW_FILE_EDIT', true);
}

// Remove WP Version
if (get_option('jd_remove_wp') == 'yes') {
	remove_action('wp_head', 'wp_generator'); // из заголовка
	add_filter('the_generator', '__return_empty_string'); // из фидов и URL

	//удаление версии WordPress из ссылок на скрипты start
	function wp_version_js_css($src)
	{
		if (strpos($src, 'ver=' . get_bloginfo('version'))) {
			$src = remove_query_arg('ver', $src);
		}
		return $src;
	}
	add_filter('style_loader_src', 'wp_version_js_css', 9999);
	add_filter('script_loader_src', 'wp_version_js_css', 9999);
}

// Remove WP Version from admin
if (get_option('jd_remove_wp_core') == 'yes') {
	function jd_wp_core()
	{
		remove_filter('update_footer', 'core_update_footer');
	}
	add_action('admin_menu', 'jd_wp_core');
}

// Add custom footer to admin panel
if (get_option('jd_add_logo') == 'yes') {
	add_action('add_admin_bar_menus', 'reset_admin_wplogo');
	function reset_admin_wplogo()
	{
		remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10); // удаляем стандартную панель (логотип)
	}

	add_action('admin_bar_menu', 'my_admin_bar_wp_menu', 10); // добавляем свою
	function my_admin_bar_wp_menu($wp_admin_bar)
	{
		$wp_admin_bar->add_menu([
			'id' => 'wp-logo',
			'title' => '<img style="max-width:20px;height:auto;padding: 7px 0;" src="' . plugin_dir_url(__DIR__) . 'images/jd_white.svg" alt="" >',
			'href' => 'https://justdev.org',
			'meta' => [
				'title' => 'justDev',
				'target' => '_blank',
			],
		]);
	}
}

// Add custom footer to admin panel
if (get_option('jd_custom_footer') == 'yes') {
	function modify_footer_admin()
	{
		echo 'Developed by <a href="https://justdev.org" target="_blank">justDev</a>.';
	}
	add_filter('admin_footer_text', 'modify_footer_admin');
}

// Add support for SVG files
if (get_option('jd_svg_support') == 'yes') {
	function add_file_types_to_uploads($file_types)
	{
		$new_filetypes = [];
		$new_filetypes['svg'] = 'image/svg+xml';
		$file_types = array_merge($file_types, $new_filetypes);

		return $file_types;
	}
	add_action('upload_mimes', 'add_file_types_to_uploads');
}
// Add support for SVG files
if (get_option('jd_dark_mode') == 'yes') {
	add_filter('admin_body_class', function ($classes) {
		$classes .= ' dark-mode';
		return $classes;
	});
}

// Add Gravity Form fix
global $wpdb;

$new_value = '';

$option_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name = %s", 'gform_pending_installation'));

if ($option_exists) {
	$wpdb->update($wpdb->options, ['option_name' => 'rg_gforms_hideLicense'], ['option_name' => 'gform_pending_installation']);
}

$wpdb->update($wpdb->options, ['option_value' => $new_value], ['option_name' => 'rg_gforms_message']);
