<?php

function fre_verify_stripe_respond(){
	$write = isset($_GET['w']) ? $_GET['w'] : 0;
	if($write){
		et_member_log('Check file write permission');
	}

	if( isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ){

		$stripe_log  = WP_CONTENT_DIR.'/stripe_log.css';
		require_once( FRE_MEMBERSHIP_PATH . '/stripe-php/init.php');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

		$config 		= fre_get_stripe_membership_api();

		\Stripe\Stripe::setApiKey($config->secret_key);
		$signing_secret = $config->signing_secret;
		$payload 		= @file_get_contents('php://input');
		$event = null;
		//  invoice.upcoming
		try {
		    $event = \Stripe\Webhook::constructEvent(
		        $payload, $sig_header, $signing_secret
		    );

		} catch(\UnexpectedValueException $e) {
		    // Invalid payload
		    http_response_code(400);
		    et_member_log('UnexpectedValueException', $stripe_log);
		    exit();
		} catch(\Stripe\Error\SignatureVerification $e) {
		    // Invalid signature

		    et_member_log('SignatureVerification error', $stripe_log);
		    et_member_log($e->getMessage(), $stripe_log);

		    http_response_code(400);
		    exit();
		}

		// Handle the event
		$object = $event->data->object;
		$file = WP_CONTENT_DIR.'/'.$event->type.'.css';
		et_member_log('stripe_subscription event___:'.$event->type, $stripe_log);

		et_member_log($object, $file);
		//customer.subscription.deleted
		switch ($event->type) {
		    case 'payment_intent.succeeded':
		        $paymentIntent = $event->data->object; // contains a StripePaymentIntent

		       // handlePaymentIntentSucceeded($paymentIntent);
		        break;
		    case 'payment_method.attached':
		        $paymentMethod = $event->data->object; // contains a StripePaymentMethod
		        //handlePaymentMethodAttached($paymentMethod);
		        break;
		    case 'checkout.session.completed': // only get client_reference_id in this hook.
		    	// handle_checkout_session($session);
		     	$custom = WP_CONTENT_DIR.'/checkout.completed.css';
		     	$mode = $object->mode;
		     	if($mode == 'subscription'){


			     	$reference_meta 	= $object->client_reference_id; //payer_id
			     	$subscription_id 	= $object->subscription; // sub_JbWeDjRSNoywG5
			     	$session 			= $event->data->object;
			     	$subscription 		= \Stripe\Subscription::retrieve($subscription_id);
			     	$customer_email 	= $object->customer_email;

			     	$output = explode("||",$reference_meta); // user_ID||sku;
			     	$user_id = $output[0];
			     	$sku 	 = $output[1];

			     	update_user_meta($user_id, 'subcription_id',$subscription_id); //sub_Fh4CK0MU8RroRO
					update_user_meta($user_id, 'current_period_end',$subscription->current_period_end); //1569496157
					update_user_meta($user_id, 'stripe_customer_id',$subscription->customer);//cus_Fh4CZC1iuO5mh6
					update_user_meta($user_id, 'plan',$subscription->plan);
					update_user_meta($user_id, 'plan_ID',$subscription->plan->id);
					update_user_meta($user_id, 'pricing_ID',$subscription->plan->id); //new API
					et_member_log('subscription:', $custom);
					et_member_log($subscription, $custom);
					$live = (bool)$subscription->livemode;
					$test_mode = !$live;
					$pack_type 		= get_pack_type_of_user($user_id);
					$pack 			= membership_get_pack($sku, $pack_type);
					$auto_renew 	= 'active';
					if($subscription->cancel_at_period_end){
						$auto_renew = 'disabled';
					}

					$o_args = array(
						'user_id' 		=> $user_id,
						'plan_sku' 		=> $sku,
						'price' 		=> $object->amount_total/100,
						'currency' 		=> $object->currency,
						'api_subscr_id' =>$subscription_id,
						'remain_posts' 	=> $pack->et_number_posts,
						'expiry_time' 	=> $subscription->current_period_end,
						'payment_gw'	=> 'stripe',
						'pack_type' 	=> $pack_type,
						'payment_status' => $object->payment_status, // paid
						'test_mode' => $test_mode,
						'auto_renew' => $auto_renew,

					);
					$o_args = (object)$o_args;
					$subscr_id = fre_mebership_save_subscrition($o_args);
					$user 	  = get_userdata($user_id);
					$m_args = array(
						'user_id' 		=> $user_id,
						'user_email' 	=> $user->user_email,
						'user_login' 	=> $user->user_login,
						'subscr_id' 	=> $subscr_id,
					);
					$m_args  =(object)$m_args;
					fre_save_membership( $m_args );
				}
				break;
		    case 'customer.subscription.created':
		    	et_member_log('customer.subscription.created', $stripe_log);
		    	et_member_log('Sub ID: '.$object->id. ' - Customer: '.$object->customer. ' -  Status: '.$object->status, $stripe_log);
		    	$custom_log = WP_CONTENT_DIR.'/customer.subscription.created.css';
		    	et_member_log($object, $custom_log);


		    case 'customer.subscription.updated': // created updated
		    	et_member_log('customer.subscription.updated', $stripe_log);
		    	et_member_log('Sub ID: '.$object->id. ' - customer: '.$object->customer. ' -  Status: '.$object->status, $stripe_log);
		    	$file = WP_CONTENT_DIR.'/customer.subscription.updated.css';
		    	et_member_log($object, $file);
		    	$api_subscr_id = $object->id;
		    	$status = $object->status;
		    	if( in_array($status, array('incomplete','incomplete_expired','past_due','canceled','unpaid' ) ) ){
		    		// tắt chức năng auto renew
		    		$subscription = get_subscription_by_sub_api_id($api_subscr_id);

				   	if($subscription){
			    		update_auto_renew_status('cancel',$subscription);
			    	}
				} else if($status == 'active'){
					$subscription = get_subscription_by_sub_api_id($api_subscr_id);
				   	if($subscription){
			    		update_auto_renew_status('active',$subscription->id);
			    	}
				}

		    	break;
		    case 'customer.subscription.deleted': // created updated
		   		$api_subscr_id = $object->id;
		    	et_member_log('customer.subscription.updated',$stripe_log);
		    	$file = WP_CONTENT_DIR.'/customer.subscription.deleted.css';
		    	et_member_log($object, $file);
			   	$subscription = get_subscription_by_sub_api_id($api_subscr_id);
			   	if($subscription){
		    		update_auto_renew_status('cancel',$subscription->id);
		    	}

		    	break;
		    case 'invoice.upcoming': // số ngày gửi hook được set trong này: https://dashboard.stripe.com/settings/billing/automatic
		    $file = WP_CONTENT_DIR.'/invoice.update.css';
			$api_subscr_id = $object->subscription;
		    et_member_log('Your subscription will expire soon: '.$api_subscr_id, $file);

		    $subscription= get_subscription_by_sub_api_id($api_subscr_id);
		    if($subscription){
		    	$subject = __('Your subscription will expire soon.','enginethemes');
		    	$message = "Hi {$subscrion->user_login} <br /> Your subscription will expire soon.<br />";
		    	wp_mail($subscription->user_email, $subject, $message);
		    }
		    // $subscription = get_subscription_by_sub_api_id($api_subscr_id, $user_id);
		    // $subject = __('Your subscription will expire soon.','fre_membership');
		    	// fre_email_expire_soon();
				// ... handle other event types
		    default:
		    	http_response_code(200); // show response ok.
				// http_response_code(400); show response fail
		        exit();
		} // end switch

		http_response_code(200);
	} else {
		http_response_code(404);
		exit();
		// et_member_log("No _SERVER['HTTP_STRIPE_SIGNATURE']");
	}
}

