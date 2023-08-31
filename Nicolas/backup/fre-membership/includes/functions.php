<?php

/**
 * get pack detail of the plan;
 **/
function fre_get_pack_detail($sku, $pack_type){

	$packs = AE_Package::get_instance();
	$pack  = $packs->get_pack( $sku, $pack_type );

	if( !$pack ) return false;

	$enable_stripe 	= ae_get_option('mebership_stripe', false);

	if($enable_stripe){
		$pack->stripe_pricing_id = isset($pack->stripe_pricing_id) ? $pack->stripe_pricing_id : 0;
	} else{
		$pack->stripe_pricing_id = 0;
	}

	return $pack;
}
function get_subscribe_price($price){
	return apply_filters('cal_subscribe_price', $price);
}
function membership_get_pack($sku = '', $pack_type = ''){

		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT * FROM $wpdb->posts p INNER JOIN $wpdb->postmeta m
			ON p.ID = m.post_id
			WHERE p.post_type = %s AND m.meta_key = 'sku' AND m.meta_value = %s LIMIT 1 ",
			$pack_type, $sku );
		$package = $wpdb->get_row($sql, ARRAY_A);

		if( !$package ||  is_null($package) ){
			return false;
		}
		$package['stripe_pricing_id'] = 0;
		$pack_id = $package['ID'];
		$meta_sql = $wpdb->prepare(
			"SELECT * FROM  $wpdb->postmeta m
			WHERE m.post_id = %d ",
			$pack_id
		);
		$metas = $wpdb->get_results($meta_sql);
		if($metas){
			foreach($metas as $meta){
				$package[$meta->meta_key] = $meta->meta_value;
			}
		}
 	return (object) $package;
}

function fre_get_paypal_membership_api(){
	$paypal_api 		= ae_get_option('membership_paypal_api'); //lab@etlab.com
	$paypal_business 	= isset($paypal_api['paypal_business']) ? trim($paypal_api['paypal_business']) : '';
	$secret_key 		= isset($paypal_api['secret_key']) ? trim($paypal_api['secret_key']) : '';
	$client_id 			= isset($paypal_api['client_id']) ? trim($paypal_api['client_id']) : '';
	//$client_id 			= 'AWeXnxRnSBh7sO4BIp8RDMaaDwi5_HOsp6LHkNk3ai1syOAD5NA7Fgss53IwgFPjvV4pbYbhAYIgcR3A';
	//$secret_key 		= 'EM08bS6Ghxtxg_1PJeVxBGfo88wzD9uQTe47MNVJutbwCXKGarlez4SsnWg3ODPQOMYYCkCLf-JzjGjO';
	$testmode = 	(boolean) ae_get_option('membership_mode', true);
	if($testmode){
		$paypal_business 	= isset($paypal_api['test_paypal_business']) ? trim($paypal_api['test_paypal_business']) : 'lab@etlab.com';
		$secret_key 		= isset($paypal_api['test_secret_key']) ? trim($paypal_api['test_secret_key']) : '';
		$client_id 			= isset($paypal_api['test_client_id']) ? trim($paypal_api['test_client_id']) : '';
	}

	return (object) array('paypal_business' => $paypal_business, 'client_id' => $client_id, 'secret_key' => $secret_key);
}


function get_plan_free($pack_type){


	$args = array(
	    'post_type'  	=> $pack_type,
	    'meta_key'   	=> 'et_price',
	    'post_status' 	=> 'publish',
	    'meta_value' 	=> 0,
	    'posts_per_page' => 1,

	);

	$query = new WP_Query( $args );

	if(  !empty($query->posts) ){
		return $query->posts[0];
	}

	return false;
}

