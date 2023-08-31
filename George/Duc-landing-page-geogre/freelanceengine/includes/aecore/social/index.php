<?php
if(!defined('USE_SOCIAL') || !USE_SOCIAL ) return;

define('NEW_SOCIAL', 1); // disable the step update display name;

require_once dirname( __FILE__ ).'/social_auth.php';
require_once dirname( __FILE__ ).'/social_functions.php';
require_once dirname( __FILE__ ).'/twitter.php';
require_once dirname( __FILE__ ).'/facebook.php';
require_once dirname( __FILE__ ).'/google.php';
require_once dirname(__FILE__).'/class_fre_social.php';
require_once dirname( __FILE__ ).'/new_google.php';
require_once dirname( __FILE__ ).'/linkedin.php';
require_once dirname( __FILE__ ).'/settings.php';
require_once dirname( __FILE__ ).'/template.php';

function temp_social_debug(){
	$role = get_social_role();

	$del = isset($_GET['del']);
	if($del){
		global $wpdb;
		$sql = "SELECT * FROM $wpdb->users u LEFT JOIN $wpdb->usermeta
		ON u.ID = m.user_id
		WHERE m.meta_key = 'et_google_id' and m.meta_value IS NOT NULL";
		$gusers = $wpdb->get_results($sql);
		//wp_delete_user($user_id);

		$file_store = WP_CONTENT_DIR.'/uploads/et_log.css';
		echo '<a target="_blank" href="'.home_url().'/wp-content/uploads/et_log.css">Log File </a><br /> ..<br />';
	}
}
add_action('wp_footer','temp_social_debug');