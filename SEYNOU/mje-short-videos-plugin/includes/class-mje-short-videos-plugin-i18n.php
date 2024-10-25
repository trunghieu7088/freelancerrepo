<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://enginethemes.com
 * @since      1.0.0
 *
 * @package    Mje_Short_Videos_Plugin
 * @subpackage Mje_Short_Videos_Plugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Mje_Short_Videos_Plugin
 * @subpackage Mje_Short_Videos_Plugin/includes
 * @author     Trung Hieu <lamtrunghieu366@gmail.com>
 */
class Mje_Short_Videos_Plugin_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'mje-short-videos-plugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
