<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       justdev.org
 * @since      0.0.1
 *
 * @package    Jd_support
 * @subpackage Jd_support/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Jd_support
 * @subpackage Jd_support/admin
 * @author     Kyrylo Dorozhynskyi | justDev <kyrylo.dorozhynskyi@justdev.org>
 */
class Jd_support_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jd_support_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jd_support_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Загружаем стили с использованием свойства $plugin_name
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/jd_support-admin.css', [], $this->version, 'all');

		// Добавляем стиль в фильтр gform_noconflict_styles
		add_filter('gform_noconflict_styles', [$this, 'register_style']);
	}

	public function register_style($styles)
	{
		// Регистрируем стиль с использованием свойства $plugin_name
		$styles[] = $this->plugin_name;
		return $styles;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jd_support_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jd_support_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/jd_support-admin.js', ['jquery'], $this->version, true);
	}
}
