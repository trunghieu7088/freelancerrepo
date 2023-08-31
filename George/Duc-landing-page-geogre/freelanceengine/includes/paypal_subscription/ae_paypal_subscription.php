<?php
// https://developer.paypal.com/docs/subscriptions/integrate/#4-create-a-subscription
// create product: https://www.sandbox.paypal.com/billing/plans
// paypal button code: https://www.sandbox.paypal.com/billing/plan/P-87H0055203736732SL2OCVNA/copy-code
//<script src="https://paypal.com/sdk/js?client-id=YOUR_CLIENT_ID&disable-funding=credit,card"></script>
// button https://developer.paypal.com/demo/checkout/#/pattern/checkout

//response: https://developer.paypal.com/docs/subscriptions/full-integration/#payment-processing-and-retry
//PAYMENT.SALE.COMPLETED
//https://developer.paypal.com/docs/api/webhooks/#verify-webhook-signature_post
//https://paypal.github.io/PayPal-PHP-SDK/sample/doc/notifications/ValidateWebhookEvent.html
//https://www.paypal-community.com/t5/REST-APIs/Webhook-PHP-Example-for-PAYMENT-SALE-COMPLETED/td-p/1611064
//https://github.com/paypal/PayPal-PHP-SDK/issues/1286


define('SB_CLIENT_ID','AWeXnxRnSBh7sO4BIp8RDMaaDwi5_HOsp6LHkNk3ai1syOAD5NA7Fgss53IwgFPjvV4pbYbhAYIgcR3A');
define('APP_SECRET_KEY','EM08bS6Ghxtxg_1PJeVxBGfo88wzD9uQTe47MNVJutbwCXKGarlez4SsnWg3ODPQOMYYCkCLf-JzjGjO');
define('FIRST_PLAN_ID','P-0NP20713UG787752SL2NMREQ');
function enable_paypal_subscription(){

	$paypal = ae_get_option('paypal', false);

	if( isset($paypal['enable_paypal_subsction']))
		return (int) $paypal['enable_paypal_subsction'];

	return false;
}


Class AE_PayPal_Subscription{
	function __construct(){
		add_filter('ae_paypayl_subscription_setting', array($this,'paypal_subscription_Setting'));
		add_filter('ae_package_metas',array($this,'ae_package_metas'));



		add_action('wp_ajax_nopriv_pps-onApprove', array($this, 'approveSubscription'));
		add_action('wp_ajax_pps-onApprove', array($this, 'approveSubscription'));
		add_action('right_post_project_form',array($this, 'fre_block_membership_plan') );

	}
	function fre_block_membership_plan(){

	}
	function approveSubscription(){
		//var_dump($_POST);
		$data= $_POST['data'];
		$sku = $_POST['sku'];
		// data[orderID]: 0LK62491HM112714C
		// data[subscriptionID]: I-UK65RXNAM78G
		// data[facilitatorAccessToken]: A21AAHWraPAtI9OddXfo48Z3-fhfIBlBhx6c_aKEGY6V-ZJWAZX_RVuqfv_HXYWpvMQo01wWSVyo2OSATW1ETrX076WPeQnlw
		$setting = get_pps_api();
		//$basic = base64_encode(SB_CLIENT_ID.":".APP_SECRET_KEY);
		$basic = base64_encode($setting->client_id.":".$setting->secret_key);

		$url = 'https://api.sandbox.paypal.com/v1/billing/subscriptions/'.$data['subscriptionID'];
		$headers = array(
			'Authorization' => "Basic ".$basic,
			'Content-type' => 'application/json',
			'Accept' => 'application/json',
		);
		$subscription = wp_remote_get( $url,
		    array(
		        'timeout'     => 120,
		        'httpversion' => '1.1',
		        'headers' => $headers,
		    )
		);





		$sub_body = wp_remote_retrieve_body( $subscription );

		$sub = json_decode( $sub_body );
		$plan_id = $sub->plan_id;
		$subscription_id = $data['subscriptionID'];
		$billing_info = $sub->billing_info;
		$next_billing_time = $billing_info->next_billing_time;

		$order_url = 'https://api.sandbox.paypal.com/v2/checkout/orders/'.$data['orderID'];

		$order_res = wp_remote_get( $order_url,
		    array(
		        'timeout'     => 120,
		        'httpversion' => '1.1',
		        'headers' => $headers,
		    )
		);
		//$orderbody = wp_remote_retrieve_body( $order_res );
		//$order = json_decode( $orderbody );
		//$payer = $order->payer;
		$subscriber = $sub->subscriber;




		//if($payer->payer_id == $subscriber->payer_id && $order->status == 'APPROVED'){

		if( $sub->status == 'ACTIVE'){
			global $user_ID;
			// update db here.
			$has_owner = has_paypal_subscription($subscription_id);// fix hacker. someone can trigger an ajax with argument of other subscription id.
			if(!$has_owner){
				// only
				// get pack_sku from meta pp_plan_id
				insert_pp_order($plan_id);
				update_user_meta($user_ID, 'paypal_plan_id', $plan_id);
				update_user_meta($user_ID, 'paypal_subscription_id', $subscription_id);
				update_user_meta($user_ID, 'paypal_payer_id', $subscriber->payer_id);
				update_user_meta($user_ID, 'next_billing_time', $next_billing_time);
				$args = array(
					'ID' => $_POST['curID'],
					'post_status' => 'publish',
				);
				wp_update_post($args);
			}
		}

		//L#$7ZjXf$qeuwLpjuKSsIDJH

		$resp = array(
			'status' => 'OK',
			'msg' =>'done',
			'project_url' => get_permalink($_POST['curID'])
		);
		wp_send_json($resp);
	}




	function ae_package_metas($meta){
		$meta[] = 'paypal_plan';
		return $meta;
	}
	function paypal_subscription_Setting( $paypal_fields){

		$paypal_fields[] = array(
			'id'    => 'enable-paypal-subsction',
			'type'  => 'switch',
			'title' => __( "Align", ET_DOMAIN ),
			'name'  => 'enable_paypal_subsction',
			'class' => 'option-item bg-grey-input',
			'label_desc' => 'Enable PayPal Subscription'
		);

		$paypal_fields[]=array(
					'id'          => 'sid',
					'type'        => 'text',
					'title'       => __( "Client id", ET_DOMAIN ),
					'label'       => __( "Client id", ET_DOMAIN ),
					'name'        => 'sid',

					'class'       => 'option-item bg-grey-input ',
					'placeholder' => __( 'Your PayPal Client ID', ET_DOMAIN )
				);
		$paypal_fields[]=array(
					'id'          => 'sid',
					'type'        => 'text',
					'title'       => __( "Secreky Key", ET_DOMAIN ),
					'label'       => __( "Secreky Key ", ET_DOMAIN ),
					'name'        => 'sid',
					'class'       => 'option-item bg-grey-input ',
					'placeholder' => __( 'Your PayPal Secret Key', ET_DOMAIN )
				);




		return $paypal_fields;
	}
}
// new AE_PayPal_Subscription();


