<?php

/**
 * define new method to easy understand the workflow of payment in the theme.
 * @since v1.8.19
 * @author danng.
 **/
function fre_create_draft_order($order_data, $plans){
	et_track_payment('START: CREATE 1 DRAFR ORDER');
	$project_id  = isset( $_POST['ID'] ) ? $_POST['ID'] : '';
	$packageID   = isset( $_POST['packageID'] ) ? $_POST['packageID'] : '';
	if( $packageID !== 'no_pack' ){

		if ( empty( $plans ) ) {
			wp_send_json( array(
				'success' => false,
				'msg'     => __( "There is no payment plan.", ET_DOMAIN )
			) );
		}
		foreach ( $plans as $key => $value ) {
			if ( $value->sku == $packageID ) {
				$plan_info = $value;
				break;
			}
		}
		$plan_info->post_id = $project_id;
		$plan_info->ID = $plan_info->sku;
		$ship = apply_filters( 'ae_payment_ship', array(), $order_data, $_POST );
	} else {
		$plan_info = array();
		$plan_info['ID'] = 'emptysku';
		$plan_info['post_id'] = $project_id;
		$plan_info['post_title'] = __('Deposited Credit',ET_DOMAIN);
		$plan_info['et_price'] = 0;
		$plan_info['post_content'] = 'Deposited Fix Credit For The Project: <i><a href="'.get_permalink($project_id).'">'.get_the_title($project_id).'</a></i>';
		$plan_info['post_type'] =  $_POST['packageType'];//'fre_credit_plan - fre_credit_fix;
		// use this hook  to count the amount of deposit credit( via bid or via input amount)
		$plan_info = apply_filters( 'fre_order_infor', $plan_info);
	}
	$ship 			= apply_filters( 'ae_payment_ship', array(), $order_data, $_POST );
	$order_data 	= apply_filters( 'ae_payment_order_data', $order_data, $_POST );// set version v2 in this.
	$order 			=  new AE_Order( $order_data, $ship );
	$order->add_product( (array) $plan_info );
	$order_arr 		= $order->generate_data_to_pay();
	$order_obj 		= (object) $order_arr;
	$order_id 		= $order_obj->ID;

	if( $packageID == 'no_pack' )  update_post_meta( $order_id,'order_type','fre_credit_fix' );

	// write session
	$order_version = isset($order_data['order_version']) ? $order_data['order_version'] : '';
	if($order_version !== 'v2'){
		et_write_session( 'order_id', $order_id ); //V2 no need this.
		et_write_session( 'ad_id', $project_id ); // V2 No need this
	} else {
		et_track_payment('V2 order.');
	}
	return $order;
}
/**
 * Clone a method fre_credit_process_payment(). The processing is the same but in a new flow.
 * @since 1.8.19
 **/
function fre_complete_deposit_payment($order){

	$gateway 	= $order->payment;  // cash, bitcoin, stripe, paypal
	$type 		= $order->type;
	$payer_id 	= $order->payer;
	et_track_payment('Start. fre_complete_deposit_payment with Type == '.$type);
	if( $type == 'fre_credit_fix') {
            $number_credit = $order->total;
            $default = array(
               "package_name" => 'Deposit Specific '.$number_credit.' Credits V2',
               "amount" => $number_credit, // amount in cents
               "currency" => fre_credit_get_payment_currency(),
               "destination" => '',
               "source_transaction" => '',
               "commission_fee"=> 0,
               "statement_descriptor" => '',
               "history_type"=> 'deposit',
               "post_title"=> 'Deposited',
               "payment" => $gateway,
               'post_author' => $payer_id, // no use user_ID,
            );
            //if( is_pmgw_v2($gateway) ){
            	et_track_payment('deposit fre_credit_fix DONE');
            	$instance = FRE_Credit_Users::getInstance();
					$instance->deposit($payer_id, $number_credit);
					$default['status'] = 'completed';

					$history_id = FRE_Credit_History()->saveHistory($default);
					wp_update_post(array(
						'ID' 				=> $order->ID,
					 	'post_parent' 	=> $history_id,
					 	'post_status' 	=> 'publish',
					));
            //}

    } else { //fre_credit_plan
		$sku 		= $order->payment_plan;
		$pack 	= fre_get_pack($sku,'fre_credit_plan');
		if($pack != false){
			$default = array(
				"package_name" => $pack->post_title .' V2' ,
				"amount" => $pack->et_number_posts, // amount in cents
				"currency" => fre_credit_get_payment_currency(),
				"destination" => '',
				"source_transaction" => '',
				"commission_fee"=> 0,
				"statement_descriptor" => '',
				"history_type"=> 'deposit',
				"post_title"=> 'Deposited',
				"payment" => $gateway,
				'post_author' => $payer_id, // no use user_ID,
			);


			if( $pack->et_number_posts > 0 ){

				et_track_payment('>>>>> ADD CREDIT to ballance of user. Method: fre_complete_deposit_payment()');
				$instance = FRE_Credit_Users::getInstance();
				$instance->deposit($payer_id, $pack->et_number_posts);
				$default['status'] = 'completed';
				$history_id = FRE_Credit_History()->saveHistory($default);
				wp_update_post(array(
					'ID' 				=> $order->ID,
				 	'post_parent' 	=> $history_id,
				 	'post_status' 	=> 'publish',
				));
				et_track_payment('change stauts or Order to publish. Order ID.'.$order->ID);
				et_track_payment('V2 Deposit fre_credit_plan DONE. Finish.');
			}
		}
	}
}

