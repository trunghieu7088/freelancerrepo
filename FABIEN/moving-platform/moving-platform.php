<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://hieubinh.com
 * @since             1.0.0
 * @package           Moving_Platform
 *
 * @wordpress-plugin
 * Plugin Name:       Moving Platform
 * Plugin URI:        https://hieubinh.com
 * Description:       Integrating moving platform to your site
 * Version:           1.0.0
 * Author:            Thien Vu
 * Author URI:        https://hieubinh.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       moving_platform
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
define( 'MOVING_PLATFORM_VERSION', '1.0.0' );

//custom code
define('MOVING_PLATFORM_PATH', dirname(__FILE__));
define('MOVING_PLATFORM_URL', plugin_dir_url(__FILE__));
//end

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-moving-platform-activator.php
 */
function activate_moving_platform() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moving-platform-activator.php';
	Moving_Platform_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-moving-platform-deactivator.php
 */
function deactivate_moving_platform() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moving-platform-deactivator.php';
	Moving_Platform_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_moving_platform' );
register_deactivation_hook( __FILE__, 'deactivate_moving_platform' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-moving-platform.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_moving_platform() {

	$plugin = new Moving_Platform();
	$plugin->run();

}
run_moving_platform();

//custom code
function require_moving_platform_files()
{
	require_once MOVING_PLATFORM_PATH . '/includes/functions.php';
	require_once MOVING_PLATFORM_PATH . '/admin/settings.php';	
}
add_action('after_setup_theme', 'require_moving_platform_files'); 


add_action('init',  'custom_load_textdomain', 99);
function custom_load_textdomain()
{
	load_plugin_textdomain('moving_platform', false,  dirname(plugin_basename(__FILE__)) . '/languages');
}
//end