<?php

function insert_membership_free_1week($pack){

	global $wpdb, $user_ID;
	$member 	= get_user_by('login','memberweek');

	if(!$member){
		$args = array(
			'user_login' =>'memberweek',
			'role' => FREELANCER,
			'user_email' =>'memberweek@mailinator.com',
			'user_pass' => '1'
		);
		wp_insert_user($args);
	}
	$membership = $wpdb->prefix . 'membership';
	$pack_type 	= $pack->post_type;
	$sku 		= $pack->sku;
	$string = "+5 days";

	$expiry_time = strtotime($string);
	$expiry_date = date( 'Y-m-d h:i:s', $expiry_time );
	$number_posts = (int) $pack->et_number_posts-1;
	$order_status = 'publish';

	$payer_id = $member->ID;
	$payer_info = get_userdata($payer_id);
	$is_membership = is_exists_membership_record($pack_type, $payer_id);

	if( ! $is_membership ){
		$wpdb->insert(
			$membership,
			array(
				'user_id' => $payer_id,
				'user_email' => $payer_info->user_email,
				'user_login' => $payer_info->user_login ,
				'since_date' => current_time( 'mysql' ),
				'pack_type' => $pack_type,
				'pack_sku' => $sku,
				'expiry_time' =>  $expiry_time,
				'expiry_date' => $expiry_date,
				'is_sent_mail' => 0,
				'remain_posts' =>$number_posts,
				'order_status' => $order_status, // $pending      = ae_get_option( 'use_pending', false );
				'auto_renew' => 'active',
			)
		);
	} else {
		$sql_update = $wpdb->prepare(
			"
			UPDATE $membership
			SET expiry_time = %d,
			expiry_date = %s,
			is_sent_mail = 0,
			pack_sku = %s,
			order_status = %s,
			auto_renew = %s
			WHERE id = %d
			",
		    $expiry_time, $expiry_date, $sku, $order_status,'active', $is_membership->id
		);
		$wpdb->query($sql_update );
	}
}

function insert_membership_free_today($pack){

	global $wpdb;
	$member 	= get_user_by('login','membertoday');
	if(!$member || is_wp_error($member)){
		$args = array(
			'user_login' => 'membertoday',
			'user_email' => 'membertoday@mailinator.com',
			'user_pass' => '1',
			'role' => FREELANCER,
		);
		$member =wp_insert_user($args);
	}
	$time = time() + 5000;
	$membership = $wpdb->prefix . 'membership';
	$pack_type 	= $pack->post_type;
	$sku 		= $pack->sku;
	$expiry_time = $time;
	$expiry_date = date( 'Y-m-d h:i:s', $expiry_time );
	$number_posts = (int) $pack->et_number_posts-1;
	$order_status = 'publish';
	$payer_id = $member->ID;
	$payer_info = get_userdata($payer_id);
	$is_membership = is_exists_membership_record($pack_type, $payer_id);

	if( ! $is_membership ){
		$wpdb->insert(
			$membership,
			array(
				'user_id' => $payer_id,
				'user_email' => $payer_info->user_email,
				'user_login' => $payer_info->user_login ,
				'since_date' => current_time( 'mysql' ),
				'pack_type' => $pack_type,
				'pack_sku' => $sku,
				'expiry_time' =>  $expiry_time,
				'expiry_date' => $expiry_date,
				'is_sent_mail' => 0,
				'order_status' => $order_status, // $pending      = ae_get_option( 'use_pending', false );
				'auto_renew' => 'active',
			)
		);
	} else {
		$sql_update = $wpdb->prepare(
			"
			UPDATE $membership
			SET expiry_time = %d,
			expiry_date = %s,
			is_sent_mail = 0,
			order_status = %s,
			auto_renew = %s
			WHERE id = %d
			",
		    $expiry_time,$expiry_date, $order_status, $number_posts, 'active', $is_membership->id
		);

		$wpdb->query($sql_update );
	}
}


function fre_membership_del_sample_data_test(){
	global $wpdb;
	$tbl_membership = $wpdb->prefix . 'membership';
	$sql = "DELETE  FROM $tbl_membership
	        WHERE user_email  IN ('ettoday@mailinator.com',
				'ettoday1@mailinator.com',
				'ettoday2@mailinator.com',
				'etnextday@mailinator.com',
				'etnext2day@mailinator.com',
				'etnext3day@mailinator.com',
				'etnext4day@mailinator.com',
				'etnext5day@mailinator.com')";

	$wpdb->query($sql);
}

function fre_member_add_sample_membership(){
	if(isset($_GET['reset'])){
		$id = $_GET['reset'];
		//update_a_membership_expiration($id);
	}

	if(! current_user_can('manage_options')){
		return;

	}
	$add_ms = isset($_GET['add_ms']) ? $_GET['add_ms'] : 0;
	if($add_ms){
		$sku = 'bid1month';
		$pack = AE_Package::get_instance()->get($sku);
		insert_membership_free_1week($pack);
		insert_membership_free_today($pack);
	}
	$del_ms = isset($_GET['del_ms']) ? $_GET['del_ms'] : 0;
	if($del_ms){
		fre_membership_del_sample_data_test();
	}

}
function update_a_membership_expiration($id){

	global $wpdb;
	$membership = $wpdb->prefix . 'membership';
	$today = time() + 5000;
	$sql_update = $wpdb->prepare(
			"
			UPDATE $membership
			SET expiry_time = %d,
			expiry_date = %s,

			is_sent_mail = 0,
			pack_sku = %s,
			order_status = %s,
			auto_renew = %s
			WHERE id = %d
			",
		    $today, current_time( 'mysql' ), 'bid1month', 'publish','active', $id
		);
	$wpdb->query($sql_update );
	var_dump('qa done');
}

add_action('wp_footer','fre_member_add_sample_membership');