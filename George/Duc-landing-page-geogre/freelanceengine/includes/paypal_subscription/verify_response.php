
<?php
function fre_pp_validate_ipn($posted){

	if($posted['txn_type'] != 'recurring_payment'){
		return new WP_Error( 'txt_type_wrong', __( "I've fallen and can't get up", "enginethemes" ) );
	}
	// if ( number_format( 100, 2, '.', '' ) != number_format( $_POST['mc_gross'], 2, '.', '' ) ) {
	// 	return false;
	// }
	$api = get_pps_api();
	if( $posted['receiver_email'] != $api->paypal_email ){
		return new WP_Error( 'receiver_email_fail', __( "Receiver Email don't match {{$posted['receiver_email']}} - {$api->paypal_email}", "enginethemes" ) );

	}
	$code = fre_get_currency_code();
	if($posted['mc_currency'] !== $code){
		return new WP_Error( 'currency_code_different', __( "Currency Code don't match ", "enginethemes" ) );
	}
	return true;
}
function fre_pps_ipn(){
	$validate_ipn        = wp_unslash( $_POST ); // WPCS: CSRF ok, input var ok.
	$validate_ipn['cmd'] = '_notify-validate';

	// Send back post vars to paypal.
	$params = array(
		'body'        => $validate_ipn,
		'timeout'     => 60,
		'httpversion' => '1.1',
		'compress'    => false,
		'decompress'  => false,
		'user-agent'  => 'FreelanceEngine/1.0' ,
	);

	// Post back to get a response.
	$sandbox = 1;
	$response = wp_safe_remote_post( $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr', $params );

	if(isset($_POST['txn_type'])){

		pps_log('txt_type:'.$_POST['txn_type']);
		//[txn_type]               => subscr_signup,subscr_failed,subscr_payment
		//https://gist.github.com/thenbrent/3037967

	}

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
		pps_log('IPN Subscription success');
		//pps_log($_POST);
		$validate = fre_pp_validate_ipn($_POST);
		if(  !is_wp_error($validate) ){
			// update db here;
			pps_log('update_pps here');
			$pp_sub_id = $_POST['recurring_payment_id']; //paypal_subscription_id
			$user_id = has_paypal_subscription($pp_sub_id);
			//pps_log($_POST);

		} else {
			pps_log('Validate IPN Error: '.$validate->get_error_message());
			pps_log($_POST);
		}
	} else{
		if( isset($_GET['unlink']))
			unlink(WP_CONTENT_DIR.'/et-content/pps_log.css');

	}
}
add_action( 'init', 'fre_pps_ipn' );