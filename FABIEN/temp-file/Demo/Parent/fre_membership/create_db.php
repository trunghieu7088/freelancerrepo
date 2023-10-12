<?php
global $membership_version;

$membership_version = '1.0';
function fre_member_dropdb(){
	global $wpdb;
	$tbl_order 			= $wpdb->prefix . 'membership_order';
	$tbl_member 		= $wpdb->prefix . 'fre_membership';
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';
    $wpdb->query( "DROP TABLE IF EXISTS $tbl_order" );
    $wpdb->query( "DROP TABLE IF EXISTS $tbl_member" );
    $wpdb->query( "DROP TABLE IF EXISTS $tbl_subscriptions" );
}
function fre_membership_install() {

	global $wpdb, $membership_version;
	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$tbl_member = $wpdb->prefix . 'fre_membership';
	$sql = "CREATE TABLE $tbl_member (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id mediumint(9) NOT NULL ,
		user_login varchar(55) NOT NULL ,
		user_email varchar(55) NOT NULL ,
		subscr_id  int(11) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );

	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';
	$sql = "CREATE TABLE $tbl_subscriptions (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id  int(11) NOT NULL,
		plan_sku varchar(55) NOT NULL ,
		pack_type varchar(55) NOT NULL ,
		api_subscr_id varchar(55) NOT NULL ,
		price float(11) NOT NULL,
		currency varchar(10) NOT NULL ,
		remain_posts int(11) NOT NULL,
		start_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		expiry_time  int(11) NOT NULL,
		expiration_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		payment_gw varchar(55) NOT NULL ,
		payment_status varchar(15) NOT NULL ,
		auto_renew varchar(15) NOT NULL ,
		is_sent_mail  int(11) NOT NULL,
		test_mode int(11) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $sql );
	add_option( 'membership_version', $membership_version );


}
/**
 * Only have in the new version.
 * Call this method when singup new subscriber or re-new.
 * No call when expired or reduce_posts left
 * @since 2.0
**/
function fre_save_membership($args) {

	global $wpdb;
	$tbl_member = $wpdb->prefix . 'fre_membership';
	$user_id 	= $args->user_id;
	$sql_check = $wpdb->prepare( " SELECT *  FROM {$tbl_member}  WHERE user_id = %d", $user_id );
	$record 	= $wpdb->get_row($sql_check, OBJECT);
	if( !$record ){
		$in_args = array(
			'user_id' 		=> $args->user_id,
			'user_email' 	=> $args->user_email,
			'user_login' 	=> $args->user_login,
			'subscr_id' 	=> $args->subscr_id,
		);
		$insert = $wpdb->insert( $tbl_member, $in_args );
		if(!$insert){
			et_member_log("fre_save_membership Insert Fail:");
			et_member_log($wpdb->last_error);
		}

	} else { // update_only run when re-new or change subscriber.
		$up_args = array(
			'subscr_id' 	=> (int) $args->subscr_id,
		);
		$result = $wpdb->update( $tbl_member, $up_args, array('id'=>$record->id) );
		if( !$result){
			et_member_log("fre_save_membership Update Fail");
			et_member_log($wpdb->last_error);

		}
	}
	do_action('after_save_membership', $user_id, $args); // send mail
}

function save_free_subscription($user_id, $pack, $test_mode){

	$string = "+1 month";
	if( (int) $pack->et_subscription_time > 1 ){
		$string = "+{$pack->et_subscription_time} months";
	}
	$expiry_time = strtotime($string);

	$args = array(
		'user_id' 			=> $user_id,
		'plan_sku' 			=> $pack->sku,
		'price' 			=> 0,
		'currency' 			=> fre_get_currency_code(),
		'api_subscr_id' 	=> 'free',
		'remain_posts' 		=> $pack->et_number_posts,
		'expiry_time' 		=> $expiry_time,
		'payment_gw' 		=> 'free',
		'pack_type' 		=> $pack->post_type,
		'payment_status' 	=> 'completed', // paid
		'test_mode' 		=> $test_mode,
	);

	$args 		= (object)$args;
	$user 	  	= get_userdata($user_id);
	$subscr_id 	= (int) fre_mebership_save_subscrition($args);
	if($subscr_id > 0){
		$m_args = array(
			'user_id' 		=> $user_id,
			'user_email'	=> $user->user_email,
			'user_login' 	=> $user->user_login,
			'subscr_id' 	=> $subscr_id,
		);
		$m_args  =(object)$m_args;
		fre_save_membership( $m_args );
	}

}

