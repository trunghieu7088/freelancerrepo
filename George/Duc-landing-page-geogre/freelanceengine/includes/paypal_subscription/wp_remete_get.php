<?php
//$basicauth = 'Bearer '.$data['facilitatorAccessToken'];

		$basic = base64_encode(SB_CLIENT_ID.":".APP_SECRET_KEY);

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
		$url = 'https://api.sandbox.paypal.com/v1/billing/subscriptions/I-HANCFRU8SJ96';

		$order_url = 'https://api.sandbox.paypal.com/v2/checkout/orders/5K860471H22778605';

		$subscription = wp_remote_get( $url,
		    array(
		        'timeout'     => 120,
		        'httpversion' => '1.1',
		        'headers' => $headers,
		    )
		);
		$order = wp_remote_get( $order_url,
		    array(
		        'timeout'     => 120,
		        'httpversion' => '1.1',
		        'headers' => $header_oder,
		    )
		);

		echo '<pre>';
		//var_dump($response);
		$sub_body = wp_remote_retrieve_body( $subscription );

		$sub_detail = json_decode( $sub_body );
		$orderbody = wp_remote_retrieve_body( $order );
		$order_detail = json_decode( $orderbody );

		var_dump($sub_detail);
		var_dump($order_detail);

		//var_dump($response);
		echo '</pre>';