<?php
global $user_ID, $ae_post_factory;
/*
if(get_post_meta($product->ID,'payment_meta',true)){
    ?>
    <script>
        window.location="<?php echo home_url("/") ?>";
    </script>
    <?php
}
*/
$mjob_object = $ae_post_factory->get( 'mjob_post' );
$current = $mjob_object->convert( get_post($product->post_parent) );

$price=get_post_meta($product->post_parent,'et_budget',true);
$fee=get_post_meta($product->ID,'claim_fee',true);
$total = ( float )round($price*($fee/100),2);

/* Get extra services */
$extras_ids = array();
if ( isset( $_GET['extras_ids'] ) ) {
    $extras_ids = $_GET['extras_ids'];
}
if ( !empty($extras_ids ) ) {
    foreach ( $extras_ids as $key => $value ) {
        $extra = mje_extra_action()->get_extra_of_mjob( $value, $product->ID );
        if ($extra) {
            $total += ( float ) $extra->et_budget;
        } else {
            unset($extras_ids[$key]);
        }
    }
}
//custom code 18th jun 2024 
$ae_option = new AE_Options;
$fee = ($ae_option->get_option('claim_price_fixed_value') <> "") ? $ae_option->get_option('claim_price_fixed_value') : 10;
$total = ( float )$fee;
//end custom code
$total_text = mje_format_price( $total );

// Generate order args
$order_args = array();

$default_order_args = array(
    'post_parent' => $product->ID,
    'et_budget' => $total,
    'total' => $total,
    'post_type' => $product->post_type,
    'method' => 'create',
    '_wpnonce' => de_create_nonce('ae-mjob_post-sync'),
);

// Opening message
if( ! empty( $product->opening_message ) ) {
    $default_order_args['opening_message'] = $product->opening_message;
}

// Merge order args with default order args
$order_args = wp_parse_args( $order_args, $default_order_args );

if( $user_ID != $product->post_author or 1 ) :
?>
    <div class="claim-checkout">
        <div class="title-top-pages">
            <p class="block-title"><?php _e('Checkout details', 'mje_verification') ?></p>
        </div>

        <div class="row order-information">
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 items-chosen">
                <div class="block-items">
                    <p class="title-sub"><?php _e('Microjob name', 'mje_verification'); ?></p>
                    <div class="mjob-list mjob-list--horizontal">
                        <?php mje_get_template( 'template/mjob-item.php', array( 'current' => $current ) ); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 order mjob-order-info">
                <div class="title-sub"><?php _e('Job verification price', 'mje_verification'); ?></div>
                <!-- custom code jun 18th 2024 -->
               <!-- <div class="mjob-order-info row">
						<div class="title-sub-claim col-lg-6 col-md-6 col-sm-6 col-xs-6"><?php _e('Mjob price', 'mje_verification' ); ?></div>
                    	<div class="price col-lg-6 col-md-6 col-sm-6 col-xs-6 float-right"><span title=""><?php echo mje_format_price($price); ?></span></div>
                </div> -->
                   <!-- end custom -->
                <div class="mjob-order-info row">
				    	<div class="title-sub-claim col-lg-6 col-md-6 col-sm-6 col-xs-6"><?php _e('Job verification fee', 'mje_verification' ); ?></div>
                    	<div class="price col-lg-6 col-md-6 col-sm-6 col-xs-6 float-right"><span title=""><?php echo mje_format_price($fee); ?></span></div>
                </div>
             
                <hr>
                <div class="mjob-order-info row">
						<div class="title-sub col-lg-6 col-md-6 col-sm-6 col-xs-6"><?php _e('Total', 'mje_verification' ); ?></div>
                    	<div class="price col-lg-6 col-md-6 col-sm-6 col-xs-6 float-right"><span><?php echo $total_text; ?></span></div>
                </div>
                <div class="float-right action-order">

                    <button class="<?php mje_button_classes( array( 'btn-checkout', 'mjob-btn-checkout', 'waves-effect', 'waves-light' ) ); ?>"><?php _e('Checkout now', 'mje_verification'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php
    //echo '<script type="text/template" id="mjob_single_data" >' . json_encode($product) . '</script>';
    echo '<script type="text/template" id="mje-checkout-info">' . json_encode( $order_args ) . '</script>';
    echo '<script type="text/template" id="mje-extra-ids">' . json_encode( $extras_ids ) . '</script>';
    ?>
<?php else: ?>
    <div class="error-block">
        <p><?php _e('You cannot make an order for your Job Verification', 'mje_verification'); ?></p>
        <p><?php printf(__('Please browsing other <a href="%s">mJobs</a> to find the correct one.', 'mje_verification'), get_post_type_archive_link('mjob_post')); ?></p>
    </div>
<?php endif; ?>
