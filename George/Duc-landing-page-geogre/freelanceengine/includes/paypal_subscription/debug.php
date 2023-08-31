<?php
function pps_debug(){
	$file_store = WP_CONTENT_DIR.'/et-content/pps_log.css';
	echo '<br /> <a target="_blank"  href="'.home_url().'/wp-content/et-content/pps_log.css">Log File </a>';

	global $user_ID;
	$current_plan_id = get_user_meta($user_ID,'paypal_plan_id', true);
	$user_sub_id = get_user_meta($user_ID,'paypal_subscription_id', true);
	echo '<pre>';
	var_dump('current Plan ID:  '.$current_plan_id);
	var_dump('current paypal_subscription_id:  '.$user_sub_id);
	var_dump($user_sub_id);
	$setting = get_pps_api();





	echo '</pre>';
	//$basic = base64_encode(SB_CLIENT_ID.":".APP_SECRET_KEY);
	$basic = base64_encode($setting->client_id.":".$setting->secret_key);

		$headers = array(
			'Authorization' => "Basic ".$basic,
			'Content-type' => 'application/json',
			'Accept' => 'application/json',
		);


		$header_oder = array(
			'Authorization' => "Basic ".$basic,
			'Content-type' => 'application/json',
			'Accept' => 'application/json',
		);


		//$url = 'https://api.sandbox.paypal.com/v1/billing/subscriptions/'.$data['subscriptionID'];
		$url = 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-JCE0YX66BSV1';

		$order_url = 'https://api.sandbox.paypal.com/v2/checkout/orders/5K860471H22778605';
		$order_res = wp_remote_get( $order_url,
		    array(
		        'timeout'     => 120,
		        'httpversion' => '1.1',
		        'headers' => $header_oder,
		    )
		);

		$subscription = wp_remote_get( $url,
		    array(
		        'timeout'     => 120,
		        'httpversion' => '1.1',
		        'headers' => $headers,
		    )
		);


		echo '<pre>';
		//var_dump($response);
		$sub_body = wp_remote_retrieve_body( $subscription );
		//var_dump($sub_body);

		$sub = json_decode( $sub_body );
		$orderbody = wp_remote_retrieve_body( $order_res );
		$order = json_decode( $orderbody );


		//var_dump($order);

		$subscriber = $sub->subscriber;
		$billing_info = $sub->billing_info;
		$next_billing_time = $billing_info->next_billing_time;
		var_dump($next_billing_time);

		//$payer = $order->payer;
	
		//var_dump($subscriber);
		echo 'Subcriber <br />';
		var_dump($sub);
		//var_dump($order);

		//$start_time = $sub->start_time;
		//$date1 = DateTime::createFromFormat('Y-m-d H:i:s', $start_time);

		//var_dump($response);
		echo '</pre>';


}
//add_action('wp_footer','pps_debug');