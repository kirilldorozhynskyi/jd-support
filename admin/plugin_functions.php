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



function update_htaccess_cache_rules() {
    $htaccess_file = ABSPATH . '.htaccess';
    $cache_enabled = get_option('jd_cache') === 'yes';

    // Start and end of cache section for safe deletion and update
    $start_marker = '# BEGIN JDEV CACHE';
    $end_marker = '# END JDEV CACHE';

    // Removing old directives from .htaccess
    $htaccess = file_exists($htaccess_file) ? file_get_contents($htaccess_file) : '';
    $htaccess = preg_replace("/$start_marker(.*?)$end_marker/s", '', $htaccess);

    if ($cache_enabled) {
        $cache_rules = <<<HTACCESS
{$start_marker}

# Cache-Control for better performance and cache invalidation
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresDefault "access plus 1 month"

    # HTML (refresh on every load)
    ExpiresByType text/html "access plus 0 seconds"

    # JSON, XML, RSS — update every 1 hour
    ExpiresByType text/xml "access plus 1 hour"
    ExpiresByType application/xml "access plus 1 hour"
    ExpiresByType application/json "access plus 1 hour"
    ExpiresByType application/rss+xml "access plus 1 hour"
    ExpiresByType application/atom+xml "access plus 1 hour"

    # Media: 4 months cash
    ExpiresByType image/gif "access plus 4 months"
    ExpiresByType image/png "access plus 4 months"
    ExpiresByType image/jpeg "access plus 4 months"
    ExpiresByType image/webp "access plus 4 months"
    ExpiresByType image/svg+xml "access plus 4 months"
    ExpiresByType video/mp4 "access plus 4 months"
    ExpiresByType audio/ogg "access plus 4 months"
    ExpiresByType video/ogg "access plus 4 months"
    ExpiresByType video/webm "access plus 4 months"

    # Fonts: 1 year cache
    ExpiresByType font/ttf "access plus 1 year"
    ExpiresByType font/otf "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType application/vnd.ms-fontobject "access plus 1 year"

    # CSS и JS: 1 year cache
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType text/javascript "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
    # Public cache with revalidation
    <FilesMatch "\.(html|htm)$">
        Header set Cache-Control "public, max-age=0, must-revalidate"
    </FilesMatch>

    # Cache for static files (immutable)
    <FilesMatch "\.(css|js|gif|jpe?g|png|webp|svg|woff|woff2|ttf|otf|eot|mp4|webm|avi|mov|flv|ico|json|xml)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    SetOutputFilter DEFLATE

    <IfModule mod_setenvif.c>
        <IfModule mod_headers.c>
            SetEnvIfNoCase ^(Accept-Encoding|X-cept-Encoding|X{15}|~{15}|-{15})$ ^(gzip|deflate)$ HAVE_Accept-Encoding
            RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding

            # Don’t compress images and other uncompressible content
            SetEnvIfNoCase Request_URI \
            \.(?:gif|jpe?g|png|rar|zip|exe|flv|mov|wma|mp3|avi|swf|mp?g|mp4|webm|webp|pdf)$ no-gzip dont-vary
        </IfModule>
    </IfModule>

    # Compress all text-based files
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE application/atom+xml \
                                      application/javascript \
                                      application/json \
                                      application/rss+xml \
                                      application/vnd.ms-fontobject \
                                      application/xhtml+xml \
                                      application/xml \
                                      font/ttf \
                                      font/otf \
                                      font/woff \
                                      font/woff2 \
                                      image/svg+xml \
                                      image/x-icon \
                                      text/css \
                                      text/html \
                                      text/plain \
                                      text/x-component \
                                      text/javascript \
                                      text/xml
    </IfModule>

    <IfModule mod_headers.c>
        Header append Vary: Accept-Encoding
    </IfModule>
</IfModule>

{$end_marker}
HTACCESS;

        // Add new cache rules to the end of .htaccess
        $htaccess .= PHP_EOL . $cache_rules;
    }

    // Write the updated .htaccess
    file_put_contents($htaccess_file, $htaccess);
}

// Update .htaccess when option changes
add_action('update_option_jd_cache', 'update_htaccess_cache_rules');
add_action('admin_init', 'update_htaccess_cache_rules');


// Add Gravity Form fix
global $wpdb;

$new_value = '';

$option_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name = %s", 'gform_pending_installation'));

if ($option_exists) {
	$wpdb->update($wpdb->options, ['option_name' => 'rg_gforms_hideLicense'], ['option_name' => 'gform_pending_installation']);
}

$wpdb->update($wpdb->options, ['option_value' => $new_value], ['option_name' => 'rg_gforms_message']);
