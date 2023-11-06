<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       justdev.org
 * @since      0.0.1
 *
 * @package    Jd_support
 * @subpackage Jd_support/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.0.1
 * @package    Jd_support
 * @subpackage Jd_support/includes
 * @author     Kyrylo Dorozhynskyi | justDev <kyrylo.dorozhynskyi@justdev.org>
 */
class Jd_support_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'jd_support',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
