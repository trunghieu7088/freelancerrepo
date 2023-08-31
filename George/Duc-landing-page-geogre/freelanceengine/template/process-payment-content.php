<?php
global  $ad, $project_id, $order_id;
$order      = new AE_Order( $order_id );
$order_data = $order->get_order_data();
$project_id       = ( isset( $session['project_id'] ) ) ? $session['project_id'] : '';
$order_id         = $order_data['ID'];
$version 	      = isset($order_data['version']) ? $order_data['version'] : 0;
$heading 	      = __( "Payment Successfully Completed", ET_DOMAIN );
$des 		      = __( "Thank you. Your payment has been received and the process is now being run.", ET_DOMAIN );
$pending_title =  __('Payment pending.',ET_DOMAIN);

$payment_status = $order_data['status'];

if( $version == 'v2' && in_array($payment_status, array('failed','cancelled') ) ){
	$heading = __('Payment Failed', ET_DOMAIN);
	$des 		= __( "Sorry. Your payment failed. Please check and pay again.", ET_DOMAIN );
}
if( $payment_status == 'draft' ){
    $heading = __('Payment fail.',ET_DOMAIN);
    $des        = __( "Sorry. Your payment failed. Please check and pay again.", ET_DOMAIN );
} else if( $payment_status == 'pending'){
    $heading    = $pending_title;
    $des        = __( "Thank you. Your payment is pending. Administrator will check and approve it soon.", ET_DOMAIN );
}

?>
<div class="fre-page-wrapper">
    <div class="fre-page-title">
        <div class="container">
            <h2><?php the_title(); ?></h2>
        </div>
    </div>
    <div class="fre-page-section">
        <div class="container">
            <div class="page-purchase-package-wrap template\process-payment-content">
                <div class="fre-purchase-package-box">
                    <div class="step-payment-complete">
                        <h2><?php echo $heading; ?></h2>
                        <p><?php echo $des; ?></p>
                        <div class="fre-table">
                            <div class="fre-table-row">
                                <div class="fre-table-col fre-payment-id"><?php _e( "Invoice No:", ET_DOMAIN ); ?></div>
                                <div class="fre-table-col"><?php echo $order_data['ID']; ?></div>
                            </div>
                            <div class="fre-table-row">
                                <div class="fre-table-col fre-payment-date"><?php _e( "Date:", ET_DOMAIN ); ?></div>
                                <div class="fre-table-col"><?php echo get_the_date( get_option( 'date_format' ), $order_id ); ?></div>
                            </div>
                            <div class="fre-table-row">
                                <div class="fre-table-col fre-payment-type"><?php _e( "Payment Type:", ET_DOMAIN ); ?></div>
                                <div class="fre-table-col"><?php echo $order_data['payment']; ?></div>
                            </div>
                            <div class="fre-table-row">
                                <div class="fre-table-col fre-payment-total"><?php _e( "Total:", ET_DOMAIN ); ?></div>
                                <div class="fre-table-col"><?php echo fre_order_format( $order_data['total'], $order_data['currency'] ); ?></div>
                            </div>
                        </div>
                        <div class="fre-view-project-btn">
							<?php
							if ( isset( $order_data['products'] ) ) {
									$product = current( $order_data['products'] );
									$type    = $product['TYPE'];
                                    $args = array(
                                        'project_id'    => $project_id,
                                        'ad'            => $ad,
                                        'order_data'    => $order_data,
                                    );
									fre_show_order_by_type($type, $args);
							} ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>