<?php

// et_payment_package
// post_sync : use to check free or premium post.

function fre_membership_update_remain_post_of_pack($sku){
	global $user_ID, $wpdb;

	$table = $wpdb->prefix . 'membership';
	$sql_update = $wpdb->prepare(
			"
			UPDATE $table
			SET  remain_posts=remain_posts - 1
			WHERE pack_sku = %s
			",  $sku
		);

	$wpdb->query($sql_update);
}
function fre_membership_update_pack_quanlity($product){
	$sku = $product->et_payment_package;
	$author = $product->post_author;

	fre_membership_update_remain_post_of_pack($sku);
}
add_action('fre_update_pack_quantity', 'fre_membership_update_pack_quanlity', 99);


/**
 * This hook fire after 1 project has been inserted successful.
 * use this filter to show the next step ( choose gateway or redirect to detail porject without step choose payment gateways);
 * use this hook to insert 1 membership if the package is free.

*/
function fre_membership_check_package_or_free($response, $sku, $ad){

	global $user_ID, $wpdb;
	$table = $wpdb->prefix . 'membership';
	$package = AE_Package::get_instance()->get($sku);

	if ( $package->et_price == 0 ){
		wp_send_json(array('success' => false,'msg' => 'Please select other plan'));

		et_write_session( 'ad_id', $ad->ID );
		$response['success'] = true;
		$response['url']     = et_get_page_link( 'process-payment', array(
			'paymentType' => 'free'
		) );
	}

	$now = strtotime('now');
	$sql =  $wpdb->prepare(
		"
			SELECT tbl.*
			FROM $table tbl
			WHERE expiry_time > $now AND pack_sku =  %s  AND user_id = %d AND remain_posts > 0 AND order_status =%s
			ORDER BY id ASC
		",
	$sku, $user_ID, 'publish');

	$use_package = $wpdb->get_row($sql);

	if ( $use_package ) {
		et_member_log('user_pack available');
		et_write_session( 'ad_id', $ad->ID );
		$response['success'] = true;
		$response['url']     = et_get_page_link( 'process-payment', array(
			'paymentType' => 'usePackage'
		) );

	}

	// if ( $package->et_price == 0 ) {
	// 	$member = is_exist_pack_free($package);

	// 	if( ! $member ){
	// 		insert_membership_free($package);
	// 	} else {

	// 		if( (int) $member->remain_posts < 1 ){
	// 			$resp  = array('success' => false, 'msg' => __('You can not post a job via this pack.','enginethemes') );
	// 			wp_send_json($resp);
	// 		} else {
	// 			// deduct -1 number post available of this membership.
	// 			update_remain_post_free($member);
	// 		}
	// 	}
	// }
	return $response;
}
// add_filter('package_or_free','fre_membership_check_package_or_free', 99,3); //check_use_package

/**
 * if return true : check order and update quanlity of remain post.
*/
function fre_membership_check_pack($available, $ad){

	//$ad->post_author, $ad->et_payment_package
	$sku = $ad->et_payment_package;
	global $user_ID, $wpdb;
	$table = $wpdb->prefix . 'membership';
	$now = strtotime('now');
	$sql =  $wpdb->prepare(
		"
			SELECT tbl.*
			FROM $table tbl
			WHERE expiry_time > $now AND pack_sku =  %s AND user_id = %d
			ORDER BY id ASC
		",
		$sku,  $user_ID
	);

	$use_package = $wpdb->get_row($sql);

	if ( $use_package ) {
		return true;
	}
	return $available;
}
// add_filter('check_use_package','fre_membership_check_pack', 99 ,2);

function check_before_select_pack_free($result, $sku){
	global $wpdb, $user_ID;
	$table_name = $wpdb->prefix . 'fre_membership';
	$now = strtotime('now');

	$package = AE_Package::get_instance()->get($sku);
	if($package->et_price == 0){
		$sql =  $wpdb->prepare(
			"
				SELECT tbl.*
				FROM $table_name tbl
				WHERE expiry_time > $now AND pack_sku =  %s AND user_id = %d AND order_status = %s
				ORDER BY id ASC
			",
			$sku,  $user_ID, 'publish'
		);
		$field_name = 'remain_posts';

		$cond_sql = $wpdb->prepare( "SELECT {$field_name} FROM {$table_name} WHERE  user_id = %d and expiry_time > {$now} AND pack_sku =%s", $user_ID , $sku);
		$record = $wpdb->get_col( $cond_sql );
		if($record == null){
			return true;
		}
		$avaible_post = (int) $record['0'];
		if($avaible_post<1)
			return false;
		return true;
	}
	return true;
}
// add_filter('can_post_project_free','check_before_select_pack_free', 10 , 2);
function fre_get_remain_posts_of_package($sku, $user_id = 0){
	global $wpdb;
	if(!$user_id){
		global $user_ID;
		$user_id = $user_ID;
	}
	$table_name = $wpdb->prefix . 'membership';
	$sql= "SELECT remain_posts FROM $table_name WHERE user_id = {$user_id} AND pack_sku = '{$sku}'";

	$row = $wpdb->get_results($sql);
	return $row;
}