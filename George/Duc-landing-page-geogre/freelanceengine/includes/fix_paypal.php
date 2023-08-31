<?php
/**
 * @pre: fix_paypal.php
 * Một số trường hợp, nếu k phải là paypal business thì gây ra lỗi 404 bên tràn process_payment.
 * ví dụ như deposit credit bằng paypal
 **/
function frev2_paypal_reponse(){
	// if ( !empty($_REQUEST['paypalListener']) && ('paypal_appengine_IPN' == $_REQUEST['paypalListener']) ) {

	if( isset($_POST['txn_type']) && $_POST['txn_type'] == 'cart' ) {

		// et_track_payment($_REQUEST);
		$order_id 		= (int) $_REQUEST['custom'];
		et_track_payment('frev2_paypal_reponse process. ID '.$order_id);
		$payment_status = strtolower($_REQUEST['payment_status']);
		$business 		= isset($_REQUEST['business']) ?  $_REQUEST['business'] :'';

		$receiver_email = isset( $_REQUEST['receiver_email'] ) ? $_REQUEST['receiver_email'] : '';
		$mc_currency 	= isset($_REQUEST['mc_currency']) ? $_REQUEST['mc_currency']: '';
		if( empty($business) ){
			$business 	= $receiver_email;
		}
		$mc_gross 		= $_REQUEST['mc_gross'];
		$payment 		= new ET_Paypal();
		$api 			= $payment->get_api();
		$order     		= new AE_Order( $order_id );

		$order_data = $order->get_order_data();
		$status  	= $order_data['status'];

		if( $status !== 'draft'){
			et_track_payment('Exit. order_status: '.$status);
			return  0;
		}

		et_track_payment('Order ID: '. $order_id.'. order_tatus: '.$status);
		et_track_payment('payment_status:'.$payment_status);

		if( $mc_currency !== $order_data['currency'] ){
			et_track_payment('Currence Code Fail.');
			et_track_payment('order_data:');
			et_track_payment($order_data);
			et_track_payment('mc_currency: '.$mc_currency);
			return ;
		}
		if( !empty($business) &&  $business !== trim( $api['api_username'] )  ){
		// moot so case k co email nguoi nhan or business email. bo qua dieu kien nay. ex: txn_type = cart
			et_track_payment('Business Email: '.$business);
			et_track_payment('api_username: '.trim( $api['api_username'] ) );
			et_track_payment('Fail. Receiver email not the same api.');
			return 0;
		}
		if( $payment_status == 'pending'   ){
			et_track_payment('Set order id '.$order_id.' to pending.');
			wp_update_post(array('ID' => $order_id,'post_status' => 'pending'));
		} else if( $payment_status == 'completed'){
			et_track_payment('call: fre_order()->payment_complete();');
			$order = new Fre_Order($order_id);
			$order->payment_complete();
			wp_update_post( array('ID' => $order_id,'post_status' => 'publish') );

		}
	}

}
add_action('init','frev2_paypal_reponse');

/**
// [paymentType] => paypal
//     [order-id] => 999
//     [payer_email] => freelancer@etteam.com
//     [payer_id] => MFESFXF8A6TJW
//     [payer_status] => VERIFIED
//     [first_name] => Freelancer
//     [last_name] => Staff
//     [address_name] => Freelancer Staff
//     [address_street] => 1 Main St
//     [address_city] => San Jose
//     [address_state] => CA
//     [address_country_code] => US
//     [address_zip] => 95131
//     [residence_country] => US
//     [txn_id] => 3FC62641VT0703436
//     [mc_currency] => USD
//     [mc_fee] => 0.52
//     [mc_gross] => 1.00
//     [protection_eligibility] => ELIGIBLE
//     [payment_fee] => 0.52
//     [payment_gross] => 1.00
//     [payment_status] => Completed
//     [payment_type] => instant
//     [handling_amount] => 0.00
//     [shipping] => 0.00
//     [item_name1] => 1
//     [item_number1] => 1
//     [quantity1] => 1
//     [mc_gross_1] => 1.00
//     [tax1] => 0.00
//     [num_cart_items] => 1
//     [txn_type] => cart
//     [payment_date] => 2022-08-02T07:10:20Z
//     [receiver_id] => Z78VLBZT7FN6G
//     [notify_version] => UNVERSIONED
//     [custom] => 999
//     [verify_sign] => ApV.TKg645AF013D4.ZnodnMmCm-Af6oMColkb6VzwP1EGCdKFO8I2RX
// )
**/