/** Get pack detail. The previous method cause some problem with caching when get via option_name.
 *
 ***/
function fre_get_pack($sku, $pack_type = 'pack'){
	global $wpdb;
	$sql = $wpdb->prepare("
		SELECT p.*, m.meta_value FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta m
				ON p.ID = m.post_id
				WHERE p.post_type = %s AND m.meta_key = 'sku'
					AND m.meta_value = %s",
				$pack_type, $sku
			);
	$pack 	= array();
	$result 	= $wpdb->get_row($sql, ARRAY_A);
	if( $result == NULL ) return 0;

	$pack 							= $result;
	$pack['ID'] 					= $result['meta_value'];
	$pack['sku'] 					= $result['meta_value'];
	$pack['et_number_posts'] 	= (int) get_post_meta($result['ID'], 'et_number_posts', true);
	return (object) $pack;
}
/**
 * Clone method fre_credit_setup_payment()  freelanceengine\includes\aecore\class-ae-payments.php
 * Process after user purchase package + submit project.
 * Method fre_credit_setup_payment() and  process_payment() are the same same flow and action.
 * @since  1.8.19
**/
function fre_purchase_pack_complete($order){
	et_track_payment('EMP_purchase_pack_complete START');
	$project_id 	= $order->product_id;
	$sku 				= $order->payment_package;
	$pending_post 	= ae_get_option('use_pending', false);
	if( current_user_can(  'manage_options' ) ){
		$pending_post = 0;
	}
	$post_status = $pending_post ? 'pending' : 'publish';

	et_track_payment('START Update project status & order-status. Project_id:'.$project_id);
	wp_update_post(array(
		'ID' 				=> $project_id,
		'post_status' 	=> $post_status
	)); // clone from processEscrow();
   wp_update_post(array(
		'ID' 				=> $order->ID,
	 	'post_status' 	=> 'publish',
	));
   $package 	= fre_get_pack($sku);
   if($package){

	   $payer_id = $order->payer;
	   et_track_payment('UPDATE Userpackagedata  ORDER_ID:'.$order->ID .' Project_id: '.$project_id .' Payer_ID: '.$payer_id. ' SKU: '.$package->sku);
	   $set_current_oder  = AE_Payment::update_current_order($payer_id, $package->sku, $order->ID);
	   //update package data
	   AE_Package::add_package_data($package->sku, $payer_id);
	   AE_Package::update_package_data($package->sku, $payer_id);
	} else {
		et_track_payment('FAIL. pack_null');
	}

}