function fre_update_sub_id_of_member($subscr_id, $user_id){
	global $wpdb;
	$tbl_member = $wpdb->prefix . 'fre_membership';
	$up_args = array(
			'subscr_id' 	=> $subscr_id
	);
	$wpdb->update( $tbl_member, $up_args, array('user_id'=>$user_id) );
}
/**
 * new version
 **/
function fre_mebership_save_subscrition($args){
	global $wpdb;
	//$membership_order = $wpdb->prefix . 'membership_order';
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

	$expiration_date = date("Y-m-d H:i:s", $args->expiry_time);
	$pack_type = $args->pack_type;
	if( empty($pack_type) ){
		$pack_type = get_pack_type_of_user($args->user_id);
	}
	$insert = $wpdb->insert(
		$tbl_subscriptions,
		array(
			'user_id' 			=> $args->user_id,
			'plan_sku' 			=> $args->plan_sku,
			'pack_type' 		=> $pack_type,
			'api_subscr_id' 	=> $args->api_subscr_id,
			'price' 			=> $args->price,
			'currency' 			=> $args->currency,
			'start_date' 		=> current_time( 'mysql' ),
			'expiry_time' 		=> $args->expiry_time,
			'expiration_date'	=> $expiration_date,
			'payment_gw'		=> $args->payment_gw,
			'payment_status' 	=> 'completed',
			'remain_posts' 		=> $args->remain_posts,
			'auto_renew' 		=> 'active',
			'is_sent_mail' 	    => 0,
			'test_mode' 		=> $args->test_mode,
		)
	);

	if( $wpdb->insert_id ){
		
		//custom code here
		$custom_options=get_option('et_options');
		$plan_array=array( 'post_type'=>'bid_plan',
							'numberposts' =>1,
							'post_status' =>'publish',
							 'meta_query' => array(
					                'relation' => 'AND',
					                array(                    
					                'key'     => 'sku',                
					                'compare' => '=',
					                'value' =>$args->plan_sku
					                ),
							)
						);
		$rank_point_of_plan=get_posts($plan_array);
		$rank_plan_id=$rank_point_of_plan[0]->ID;
		$user_profile_id=get_user_meta($args->user_id,'user_profile_id',true);
		$current_point=get_user_meta($args->user_id,'custom_rank_point',true);
		$new_point=$current_point+$custom_options["subcription_rank_condition_$rank_plan_id"];
		$datetime_rank_update = date('Y-m-d H:i:s'); 
		
		update_user_meta($args->user_id,'custom_rank_point',$new_point);	
		update_user_meta($args->user_id,'custom_rank_update_time',$datetime_rank_update);	

		update_post_meta($user_profile_id,'custom_rank_point',$new_point);
		update_post_meta($user_profile_id,'custom_rank_update_time',$datetime_rank_update);

		//end

		return $wpdb->insert_id;
	}
	et_member_log('fre_mebership_save_subscrition  Fail:');
	et_member_log($wpdb->last_error);

	return $wpdb->last_error;
}



function upgrade_membership_table(){
	global $wpdb;

	$tbl_member 		= $wpdb->prefix . 'fre_membership';
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

	//$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_member}'  AND COLUMN_NAME = 'remain_posts'";
	//$has_col_rmp = $wpdb->get_results($sql);

	//if( !$has_col_rmp ){
	   // $wpdb->query("ALTER TABLE {$membership_tbl} ADD remain_posts int(11) NOT NULL ");
	//}
	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_member}'  AND COLUMN_NAME = 'pack_type'";
	$has_col_type = $wpdb->get_results($sql);
	if( !$has_col_type ){
		// $wpdb->query("ALTER TABLE {$membership_tbl} ADD pack_type varchar(11) NOT NULL ");
	}

	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_subscriptions}'  AND COLUMN_NAME = 'plan_sku'";
	$exist = $wpdb->get_results($sql);
	if(!$exist){
		$wpdb->query("ALTER TABLE {$tbl_subscriptions} ADD plan_sku varchar(11) NOT NULL ");
	}
	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_subscriptions}'  AND COLUMN_NAME = 'pack_type'";
	$exist = $wpdb->get_results($sql);
	if(!$exist){
		$wpdb->query("ALTER TABLE {$tbl_subscriptions} ADD pack_type varchar(11) NOT NULL ");
	}

	// $wpdb->query("ALTER TABLE {$tbl_member} MODIFY  user_login varchar(55) NOT NULL");

	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_member}'  AND COLUMN_NAME = 'user_login'";
	$exist = $wpdb->get_results($sql);
	if(!$exist){
		$wpdb->query("ALTER TABLE {$tbl_member} ADD user_login varchar(11) NOT NULL ");
	}
	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_subscriptions}'  AND COLUMN_NAME = 'expiration_date'";
	$exist = $wpdb->get_results($sql);
	if(!$exist){
		$wpdb->query("ALTER TABLE {$tbl_subscriptions} ADD expiration_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL ");
	}

}