function fre_get_stripe_membership_api(){

	$stripe_api  			= ae_get_option('membership_stripe_api');
	$live_public_key 		= isset($stripe_api['live_publishable_key']) ? trim($stripe_api['live_publishable_key']) : '';
	$live_secret_key 		= isset($stripe_api['live_secret_key']) ? trim($stripe_api['live_secret_key']) : '';
	$test_publishable_key 	= isset($stripe_api['test_publishable_key']) ? trim($stripe_api['test_publishable_key']) :'';
	$test_secret_key 		= isset($stripe_api['test_secret_key']) ? trim($stripe_api['test_secret_key']) : '';
	$live_signing_secret 	= isset($stripe_api['live_signing_secret']) ? trim($stripe_api['live_signing_secret']) : '';
	$test_signing_secret 	= isset($stripe_api['test_signing_secret']) ? trim($stripe_api['test_signing_secret']) : '';

	$testmode = 	(boolean) ae_get_option('membership_mode', true);

	$config = array(
		'publishable_key' 	=> $test_publishable_key,
		'secret_key' 		=> $test_secret_key,
		'signing_secret' 	=> $test_signing_secret,
	);
	if( !$testmode ){
		$config = array(
			'publishable_key' 	=> $live_public_key,
			'secret_key' 		=> $live_secret_key,
			'signing_secret' 	=> $live_signing_secret,
		);
	}
	return (object)$config;
}
/**
 Show total post avaibale of this user.
*/
 function fre_get_membershipplan($sku){
 	global $wpdb;
 	$sql = "SELECT
	  p.*
	FROM  $wpdb->posts p
	LEFT JOIN  $wpdb->postmeta m
	  ON p.ID = m.post_id
	WHERE 1=1

	AND m.meta_key = 'sku'
	AND m.meta_value = '{$sku}'
	ORDER BY p.post_date DESC LIMIT 1";

	$result = $wpdb->get_results($sql);
	if($result)
		return $result[0];
	return $result;

 }
  function fre_get_plan_by_sql($sku){
 	global $wpdb;
 	$sql = "SELECT
	  p.*
	FROM  $wpdb->posts p
	LEFT JOIN  $wpdb->postmeta m
	  ON p.ID = m.post_id
	WHERE 1=1

	AND m.meta_key = 'sku'
	AND m.meta_value = '{$sku}'
	ORDER BY p.post_date DESC LIMIT 1";

	$result = $wpdb->get_row($sql, ARRAY_A);
	if( ! $result )
		return false;
	if($result){
		$result['et_number_posts'] 		= get_post_meta($result['ID'],'et_number_posts', true);
		$result['et_price'] 			= get_post_meta($result['ID'],'et_price', true);
		$result['et_subscription_time'] = get_post_meta($result['ID'],'et_subscription_time', true);

	}
	return (object) $result;

 }

if(! function_exists('fre_membership_package_info') ){
	function fre_membership_package_info(){
		global $wpdb, $user_ID;
		$table = $wpdb->prefix . 'membership';
		$now = strtotime('now');
		$sql =  $wpdb->prepare(
			"
				SELECT *
				FROM $table
				WHERE expiry_time > $now AND user_id =  %d
			",
			$user_ID );

		$packs=  $wpdb->get_results( $sql );


		$remain_posts = 0;
		foreach ($packs as $pack) {
			$remain_posts += $pack->remain_posts;
		}

		return 0;
	}
}
function et_paypal_log($input, $file_store = FALSE){

	$file_store = WP_CONTENT_DIR.'/paypal_log.css';

	if( is_array( $input ) || is_object( $input ) ){
		error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. print_r($input, TRUE), 3, $file_store );
	} else {
		error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. $input . "\n" , 3, $file_store);
	}

}
function et_member_log($input, $file_store = FALSE){

	if( !$file_store)  $file_store = MEMBERSHIP_LOG;

	if( MEMBERSHIP_LOG ){
		if( is_array( $input ) || is_object( $input ) ){
			error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. print_r($input, TRUE), 3, $file_store );
		} else {
			error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. $input . "\n" , 3, $file_store);
		}
	}
}
function et_cron_log($input){

	$file_store = WP_CONTENT_DIR.'/cron_log.css';
	$home_url 	= home_url();
	if ( strpos($home_url, "enginethemes.com")!==false){
		 $file_store = ET_CRON_PATH;
	}

	if( MEMBERSHIP_LOG ){
		if( is_array( $input ) || is_object( $input ) ){
			error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. print_r($input, TRUE), 3, $file_store );
		} else {
			error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. $input . "\n" , 3, $file_store);
		}
	}
}


