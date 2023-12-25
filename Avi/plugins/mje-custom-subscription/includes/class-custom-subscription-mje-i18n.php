<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://mysite.com
 * @since      1.0.0
 *
 * @package    Custom_Subscription_Mje
 * @subpackage Custom_Subscription_Mje/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Custom_Subscription_Mje
 * @subpackage Custom_Subscription_Mje/includes
 * @author     TranBao <tranbao3666@gmail.com>
 */
class Custom_Subscription_Mje_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'custom-subscription-mje',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
