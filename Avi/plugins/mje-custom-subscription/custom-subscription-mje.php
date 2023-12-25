<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mysite.com
 * @since             1.0.0
 * @package           Custom_Subscription_Mje
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Subscription MJE
 * Plugin URI:        https://mysite.com
 * Description:       An awesome plugin to create subscription feature for MicrojobEngine single theme
 * Version:           1.0.0
 * Author:            TranBao
 * Author URI:        https://mysite.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-subscription-mje
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUSTOM_SUBSCRIPTION_MJE_VERSION', '1.0.0' );
//custom code

define( 'CUSTOM_SUBSCRIPTION_MJE_PATH', dirname( __FILE__ ) );
define( 'CUSTOM_SUBSCRIPTION_MJE_URL', plugin_dir_url( __FILE__ ) );

//end

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-subscription-mje-activator.php
 */
function activate_custom_subscription_mje() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-subscription-mje-activator.php';
	Custom_Subscription_Mje_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-subscription-mje-deactivator.php
 */
function deactivate_custom_subscription_mje() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-subscription-mje-deactivator.php';
	Custom_Subscription_Mje_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_custom_subscription_mje' );
register_deactivation_hook( __FILE__, 'deactivate_custom_subscription_mje' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-subscription-mje.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_custom_subscription_mje() {

	$plugin = new Custom_Subscription_Mje();
	$plugin->run();

}
run_custom_subscription_mje();

//custom code
function require_custom_subscription_mje_files()
{
	require_once CUSTOM_SUBSCRIPTION_MJE_PATH . '/includes/functions.php';
	require_once CUSTOM_SUBSCRIPTION_MJE_PATH . '/admin/settings.php';
	//require_once CUSTOM_SUBSCRIPTION_MJE_PATH . '/includes/index.php';
}
add_action( 'after_setup_theme', 'require_custom_subscription_mje_files' ); 
//end