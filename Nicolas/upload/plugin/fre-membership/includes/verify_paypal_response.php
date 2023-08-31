<?php
function fre_validate_ipn() {

		// Get received values from post data.
		$validate_ipn        =  wp_unslash( $_POST ); // WPCS: CSRF ok, input var ok.
		$validate_ipn['cmd'] = '_notify-validate';

		$pp_path = WP_CONTENT_DIR.'/paypal.css';
		et_member_log( 'Checking IPN response is valid.', $pp_path );
		// Send back post vars to paypal.
		$params = array(
			'body'        => $validate_ipn,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'fre_membership/v1.1',
		);

		$test_mode = 	(boolean) ae_get_option('membership_mode', true);

		$sandbox_url 	= "https://www.sandbox.paypal.com/cgi-bin/webscr";
		$live_url 		= "https://www.paypal.com/cgi-bin/webscr";

		$paypal_url = $live_url;
		if($test_mode){
			$paypal_url = $sandbox_url;
		}



		// Post back to get a response.
	 	$response = wp_safe_remote_post( $paypal_url, $params );

		// Check to see if the request was valid.
		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
			et_member_log( 'Validate IPN:  VERIFIED.', $pp_path );
			return true;
		}

		if ( is_wp_error( $response ) ) {
			et_member_log( 'Validate IPN:: FAIL. Error msg : ' , $pp_path );
			et_member_log(  $response->get_error_message(), $pp_path );
		}

		return false;
	}
