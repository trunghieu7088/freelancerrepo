<?php
function save_membership_info($payment_return, $data){
	et_log('save membership info');
	if ( isset( $data['order_id'] ) ) {
		et_log('order infor: '.$data['order_id']);
		$order = new AE_Order( $data['order_id'] );
		if( $order && ! is_wp_error($order) ){
			$order_id = $data['order_id'];
			$order      = new AE_Order( $order_id );
			$order_data = $order->get_order_data();

			$product = current( $order_data['products'] );
			$type    = $product['TYPE'];

			if( $type == 'pack' || $type == 'bid_plan'){
				// et_member_log('call insert_membership with input $type = '.$type);
				//et_member_log($order_data);
				// insert_membership( $order_data, $type );
			} else {

			}
			et_log('save successul');

		}
	} else {
		et_log('save fail');
	}
}
//add_action('ae_process_payment_action','save_membership_info', 99 ,2);

/**
 this hook only available for freelancer account. After freelancer paytobid or upgrade account successful => Sytem process this function.
*/
//add_action('after_fre_pay_to_bid','save_membership_info', 99 ,2);