function insert_membership($order_data, $pack_type) {

	global $wpdb;
	$membership = $wpdb->prefix . 'membership';
	$sku = $order_data['payment_package'];

	$payer_id = $order_data['payer'];
	$order_status = $order_data['status'];

	$payer_info = get_userdata($payer_id);

	$obj_pack 	= AE_Package::get_instance();
	$pack  		= $obj_pack->get_pack( $sku, $pack_type );
	$remain_posts = (int)$pack->et_number_posts;


	$string = "+1 month";
	if( (int) $pack->et_subscription_time > 1 )
		$string = "+{$pack->et_subscription_time} months";

	$expiry_time = strtotime($string);
	$expiry_date = date( 'Y-m-d h:i:s', $expiry_time );

	$is_membership = is_exists_membership_record( $payer_id);

	$insert = $wpdb->insert(
			$membership,
			array(
				'user_id' => $payer_id,
				'user_email' => $payer_info->user_email,
				'user_login' => $payer_info->user_login ,
				'pack_sku' => $sku,
				'since_date' => current_time( 'mysql' ),
				'expiry_time' =>  $expiry_time,
				'expiry_date' => $expiry_date,
				'is_sent_mail' => 0,
				'remain_posts' =>$remain_posts,
				'order_status' => $order_status,
				'auto_renew' => 'active',
			)
		);

}
/**
 * check and get the correct pack_type of current user.
 **/
function get_pack_type_of_user($user_id){

	$pack_type = 'pack';
	// if( ! is_user_logged_in() ) return 'bid_plan'; // wrong important
	$user_role = ae_user_role( $user_id );
	//if( current_user_can('manage_options') || in_array($user_role,array('administrator','employer') ) ){ // 2 conditions
	if( $user_role == 'freelancer' ){ // 2 conditions
		return 'bid_plan';
	}
	return $pack_type;
}

/**
 * Check and  redurect 1 post of remain_post of current user. Call this method after bid or after post_project done.
 * @since new_version
 * @author: danng
 * @return: void.
 **/
function fre_reduce_post_left_of_current_user($subscription){
	global $wpdb;
	if( !$subscription ){
		return 0;
	}

	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

	$remain_posts = (int) $subscription->remain_posts - 1;
	$remain_posts = max($remain_posts, 0);
	$sql_update = $wpdb->prepare(
		"
		UPDATE $tbl_subscriptions
		SET remain_posts = %d
		WHERE id = %d
		",
	    $remain_posts, $subscription->subscr_id
	);

	$wpdb->query($sql_update );

}
function update_auto_renew_success($member, $pack){

	global $wpdb ;
	$user_id = $member->user_id;
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

    $string = "+1 month";
	if( (int) $pack->et_subscription_time > 1 )
		$string = "+{$pack->et_subscription_time} months";

	$expiry_time 	= strtotime($string);

	$args = array(
		'user_id' 			=> $user_id,
		'plan_sku' 			=> $member->plan_sku,
		'pack_type' 		=> $member->pack_type,
		'price' 			=> $pack->et_price,
		'currency' 			=> fre_get_currency_code(),
		'api_subscr_id' 	=> 'fre_credit',
		'remain_posts' 		=> $pack->et_number_posts,
		'expiry_time' 		=> $expiry_time,
		'payment_gw' 		=> 'fre_credit',
		'payment_status' 	=> 'paid', // paid
		'test_mode' 		=> $member->test_mode,
	);

	$args 		= (object)$args;

	$new_subscr_id 	= fre_mebership_save_subscrition($args);
	$m_args = array(
		'user_id' 		=> $user_id,
		'user_email'	=> $member->user_email,
		'user_login' 	=> $member->user_login,
		'subscr_id' 	=> $new_subscr_id,
	);
	$m_args  =(object)$m_args;
	fre_save_membership( $m_args );

	// update status of old subscription here.
	update_auto_renew_status('disable', $member->subscr_id);

	do_action('after_auto_renew_success', $member, $pack);

}