function fre_legacy_paypal_ipn() {
	if ( ! empty( $_GET['paypalListener'] ) && 'paypal_standard_IPN' === $_GET['paypalListener'] ) {

		if ( ! empty( $_POST ) && fre_validate_ipn() ) { // WPCS: CSRF ok.

			$posted = wp_unslash( $_POST ); // WPCS: CSRF ok, input var ok.
			$txn_type = isset($_REQUEST['txn_type']) ? $_REQUEST['txn_type'] :'none'; // only paypal return
			et_paypal_log( 'txn_type ==  '.$txn_type);


			if( in_array( $txn_type, array('subscr_signup','subscr_payment') ) ) {


				$custom 		= isset( $_POST['custom'] ) ? $_POST['custom'] : 'none'; //user_id||pack_type
				et_paypal_log('custom: '.$_POST['custom']);
				$imp 			= explode("||",$custom);
				$user_id 		= $imp[0];
				$pack_type 		= $imp[1];

				//custom code here 

				$discount_code=$imp[2];
				//end
			

		

				

				$sku 			= isset($_POST['item_number']) ? $_POST['item_number']: '';
				$api_subscr_id 	= isset($_POST['subscr_id'] )  ? $_POST['subscr_id'] : '';
				//$pack_type 	= get_pack_type_of_user($user_id);
				//$pack= 			= fre_get_pack_detail($sku,$pack_type); //can not use this. some time cause problem because did nto load the theme.

				$pack 			= fre_get_plan_by_sql($sku);
				$payment_status = isset($_POST['payment_status']) ? $_POST['payment_status']: 'null'; // verified
				$test_mode 		= isset($_REQUEST['test_ipn']) ? $_REQUEST['test_ipn'] : 0;
				$price_sign  	= isset($_POST['mc_amount3']) ? $_POST['mc_amount3'] : 0;

				$mc_currency    = isset($_POST['mc_currency']) ? $_POST['mc_currency'] : 0;

				if( $txn_type == 'subscr_payment' ){
					$price_sign   = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : 0;
				}



				$price = get_subscribe_price($pack->et_price);

		

				if ( number_format( $price_sign, 2, '.', '' ) !== number_format( $price, 2, '.', '' ) ) {
					if(empty($discount_code))
					{
						et_paypal_log('different price. exit');
						exit;
					}
					
				}

				//custom code here 
				//xu ly giam gia cho paypal
				if(!empty($discount_code))
				{
					$pack_custom=membership_get_pack($sku,'pack');
						
					$discount_percent=get_post_meta($discount_code,'discount_percent',true);	
					$decrease_price=($discount_percent*$pack_custom->et_price)/100;								
   		     		$discount_price=$pack_custom->et_price - $decrease_price;   		     		
   		     		$price_sign=$discount_price;
				}	
				
				//end

				$string = "+1 month";
				if( (int) $pack->et_subscription_time > 1 )
					$string = "+{$pack->et_subscription_time} months";

				$expiry_time = strtotime($string);

				//et_paypal_log($_POST);
				$args = array(
					'user_id'			=> $user_id,
					'plan_sku' 			=> $sku,
					'price' 			=> $price_sign,
					'currency' 			=> $_POST['mc_currency'],
					'api_subscr_id' 	=> $api_subscr_id,
					'remain_posts' 		=>(int) $pack->et_number_posts,
					'expiry_time' 		=> $expiry_time,
					'pack_type' 		=> $pack_type,
					'payment_gw' 		=> 'paypal',
					'payment_status' 	=> $payment_status, // paid
					'test_mode' 		=> $test_mode,
					'auto_renew' 		=> 'active',

				);

				$subscription = get_subscription_by_sub_api_id($api_subscr_id); // có sẵn.
				et_paypal_log('Check of  subscription exist for api_subscr_id:'.$api_subscr_id);
				//et_paypal_log($subscription);
				if( !$subscription ){

					$s_args  	= (object)$args;
					$subscr_id 	= fre_mebership_save_subscrition($s_args);
					if( is_numeric ($subscr_id) && $subscr_id > 1 ){
						$user 	  	= get_userdata($user_id);
						$m_args = array(
							'user_id' 	=> $user_id,
							'user_email'=> $user->user_email,
							'user_login'=> $user->user_login,
							'subscr_id' => $subscr_id
						);
						$m_args  =(object)$m_args;

						fre_save_membership( $m_args );
					} else{
						et_paypal_log('fre_mebership_save_subscrition result FAIL. Error msg:');
						et_paypal_log($subscr_id);
					}
				} else {
					$args 		= (object)$args;
					// update subsription only
					// $subscr_id 	= fre_mebership_save_subscrition($args);
				}
			} else if( in_array( $txn_type, array('subscr_cancel','subscr_eot' ) ) ) {
				$api_subscr_id 	= isset($_POST['subscr_id'] )  ? $_POST['subscr_id'] : '';
				$subscription = get_subscription_by_sub_api_id($api_subscr_id);
				   	if($subscription){
			    		update_auto_renew_status('disable',$subscription->id);
			    	}
			}
			exit;
		} else {

		}

	} else{
		$none_path = WP_CONTENT_DIR.'/nopne_paypalListener.css';
		$txn_type = isset($_REQUEST['txn_type']) ? $_REQUEST['txn_type'] :'';
		if($txn_type){
			et_member_log( 'txn_type ==  '.$txn_type , $none_path);
			et_member_log($_POST, $none_path);
		}
	}
}
add_action( 'init', 'fre_legacy_paypal_ipn', 999 );



class FRE_HTTPS {

	/**
	 * Hook in our HTTPS functions if we're on the frontend. This will ensure any links output to a page (when viewing via HTTPS) are also served over HTTPS.
	 */
	public static function init() {
		add_action( 'http_api_curl', array( __CLASS__, 'http_api_curl' ), 10, 3 );
	}

	/**
	 * Force posts to PayPal to use TLS v1.2. See:
	 *        https://core.trac.wordpress.org/ticket/36320
	 *        https://core.trac.wordpress.org/ticket/34924#comment:13
	 *        https://www.paypal-knowledge.com/infocenter/index?page=content&widgetview=true&id=FAQ1914&viewlocale=en_US
	 *
	 * @param string $handle
	 * @param mixed $r
	 * @param string $url
	 */
	public static function http_api_curl( $handle, $r, $url ) {
		if ( strstr( $url, 'https://' ) && ( strstr( $url, '.paypal.com/nvp' ) || strstr( $url, '.paypal.com/cgi-bin/webscr' ) ) ) {
			curl_setopt( $handle, CURLOPT_SSLVERSION, 6 );
		}
	}
}
// not sure it availble or not.
FRE_HTTPS::init();
