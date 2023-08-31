<?php
// silence is gold

function get_escrow_using(){

}

if( !function_exists('is_use_stripe_escrow') ){
    /**
     * Check if use stripe  escrow
     * @param void
     * @return bool true/false, true if use stripe escrow and false if don't
     * @since 1.0
     * @package AE_ESCROW
     * @category STRIPE
     * @author Jack Bui
     */
    function is_use_stripe_escrow(){


        $use_escrow = ae_get_option( 'use_escrow', 0 );
        if( ! $use_escrow ){
            return false;
        }

        $escrow_using   = ae_get_option('escrow_system_using', true);

        if( $escrow_using !== 'stripe_escrow'){
            return false;
        }
        $stripe_api       = ae_get_option( 'escrow_stripe_api', false );

        if( empty($stripe_api) ){
            return false;
        }

        if( ! function_exists( 'ae_stripe_recipient_field' ) ) {
            return false;
        }
        $client_id        = isset( $stripe_api['client_id'] ) ? trim($stripe_api['client_id']) : '';
        $client_secret    = isset( $stripe_api['client_secret'] ) ? trim($stripe_api['client_secret']) : '';
        $client_public    = isset( $stripe_api['client_public'] ) ? trim($stripe_api['client_public']) : '';

        $testmode = ae_get_option('test_mode', true);
        if( $testmode ){
            $client_id        = isset( $stripe_api['test_client_id'] ) ? trim($stripe_api['test_client_id']) : '';
            $client_secret    = isset( $stripe_api['test_client_secret'] ) ? trim($stripe_api['test_client_secret']) : '';
            $client_public    = isset( $stripe_api['test_client_public'] ) ? trim($stripe_api['test_client_public']) : '';
        }


        if( empty($client_id) || empty($client_secret) || empty($client_public) ){

            return false;
        }



        return true;
    }
}

if( ! function_exists('is_show_pp_adaptive') ):
    /**
     * @since v1.8.16
    */
    function is_show_pp_adaptive(){
        //if( version_compare(ET_VERSION,'1.8.1') < 0){
            // show if using version 1.8 and previous

        $ppadaptive_settings = ae_get_option('escrow_paypal');
        // the admin's paypal business account
        $primary    = isset($ppadaptive_settings['business_mail']) ? $ppadaptive_settings['business_mail']: false;

        $api        = ae_get_option('escrow_paypal_api');

        $appID      = isset($api['appID']) ? $api['appID'] : false;

        if ( $primary && $appID ){
            // show if admin set bisiness_mail field && $appID, just a simple check == admin using adaptive
            return true;
        }

        return apply_filters('enable_pp_adaptive', false);
    }
endif;
require_once dirname(__FILE__) . '/template.php';
require_once dirname(__FILE__) . '/escrow-settings.php';
if(ae_get_option('use_escrow')) {
	require_once dirname(__FILE__) . '/ppadaptive.php';
	require_once dirname(__FILE__) . '/paypal.php';
}

function fre_process_escrow($payment_type, $data) {
    $payment_return = array(
        'ACK' => false
    );

    if ($payment_type == 'paypaladaptive') {
        $ppadaptive = AE_PPAdaptive::get_instance();

        $response = $ppadaptive->PaymentDetails($data['payKey']);
        $payment_return['payment_status'] = $response->responseEnvelope->ack;

        // email confirm
        if (strtoupper($response->responseEnvelope->ack) == 'SUCCESS') {
            $payment_return['ACK'] = true;
            // UPDATE order
            $paymentInfo = $response->paymentInfoList->paymentInfo;
            if ($paymentInfo[0]->transactionStatus == 'COMPLETED') {
                wp_update_post(array(
                    'ID' => $data['order_id'],
                    'post_status' => 'publish'
                ));
                // assign project
                $bid_action = Fre_BidAction::get_instance();
                $bid_action->assign_project($data['bid_id']);
            }

            if ($paymentInfo[0]->transactionStatus == 'PENDING') {
                //pendingReason
                $payment_return['pending_msg'] = $ppadaptive->get_pending_message($paymentInfo[0]->pendingReason);
                $payment_return['msg'] = $ppadaptive->get_pending_message($paymentInfo[0]->pendingReason);
            }
        }

        if (strtoupper($response->responseEnvelope->ack) == 'FAILURE') {
            $payment_return['msg'] = $response->error[0]->message;
        }
    }
    return apply_filters( 'fre_process_escrow', $payment_return, $payment_type, $data);
}
/**
 * @since version 1.8.6.2
 * @author: danng
 * @param bid: int or array/object
 * get detail for 1 accept bid info  - it use to show in modal accept bid such as commision, bid_budget, total amout must deposit ...
 * use this method in 2 case: in

*/
function fre_get_deposit_info( $bid = 0){

    global $user_ID;

    $error = array(
        'success' => false,
        'msg' => __('Invalid bid', ET_DOMAIN)
    );
    if ( ! $bid )
        return new WP_Error( 'empty_id', __( "Bid is empty.", "enginetheme" ) );

    if( is_numeric( $bid ) ){
        $bid = get_post($bid);
    }

    // check bid is valid
    if (! $bid || is_wp_error( $bid ) || $bid->post_type != BID) {
        wp_send_json($error);
        return new WP_Error( 'invalid_bid', __( "Invalid bid.", "enginetheme" ) );
    }

    $bid_budget = get_post_meta( $bid->ID, 'bid_budget', true );

    // get commission settings
    $commission = ae_get_option('commission', 0);
    $commission_fee = $commission;

    // caculate commission fee by percent
    $commission_type = ae_get_option('commission_type');
    if ($commission_type != 'currency') {
        $commission_fee = ((float)($bid_budget * (float)$commission)) / 100;
    }

    $commission         = fre_price_format($commission_fee);
    $payer_of_commission = ae_get_option('payer_of_commission', 'project_owner');
    if ($payer_of_commission == 'project_owner') {
        $total = (float)$bid_budget + (float)$commission_fee;
    }
    else {
        $commission = 0;
        $total = $bid_budget;
    }
    $number_format = ae_get_option('number_format');
    $decimal = (isset($number_format['et_decimal'])) ? $number_format['et_decimal'] : get_theme_mod('et_decimal', 2);
    $data = array(
        'budget'        => $bid_budget,
        'commission'    => $commission,
        'total'         => round((double)$total, $decimal),
        );
    $data = apply_filters( 'ae_accept_bid_infor', $data);
    return array(
        'budget'            => fre_price_format($data['budget']) ,
        'commission'        => $data['commission'],
        'total'             => fre_price_format($data['total']), // total_with_currencyicon
        'total_pay'      => $data['total'],
        'data_not_format'   => $data
    );
}