/**
 * base on the method: processEscrow for packageType == bid_plan and clone into this function
*/
function fre_complete_bid_plan_payment($order_array){
	$order = (object)$order_array;
	wp_update_post(array(
		'ID' 				=> $order->ID,
		'post_status' 	=> 'publish',
	));
	$payer_id 	= $order->payer;
	$sku 			= $order->payment_package;
	$pack 		  = fre_get_pack($sku,'bid_plan');
  	if( $pack->et_number_posts > 0 ){
      update_credit_number( $payer_id, $pack->et_number_posts ); // update_user_meta credit_number
  	}
  	// send mail
  	// do_action('ae_member_process_order', $payer_id, $order_array); // use default hook. co send mail in this hook.
}
function fre_get_order($order_id){

}
/**
 * Check the paymengawy is using v2 or not.
 * If V2 => do not  need call the function ae_process_payment again.
 **/
function is_pmgw_v2($pmgw){
	if( in_array( $pmgw, array('bitcoin','bitcoincash') ) ) return true;
	return false;
}
function fre_show_order_by_type($type, $args){
   $project_id    = isset($args['project_id']) ? $args['project_id'] : 0;
   $ad    			= isset($args['ad']) ? $args['ad'] : 0;
   $order_data 	= $args['order_data'];


	switch ( $type ) {
		case 'bid_plan':
			// buy bid
			if ( $project_id ) {
				$permalink = get_the_permalink( $project_id );
			} else {
				$permalink = et_get_page_link( 'my-project' );
			}
			echo "<p>" . __( 'Now you can return to the project pages', ET_DOMAIN ) . "</p>";
			echo "<a class='fre-btn' href='" . $permalink . "'>" . __( 'Return', ET_DOMAIN ) . "</a>";
		break;
		case 'fre_credit_plan':
			// deposit credit
			if ( $project_id ) {
				$permalink = get_the_permalink( $project_id );
				echo "<p>" . __( 'Return to Project page', ET_DOMAIN ) . "</p>";
			echo "<a class='fre-btn' href='" . $permalink . "'>" . __( 'Click here', ET_DOMAIN ) . "</a>";
			} else {
				$permalink = et_get_page_link( 'my-credit' );
				echo "<p>" . __( 'Return to My Credit Page', ET_DOMAIN ) . "</p>";
				echo "<a class='fre-btn' href='" . $permalink . "'>" . __( 'Click here', ET_DOMAIN ) . "</a>";
			}

		break;
		case 'fre_credit_fix':
			// deposit credit
			if ( $ad ) {

				$permalink = get_the_permalink( $ad->post_parent );
				echo "<p>" . __( 'Return to Project page', ET_DOMAIN ) . "</p>";
				echo "<a class='fre-btn' href='" . $permalink . "'>" . __( 'Click here', ET_DOMAIN ) . "</a>";
			} else {

				$permalink = et_get_page_link( 'my-credit' );
				echo "<p>" . __( 'Return to My Credit Page', ET_DOMAIN ) . "</p>";
				echo "<a class='fre-btn' href='" . $permalink . "'>" . __( 'Click here', ET_DOMAIN ) . "</a>";
			}

		break;

		default:

			if ( $order_data['status'] == 'publish' ) { //Buy package
				echo "<p>" . __( 'Click the button below to be redirected to the previous page', ET_DOMAIN ) . "</p>";
				echo "<a class='fre-btn' href='" . et_get_page_link( 'my-project' ) . "'>" . __( 'Go', ET_DOMAIN ) . "</a>";
			} else  if ( $ad ) { // Submit project
				$permalink = get_the_permalink( $ad->ID );
				echo "<p>" . __( 'Your project details is now available for you to view', ET_DOMAIN ) . "</p>";
				echo "<a class='fre-btn' href='" . $permalink . "'>" . __( 'Go', ET_DOMAIN ) . "</a>";
			}
		break;
	}
}

function fre_get_pages($search = 0){

	$args = array(
		'posts_per_page'   => -1,
		'offset'           => 0,
		'orderby'          => 'title',
		'order'            => 'DESC',
		'post_type'        => 'page',
		'post_status'      => 'publish',
	);
	if($search){
		$args['s'] = $search;
	}
	$query 	= new WP_Query( $args );
	$result 	= array();
	if ($query->have_posts() ) {
		while ( $query->have_posts() ) {
		 	$query->the_post();
			$result[get_the_ID()] = get_the_title();
		}
	}
	wp_reset_query();
	return $result;
}