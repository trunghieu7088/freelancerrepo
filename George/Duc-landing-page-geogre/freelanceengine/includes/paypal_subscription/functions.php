<?php
function insert_pp_order($pack_sku){

	//$author      = isset( $data['author'] ) ? $data['author'] : $user_ID;
	//$packageID   = isset( $data['packageID'] ) ? $data['packageID'] : '';
	//$paymentType = isset( $data['paymentType'] ) ? $data['paymentType'] : '';
	$currency_code 	 = isset( $data['currency_code'] ) ? $data['currency_code'] : '';
	$errors      = array();
	global $user_ID;
	$adID = 0;
	$currency_code = fre_get_currency_code();
	//$obj_pack 			= AE_Package::get_instance();
	//$pack  	= $obj_pack->get_pack( $pack_sku, 'recruit_pack' ); //pack333
	$pack = get_recruit_pack($pack_sku);

	$order_data = array(
			'payer'        => $user_ID,
			'total'        => $pack->et_price,
			'status'       => 'draft',
			'payment'      => 'paypal_recruit',
			//'paid_date'    => ,
			'payment_plan' => $pack_sku,
			'post_parent'  => $adID,
			'currency_code' => $currency_code,
		);
	$order 		= new AE_Order( $order_data, array() );

	$plan_info = array();



	$plan_info['et_price'] = $pack->et_price;
	$plan_info['ID'] = $pack_sku;
	$plan_info['post_title'] = 'pack -'.$pack_sku;
	$plan_info['post_content'] = 'test plan infor';
	$plan_info['post_id'] = 0;
	$plan_info['post_type'] = 'pack';

	$order->add_product( (array) $plan_info );
	$order_data = $order->generate_data_to_pay();
}
function has_paypal_subscription($id){
	global $wpdb;
	$sql = "
	SELECT u.ID
	FROM  $wpdb->users u
	LEFT JOIN $wpdb->usermeta m  ON u.ID = m.user_id
	WHERE  m.meta_key = 'paypal_subscription_id' AND m.meta_value = %s ";
	$sql = $wpdb->prepare($sql, $id);

	$result = $wpdb->get_row($sql);
	if($result){
		return (int)$result->ID;
	}
	return 0;

}
function get_pps_api(){
	$args = array(
		'test_mode' => (int) ae_get_option('pp_test_mode', 1),
		'paypal_email' => ae_get_option('paypal_email'),
		'client_id' => ae_get_option('pp_client_id'),
		'secret_key' =>ae_get_option('pp_secret_key'),
	);
	$test_mode = ae_get_option('pp_test_mode', 1);

	if($test_mode){
		$args['client_id'] = ae_get_option('test_pp_client_id');
		$args['secret_key'] = ae_get_option('test_pp_app_secret_key');
	}
	return (object) $args;
}
function get_pp_recruit_payment_note(){
	ob_start();
	$test_mode = ae_get_option('pp_test_mode', true);
	$html =  '<h3 class="group-desc">How to get APP ID and Secret Key?</h3><br />';
	if( $test_mode)
	$html.= '<p class="group-desc">https://www.sandbox.paypal.com/mep/dashboard<br />';
	$html.=  'https://developer.paypal.com/developer/applications/<br />';
	$html.=  'https://developer.paypal.com/developer/accounts/</p>';


	return $html;
}
function get_recruit_pack($sku){
	global $wpdb;
	$sql = "SELECT * FROM $wpdb->posts p
	LEFT JOIN $wpdb->postmeta m
	ON p.ID = m.post_id
	WHERE m.meta_key = 'sku' AND m.meta_value='{$sku}' AND p.post_type = 'recruit_pack'
	";

	$plan = $wpdb->get_row($sql);
	if($plan){
		$post_id = $plan->ID;
		$sql = "SELECT * FROM   $wpdb->postmeta m  WHERE post_id = {$post_id}";

		$results = $wpdb->get_results($sql);
		$return = array();
		$return['post_title'] =  $plan->post_title;
		$return['post_type'] =  $plan->post_type;
		$return['post_content'] =  $plan->post_content;

		foreach ($results as $key => $meta) {
			$return[$meta->meta_key] = $meta->meta_value;
		}
		return (object)$return;
	}
	return $plan;
}

function pps_log($input, $file_store = ''){

	$file_store = WP_CONTENT_DIR.'/et-content/pps_log.css';

	if( is_array( $input ) || is_object( $input ) ){
		error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. print_r($input, TRUE), 3, $file_store );
	} else {
		error_log( date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ). ': '. $input . "\n" , 3, $file_store);
	}
}