function fre_membership_get_available_pack(){
	global $user_ID, $wpdb;
	$table = $wpdb->prefix . 'membership';
	$now = strtotime('now');
	$sql =  $wpdb->prepare(
		"
			SELECT tbl.*, tbl.plan_sku as sku
			FROM $table tbl
			WHERE expiry_time > $now   and user_id = %d
			ORDER BY id ASC
		", $user_ID
	);

	$packs = $wpdb->get_results($sql);
	$used_package = array();
	foreach ($packs as $key => $pack) {
		//$used_package[$pack->plan_sku] = $pack;
		$used_package[$pack->plan_sku]['ID'] = $pack->plan_sku;
		$used_package[$pack->plan_sku]['qty'] = (int) $pack->remain_posts; // should count again.

	}
	return  $used_package ;
}


function filter_membership_notification($content, $notify, $type){

	if( empty($notify->post_excerpt) ){
		return $content;
	}
	$post_excerpt = str_replace( '&amp;', '&', $notify->post_excerpt );
	$obj_output = array();
	if( ! empty( $post_excerpt) ){
		parse_str( $post_excerpt, $obj_output );
	}
	if($type = 'membership'){
		$notify_link = et_get_page_link('profile');

		$sub_detail = isset($obj_output['sub_detail']) ? $obj_output['sub_detail']: '';

		if( $sub_detail == 'expiry_soon' ){
			$message =__('Your subscription will expire soon','enginethemes');
			$content = '<a class="fre-notify-wrap membership-sub-'.$sub_detail.'" href="'.esc_url($notify_link).'">
	            <span class="notify-avatar"><span class="noti-icon"><i class="fa fa-info-circle"></i></span></span>
	            <span class="notify-info">' . $message . '</span>
	            <span class="notify-time">' . sprintf( __( "%s on %s", 'enginethemes' ), get_the_time( '', $notify->ID ), get_the_date( '', $notify->ID ) ) . '</span>
	        </a>';
		}
		if($sub_detail == 'renew_success'){
			$message =__('Your subscription has been renewed successful','enginethemes');

			$content = '<a class="fre-notify-wrap membership-sub-'.$sub_detail.'"  href="'.esc_url($notify_link).'">
	            <span class="notify-avatar"><span class="noti-icon"><i class="fa fa-info-circle"></i></span></span>
	            <span class="notify-info">' . $message . '</span>
	            <span class="notify-time">' . sprintf( __( "%s on %s", 'enginethemes' ), get_the_time( '', $notify->ID ), get_the_date( '', $notify->ID ) ) . '</span>
	        </a>';
		}
		if( $sub_detail == 'renew_fail' ){
			$message =__('Can not auto renew your subscription','enginethemes');
			$content = '<a class="fre-notify-wrap membership-sub-'.$sub_detail.'"   href="'.esc_url($notify_link).'">
	            <span class="notify-avatar"><span class="noti-icon"><i class="fa fa-info-circle"></i></span></span>
	            <span class="notify-info">' . $message . '</span>
	            <span class="notify-time">' . sprintf( __( "%s on %s", 'enginethemes' ), get_the_time( '', $notify->ID ), get_the_date( '', $notify->ID ) ) . '</span>
	        </a>';
		}

	}

	return $content;
}

add_filter( 'fre_notify_item','filter_membership_notification', 99 ,3);

function insert_notification_expiry_soon($member){
	et_member_log('inset notification for member ID: '.$member->user_id);
	$code = 'type=membership&sub_detail=expiry_soon'; //  expiry_soon

	$notification = array(
        'post_type'    => 'notify',
        'post_content' => $code,
        'post_excerpt' => $code,
        'post_author'  => $member->user_id,
        'post_title'   => __( "Your subscription will expire soon.", 'enginethemes' ),
        'post_status'  => 'publish',
    );
    Fre_Notification::getInstance()->insert($notification);
    $number = get_user_meta( $member->user_id, 'fre_new_notify', true );
	$number = $number + 1;
	update_user_meta( $member->user_id, 'fre_new_notify', $number );
}

