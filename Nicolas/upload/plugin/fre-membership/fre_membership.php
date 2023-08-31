<?php
/**
Plugin Name: FrE Membership
Plugin URI: http://enginethemes.com/
Description: A plugin that allows recurring payment in FreelanceEngine
Version: 2.0.2
Author: EngineThemes
Author URI: http://enginethemes.com/
License: GPLv2
Text Domain: enginethemes
*/

define( 'FRE_MEMBERSHIP_VER', '2.0.2');
define( 'FRE_MEMBERSHIP_PATH', dirname( __FILE__ ) );
define( 'MEMBERSHIP_URL', plugin_dir_url( __FILE__ ) );
define( 'MEMBERSHIP_CRON_HOOK','membership_cron_hook');
define( 'MEMBERSHIP_DEBUG', 0   );
define( 'MEMBERSHIP_LOG', 0 );
//define( 'MEMBERSHIP_LOG', WP_CONTENT_DIR.'/member_log.css' );
define( 'ET_CRON_PATH', WP_CONTENT_DIR.'/uploads/sites/'.get_current_blog_id().'/2021/06/et_cron.css');

require_once FRE_MEMBERSHIP_PATH . '/includes/create_db.php';
require_once FRE_MEMBERSHIP_PATH . '/includes/functions.php';
require_once FRE_MEMBERSHIP_PATH . '/includes/default_email_html.php';

function require_membership_files() {
	if( ! class_exists('ET_FreelanceEngine') ) return;
	require_once FRE_MEMBERSHIP_PATH . '/includes/index.php';
	require_once FRE_MEMBERSHIP_PATH . '/admin/admin.php';
	require_once FRE_MEMBERSHIP_PATH . '/settings.php';

	require_once dirname( __FILE__ ) . '/update.php';
	upgrade_membership_table();
}
add_action( 'after_setup_theme', 'require_membership_files' ); // no add priority here. If add is_post_project_free not work.

function fre_membership_build_db(){
	require_once FRE_MEMBERSHIP_PATH . '/includes/class_schedules_cron.php';
	require_once FRE_MEMBERSHIP_PATH . '/includes/activation_hook_define.php';
	fre_membership_install();
	upgrade_membership_table();
	set_membership_default_values();

	if ( ! wp_next_scheduled ( 'excute_membership_event' ) ) {
		et_cron_log('activation_hook: Add schedule_event:');
		wp_schedule_event(time(), 'every_six_hours', 'excute_membership_event'); // catch hook mebership_schedule_event mebership_schedule_event == hourly/daily/
	}

}
register_activation_hook( __FILE__, 'fre_membership_build_db', 15 );

function fre_membership_deactivation() {
	et_member_log('wp_clear_scheduled_hook');
	wp_clear_scheduled_hook('excute_membership_event');
	update_option('next_time_checking_cron', '0');
	membership_remove_log();

}
register_deactivation_hook(__FILE__, 'fre_membership_deactivation');

function add_membership_schedule($schedules){
	$schedules['define_cron_time'] = array(
       'interval' => 2*60*60,
       'display' =>  'Auto Check Membership',
   	);
	return $schedules;
}
add_filter('cron_schedules','add_membership_schedule', 999);


function membership_cron_check(){
	add_action( MEMBERSHIP_CRON_HOOK, 'membership_cron_function' );
	if ( ! wp_next_scheduled( MEMBERSHIP_CRON_HOOK ) ) {
	    wp_schedule_event( time(), 'define_cron_time', MEMBERSHIP_CRON_HOOK );
	}
}

add_action('init','membership_cron_check', 999);

function membership_cron_function(){
	$cron = $GLOBALS['membership_cron'];
	$cron->auto_checking_subscription();
}

if( MEMBERSHIP_DEBUG ){

	// require_once dirname(__FILE__).'/debug.php';
	// require_once dirname(__FILE__).'/struct_db.php';
}