//add_action('rest_api_init','fre_verify_stripe_respond', 99);
//http://mje.thuexephanrang.net/?rest_route=/stripewebhooks/v1/task
add_action('rest_api_init',
    function () {
        register_rest_route( 'stripewebhooks/v1', '/task', array(
            'methods' => 'POST',
            'callback' => 'fre_verify_stripe_respond',
            'permission_callback' => function () {
                return true; // security can be done in the handler
            }
        ));
    }
);
function fre_save_subcription_info($object, $user_id = 0){
	$user =  0;
	if(!$user_id){

		$args = array(
		   'meta_key' => 'stripe_customer_id',
		   'meta_value' => $object->customer,
		   'number' => 1,
		   'count_total' => false
		  );
		et_member_log('get_user_with_stripe_customer_id meta');

		$user = get_users( $args ) ;
		if($user){

			$user_id = $user[0]->ID;
			$user = $user[0]; // set for next action.

		} else {
			et_member_log('get_user_with_remote_get');
			// auto sync to wp database if manually add a new subscription from stripe.
			$stripe = fre_get_stripe_api($auto_map = 1);

			$headers = array(
				'Authorization' => "Bearer ".$stripe->secret_key,
				'Content-type' => 'application/json',
				'Accept' => 'application/json',

			);
			$customer_url = 'https://api.stripe.com/v1/customers/'.$object->customer;
			$customer_res  = wp_remote_get( $customer_url,
			    array(
			        'timeout'     => 120,
			        'httpversion' => '1.1',
			        'headers' => $headers,
			    )
			);
			$customer = json_decode($customer_res['body']);
			$user_email = $customer->email;
			$user = get_user_by( 'email', $user_email );
			if( $user ){
				$user_id = $user->ID;
			}

		}
	}
	et_member_log('user_ID: '.$user_id.' - pricing_ID: '.$object->plan->id);
	et_member_log('start send mail for user_ID: '.$user_id);
	if($user_id){
		et_member_log('update Subscription Metas');

		update_user_meta($user_id, 'cancel_at_period_end', $object->cancel_at_period_end); // default is 0
		update_user_meta($user_id, 'subcription_id',$object->id); //sub_Fh4CK0MU8RroRO
		update_user_meta($user_id, 'current_period_end',$object->current_period_end); //1569496157
		update_user_meta($user_id, 'stripe_customer_id', $object->customer); //cus_Fh4CZC1iuO5mh6
		update_user_meta($user_id, 'pricing_ID',$object->plan->id); //new API
		et_member_log('update Subscription Metas line 57');
		$option 		= BX_Option::get_instance();
		$mails  		= $option->get_subscription_mails();
		$email 			= (object) $mails['new_subscription'];

		$content 		= fre_subscription_email_convert($email->content, $user);
		et_member_log('send email to:'.$user->user_email);
		fre_mail($user->user_email, $email->subject, $content);
	}
}


function fre_delete_subcription_info($object, $user_id = 0){
	if( ! $user_id ){
		$args = array(
		   'meta_key' => 'stripe_customer_id',
		   'meta_value' => $object->customer,
		   'number' => 1,
		   'count_total' => false
		 );
		$user = get_users( $args ) ;
		if( $user ){
			$user_id = $user[0]->ID;
			$user = $user[0];
		}
	}

	update_user_meta($user_id,'cancel_at_period_end', 0 ); //auto renew or not.
	update_user_meta($user_id, 'current_period_end',0 ); //1569496157
	update_user_meta($user_id, 'plan', 0);
	update_user_meta($user_id, 'plan_ID',0);
	$option 		= BX_Option::get_instance();
	$mails  		= $option->get_subscription_mails();

	$email 			= (object) $mails['expired'];
	$content 		= fre_subscription_email_convert($email->content, $user);
	fre_mail($user->user_email, $email->subject, $content);

}

function fre_email_expiring_soon($object){

}