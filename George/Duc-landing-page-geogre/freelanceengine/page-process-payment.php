<?php
/**
 *    Template Name: Process Payment
 */
$session = et_read_session();
global $ad, $payment_return, $order_id, $user_ID;

$payment_type = get_query_var( 'paymentType' );
if ( $payment_type == 'usePackage' || $payment_type == 'free' ) {
	$payment_return = ae_process_payment( $payment_type, $session );
	if ( $payment_return['ACK'] ) {
		$project_url = get_the_permalink( $session['ad_id'] );
		// Destroy session for order data
		et_destroy_session();
		// Redirect to project detail
		wp_redirect( $project_url );
		exit;
	}
}

/**
 * get order
 */
$order_id = isset( $_GET['order-id'] ) ? $_GET['order-id'] : '';
if ( empty( $order_id ) && isset( $_POST['orderid'] ) ) {
	$order_id = $_POST['orderid'];
}

$order      = new AE_Order( $order_id );
global $order_data;
$order_data = $order->get_order_data();
$payment_status = $order_data['status'];
$is_escrow = 0;
if(  $payment_type == 'paypaladaptive' || $payment_type == 'frecredit' || $payment_type == 'stripe' ){
	$is_escrow = 1;
}

if ( $is_escrow && ! $order_id ) {
	et_track_payment('vao is_escrow.');
	//frecredit --> accept bid.
	$payment_return  = fre_process_escrow( $payment_type, $session );
	$payment_return  = wp_parse_args( $payment_return, array( 'ACK' => false, 'payment_status' => '' ) );
	extract( $payment_return );
	if ( isset( $ACK ) && $ACK ):
		//change charge status transaction accept bid to pending from ver 1.8.2
		do_action( 'fre_change_status_accept_bid', $session['payKey'] );

		// Accept bid
		$ad_id 		 = $session['ad_id'];
		$order_id    = $session['order_id'];
		$permalink   = get_permalink( $ad_id );
		$permalink   = add_query_arg( array( 'workspace' => 1 ), $permalink );
		$workspace   = '<a href="' . $permalink . '">' . get_the_title( $ad_id ) . '</a>';
		$bid_id      = get_post_field( 'post_parent', $order_id );
		$bid_budget  = get_post_meta( $bid_id, 'bid_budget', true );
		$content_arr = array(
			'paypaladaptive' => __( 'Paypal', ET_DOMAIN ),
			'frecredit'      => __( 'Credit', ET_DOMAIN ),
			'stripe'         => __( 'Stripe', ET_DOMAIN )
		);

		// get commission settings
		$commission     = ae_get_option( 'commission', 0 );
		$commission_fee = $commission;

		// caculate commission fee by percent
		$commission_type = ae_get_option( 'commission_type' );
		if ( $commission_type != 'currency' ) {
			$commission_fee = ( (float) ( $bid_budget * (float) $commission ) ) / 100;
		}

		$commission          = fre_price_format( $commission_fee );
		$payer_of_commission = ae_get_option( 'payer_of_commission', 'project_owner' );
		if ( $payer_of_commission == 'project_owner' ) {
			$total = (float) $bid_budget + (float) $commission_fee;
		} else {
			$commission = 0;
			$total      = $bid_budget;
		}
		get_header();
		show_process_escrow_order($order_id, $total, $permalink);
		get_footer();
	else: //ACK Fail
		et_track_payment('404 page line 130. Page Process Payment and set status 404');
		// Redirect to 404
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		get_template_part( 404 );
		exit();
	endif;
} else if ( $order_id ) {
	$payment_gw   = $order_data['payment']; // payment gateway.
	// Process submit project

	global $ad, $project_id, $order_id;

	$ad = false;
	if( !empty($order_data['product_id']) ){
		$ad         = get_post( $order_data['product_id'] );
	}

	$project_id 		= ( isset( $session['project_id'] ) ) ? $session['project_id'] : '';
	if ( $order_id  ) {
		//et_track_payment('Call ae_process_payment default flow. payment_status: '.$payment_status);
		//processs payment
		if ( $payment_type == 'paypaladaptive' || $payment_type == 'frecredit' ) {
			$payment_return = fre_process_escrow( $payment_type, $session );
		} else {
			$order_version  = $order_data['version'];
			// New version don't process_payment order again. System process/verify order before redirect to page-process-pm.
			if( $order_version !== 'v2' && $payment_status !=='pending' ){
				$payment_return = ae_process_payment( $payment_gw, $session );
			}
		}
		update_post_meta( $order_id, 'et_order_is_process_payment', true );
		et_destroy_session();// make sure dont process_payment dublicate
	}
	get_header();
	get_template_part('template/process-payment', 'content'); //template/process-payment-content.php
	get_footer();
}
?>