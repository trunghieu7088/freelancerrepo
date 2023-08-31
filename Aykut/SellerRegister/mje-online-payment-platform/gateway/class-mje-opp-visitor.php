<?php

class MJE_OPP_Visitor extends ET_PaymentVisitor {

	protected $_payment_type = 'OPP';

	function setup_checkout( ET_Order $order ) {
		global $user_email;
		$response   = array();
		$return_url = $this->_settings['return'];
		$order_pay  = $order->generate_data_to_pay();

		$transaction_endpoint = 'https://api.onlinebetaalplatform.nl/v1/transactions';
		if ( 'test' === MJE_OPP::$opp_api['mode'] ) {
			$transaction_endpoint = 'https://api-sandbox.onlinebetaalplatform.nl/v1/transactions';
		}

		$body = array(
			'merchant_uid' => MJE_OPP::$opp_api['merchant_uid'],
			'products'     => array(
				array(
					'name'     => get_bloginfo( 'title' ),
					'price'    => $order_pay['total'] * 100,
					'quantity' => 1,
				),
			),
			'total_price'  => $order_pay['total'] * 100,
			'checkout'     => false,
			'return_url'   => $return_url,
			'notify_url'   => add_query_arg(
				array(
					'ae_page' => 'opp_ipn',
					'oid'     => $order_pay['ID'],
				),
				home_url()
			),
			'metadata'     => array(
				'order_id'   => (string) $order_pay['ID'],
				'user_email' => $user_email,
			),
		);

		$args = array(
			'method'  => 'POST',
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . MJE_OPP::$opp_api['api_key'],
				'Content-Type'  => 'application/json',
				'Cache-Control' => 'no-cache',
				'Accept'        => '*/*',
			),
			'body'    => wp_json_encode( $body ),
		);

		$response_raw  = wp_remote_post( $transaction_endpoint, $args );
		$response_body = wp_remote_retrieve_body( $response_raw );
		$response      = json_decode( $response_body, true );

		if ( siar( $response, 'uid' ) ) {
			return array(
				'extend' => false,
				'ACK'    => true,
				'url'    => siar( $response, 'redirect_url' ),
			);
		} else {
			$error_message = siars( $response, 'error/message', 'Something went wrong, please try again!' );

			// Force delete order and mjob order
			wp_delete_post( $order_pay['ID'], true );
			wp_delete_post( $order_pay['product_id'], true );

			// Destroy session
			et_destroy_session();

			return array(
				'success' => false,
				'ACK'     => false,
				'msg'     => $error_message,
			);
		}
	}

	function do_checkout( ET_Order $order ) {
		$payment_return = array(
			'ACK'            => true,
			'payment'        => 'OPP',
			'payment_status' => 'Pending',
		);
		return $payment_return;
	}
}