function insert_notification_renew_success($member){
	$code = 'type=membership&sub_detail=renew_success'; //  renew_success

    $notification = array(
        'post_type'    => 'notify',
        'post_content' => $code,
        'post_excerpt' => $code,
        'post_author'  => $member->user_id,
        'post_title'   => __( "Your subscription has been renewed successful.", 'enginethemes' ),
        'post_status'  => 'publish',
    );
    Fre_Notification::getInstance()->insert($notification);
    $number = get_user_meta( $member->user_id, 'fre_new_notify', true );
	$number = $number + 1;
	update_user_meta( $member->user_id, 'fre_new_notify', $number );
}
function insert_notification_renew_fail($member){
	$code = 'type=membership&sub_detail=renew_fail';
    $notification = array(
        'post_type'    => 'notify',
        'post_content' => $code,
        'post_excerpt' => $code,
        'post_author'  => $member->user_id,
        'post_title'   => __( "Can not auto renew your subscription", 'enginethemes' ),
        'post_status'  => 'publish',
    );
    Fre_Notification::getInstance()->insert($notification);
    $number = get_user_meta( $member->user_id, 'fre_new_notify', true );
	$number = $number + 1;
	update_user_meta( $member->user_id, 'fre_new_notify', $number );
}

/**
 * In new version.
 **/

function update_auto_renew_status($status, $sub_id){

	global $wpdb;
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

	$sql_update = $wpdb->prepare(
		"
		UPDATE $tbl_subscriptions
		SET  auto_renew=%s
		WHERE  id = %d
		",$status, $sub_id
	);
	$wpdb->query($sql_update);
}


function cancel_member_ship($user_id = 0){

	global $wpdb, $user_ID;
	if(!$user_id) $user_id = $user_ID;

	$table = $wpdb->prefix . 'membership';
	$sql_update = $wpdb->prepare(
		"
		UPDATE $table
		SET  auto_renew=%s
		WHERE user_id = %d AND plan_sku = %s
		", 'cancel', $user_id, $plan_sku
	);
	$wpdb->query($sql_update);
}
function fre_memhership_template_part( $slug, $name = null, $args = array() ) {
	/**
	 * Fires before the specified template part file is loaded.
	 *
	 * The dynamic portion of the hook name, `$slug`, refers to the slug name
	 * for the generic template part.
	 *
	 * @since 3.0.0
	 * @since 5.5.0 The `$args` parameter was added.
	 *
	 * @param string      $slug The slug name for the generic template.
	 * @param string|null $name The name of the specialized template.
	 * @param array       $args Additional arguments passed to the template.
	 */
	do_action( "get_template_part_{$slug}", $slug, $name, $args );

	$templates = array();
	$name      = (string) $name;
	if ( '' !== $name ) {
		$templates[] = "{$slug}-{$name}.php";
	}

	$templates[] = "{$slug}.php";

	/**
	 * Fires before a template part is loaded.
	 *
	 * @since 5.2.0
	 * @since 5.5.0 The `$args` parameter was added.
	 *
	 * @param string   $slug      The slug name for the generic template.
	 * @param string   $name      The name of the specialized template.
	 * @param string[] $templates Array of template files to search for, in order.
	 * @param array    $args      Additional arguments passed to the template.
	 */
	do_action( 'get_template_part', $slug, $name, $templates, $args );

	if ( ! fre_membership_locate_template( $templates, true, false, $args ) ) {
		return false;
	}
}
function fre_membership_locate_template( $template_names, $load = false, $require_once = true, $args = array() ) {
	$located = '';
	foreach ( (array) $template_names as $template_name ) {
		if ( ! $template_name ) {
			continue;
		}
		if ( file_exists( FRE_MEMBERSHIP_PATH . '/' . $template_name ) ) {
			$located = FRE_MEMBERSHIP_PATH . '/' . $template_name;
			break;
		}
	}

	if ( $load && '' !== $located ) {
		load_template( $located, $require_once, $args );
	}

	return $located;
}
function membership_remove_log(){
	$paypal_log = WP_CONTENT_DIR.'/paypal_log.css';
	if( is_file($paypal_log)) unlink($paypal_log);

	$log_path = MEMBERSHIP_LOG;
	if( is_file($log_path))unlink($log_path);

	$cron_path = WP_CONTENT_DIR.'/cron_log.css';
	$home_url 	= home_url();
	if ( strpos($home_url, "enginethemes.com")!==false){
		 $cron_path = ET_CRON_PATH;
	}
	if( is_file($cron_path) ) unlink($cron_path);
}