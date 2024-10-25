<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://enginethemes.com
 * @since             1.0.0
 * @package           Mje_Short_Videos_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       MjE Short Videos
 * Plugin URI:        https://enginethemes.com
 * Description:       A WordPress plugin that allows users to upload and share short videos. Easily create engaging, bite-sized video content on your site!
 * Version:           1.0.0
 * Author:            Trung Hieu
 * Author URI:        https://enginethemes.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mje-short-videos-plugin
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
define( 'MJE_SHORT_VIDEOS_PLUGIN_VERSION', '1.0.0' );
//custom code
define('MJE_SHORT_VIDEOS_PATH', dirname(__FILE__));
define('MJE_SHORT_VIDEOS_URL', plugin_dir_url(__FILE__));
//end
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mje-short-videos-plugin-activator.php
 */
function activate_mje_short_videos_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mje-short-videos-plugin-activator.php';
	Mje_Short_Videos_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mje-short-videos-plugin-deactivator.php
 */
function deactivate_mje_short_videos_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mje-short-videos-plugin-deactivator.php';
	Mje_Short_Videos_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mje_short_videos_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_mje_short_videos_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mje-short-videos-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mje_short_videos_plugin() {

	$plugin = new Mje_Short_Videos_Plugin();
	$plugin->run();

}
run_mje_short_videos_plugin();

//custom code
function require_mje_short_videos_files()
{
	require_once MJE_SHORT_VIDEOS_PATH . '/includes/functions.php';
	require_once MJE_SHORT_VIDEOS_PATH . '/admin/settings.php';	
}
add_action('after_setup_theme', 'require_mje_short_videos_files'); 


add_action('init',  'mje_short_video_custom_load_textdomain', 99);
function mje_short_video_custom_load_textdomain()
{
	load_plugin_textdomain('mje_short_video', false,  dirname(plugin_basename(__FILE__)) . '/languages');
}
//end