/**
 * No use anymore.
 **/
function update_auto_renew_fail($subscriber){

	global $wpdb ;

	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';
	$sql_update = $wpdb->prepare(
		"
		UPDATE $tbl_subscriptions
		SET
			is_sent_mail = %d,
			auto_renew = %s
		WHERE id = %d
		",
	     1,'cancel', $subscriber->subscr_id
	);
	$wpdb->query($sql_update );

}

/**
 * Run this method when post a peoject free. Unsure[No for buy a bid].
**/


function insert_membership_free($pack){

	global $wpdb, $user_ID;
	$membership = $wpdb->prefix . 'membership';
	$pack_type 	= $pack->post_type;
	$sku 		= $pack->sku;


	$string = "+1 month";
	if( (int) $pack->et_subscription_time > 1 )
		$string = "+{$pack->et_subscription_time} months";
	$expiry_time = strtotime($string);
	$expiry_date = date( 'Y-m-d h:i:s', $expiry_time );
	$number_posts = (int) $pack->et_number_posts-1;
	$order_status = 'publish';
	$payer_id = $user_ID;
	$payer_info = get_userdata($payer_id);
	$wpdb->insert(
		$membership,
		array(
			'user_id' => $payer_id,
			'user_email' => $payer_info->user_email,
			'user_login' => $payer_info->user_login ,
			'since_date' => current_time( 'mysql' ),

			'pack_sku' => $sku,
			'expiry_time' =>  $expiry_time,
			'expiry_date' => $expiry_date,
			'is_sent_mail' => 0,
			'remain_posts' =>$number_posts,
			'order_status' => $order_status, // $pending      = ae_get_option( 'use_pending', false );
			'auto_renew' => 'active',
		)
	);

}

// remain post project, not bid project
function update_remain_post($member){

	global $wpdb;
	$membership = $wpdb->prefix . 'membership';

	$sql_update = $wpdb->prepare(
		"
		UPDATE $membership
		SET remain_posts = remain_posts-1

		WHERE id = %d
		",
		 $member->user_id
	);
	$wpdb->query($sql_update );

}
function is_exists_membership_record( $user_id = '', $sku = ''){
	global $wpdb, $user_ID;
	if( empty($user_id))
		$user_id = $user_ID;
	$now = time();
	$membership = $wpdb->prefix . 'membership';
	$sql = $wpdb->prepare("SELECT * FROM $membership WHERE user_id = %d  AND expiry_time > {$now} AND remain_posts > 0 AND order_status = %s ", $user_id,'publish');

	if( !empty($sku) ){
		$sql = $wpdb->prepare("SELECT * FROM $membership WHERE user_id = %d  AND expiry_time > {$now} AND remain_posts > 0 AND order_status = %s AND pack_sku = %s", $user_id,'publish', $sku);
	}
	$row = $wpdb->get_row($sql);
	if ( null !== $row ) {
		return $row;
	}
	return false;
}


function is_exists_membership_vimprove( $user_id ){
	global $wpdb, $user_ID;
	if( empty($user_id))
		$user_id = $user_ID;
	$now = time();
	$membership = $wpdb->prefix . 'membership';
	$sql = $wpdb->prepare("SELECT * FROM $membership WHERE user_id = %d  ", $user_id);


	$row = $wpdb->get_row($sql);
	if ( null !== $row ) {
		return $row;
	}
	return false;
}

function is_exist_pack_free( $pack, $user_id = ''){
	global $wpdb, $user_ID;
	$pack_type = 'pack';
	if( empty($user_id))
		$user_id = $user_ID;
	$now = time();
	$membership = $wpdb->prefix . 'membership';
	$sql = $wpdb->prepare("SELECT * FROM $membership WHERE user_id = %d AND expiry_time > {$now}  AND pack_type = %s ", $user_id, $pack_type);

	$row = $wpdb->get_row($sql);
	return $row;
}


function del_subscription_of_current_user(){
	global $wpdb, $user_ID;
	$tbl_membership = $wpdb->prefix . 'membership';
	$sql = "DELETE FROM {$tbl_membership} WHERE user_id = {$user_ID}";
	$wpdb->query($sql);
}
function del_all_membership(){
	global $wpdb, $user_ID;
	$tbl_membership = $wpdb->prefix . 'membership';
	$sql = "DELETE FROM {$tbl_membership} ";
	$wpdb->query($sql);
}