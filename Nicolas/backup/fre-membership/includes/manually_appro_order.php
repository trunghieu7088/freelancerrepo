<?php

function update_membership_order_status($order_id){

	$order      = new AE_Order( $order_id );
	$order_data = $order->get_order_data();
	$product = current( $order_data['products'] );
	$type    = $product['TYPE']; // pack or bid_lan
	$sku     = $product['ID']; // sku
	$payer_id = (int) $order_data['payer'];
	// need check this.

	global $user_ID, $wpdb;
	$table = $wpdb->prefix . 'membership';

	$sql_update = $wpdb->prepare(
		"
		UPDATE $table
		SET  order_status=%s
		WHERE user_id = %d AND pack_sku = %s
		",
		 $order_data['status'], $payer_id, $sku
	);

	$wpdb->query($sql_update);
}
add_action('ae_after_update_order','update_membership_order_status');
?>