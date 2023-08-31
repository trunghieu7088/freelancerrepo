<?php

/**
 * @package Escrow config and settings
 */

// add filter to add escrow settings page
add_filter('ae_admin_menu_pages', 'fre_escrow_settings');
function fre_escrow_settings( $pages ) {

    $options = AE_Options::get_instance();
    $sections           = array();
    $escrow_fields      = array();
    $escrow_fields[]    = array(
        'args' => array(
            'title' => __("Using Escrow", ET_DOMAIN) ,
            'id' => 'use-escrow',
            'class' => '',
            'desc' => __("Enabling this will activate the Escrow system. Dispute feature is only activated when you use the Escrow system.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'use_escrow',
                'type' => 'switch',
                'title' => __("use escrow", ET_DOMAIN) ,
                'name' => 'use_escrow',
                'class' => ''
            )
        )
    );
    $escrow_fields[]   = array(
        'args' => array(
            'title' => __("Commission", ET_DOMAIN) ,
            'id' => 'commission-amount',
            'class' => '',
            'desc' => __("Decide the amount of commission to be paid for using the service.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'commission',
                'type' => 'text',
                'title' => __("commission", ET_DOMAIN) ,
                'name' => 'commission',
                'class' => 'positive_int'
            )
        )
    );
    $escrow_fields[]   = array(
        'args' => array(
            'title' => __("Commission type", ET_DOMAIN) ,
            'id' => 'commission-type',
            'class' => '',
            'desc' => __("Select the type of commission you want to use.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'commission_type',
                'type' => 'select',
                'title' => __("commission", ET_DOMAIN) ,
                'name' => 'commission_type',
                'class' => '',
                'data' => array(
                        'percent' => __("By percentage", ET_DOMAIN) ,
                        'currency' => __("By specific amount", ET_DOMAIN)
                )
            )
        )
    );
    $escrow_fields[]   = array(
        'args' => array(
                'title' => __("Payer of commission", ET_DOMAIN) ,
                'id' => 'commision-fees',
                'class' => '',
                'desc' => __("Select the user role to pay for the commission.", ET_DOMAIN)
            ) ,
            'fields' => array(
                array(
                    'id' => 'payer_of_commission',
                    'type' => 'select',
                    'title' => __("Payer of fees", ET_DOMAIN) ,
                    'name' => 'payer_of_commission',
                    'class' => '',
                    'data' => array(
                            'project_owner' => __("Project owner", ET_DOMAIN) ,
                            'worker' => __("Freelancer", ET_DOMAIN),
                    )
                )
            )
    );

    $escrow_fields = apply_filters('fre_escrow_fields_setting',$escrow_fields);
    $sections[] = array(
        'args' => array(
            'title' => __("Settings", ET_DOMAIN) ,
            'id' => 'escrow-settings',
            'icon' => 'y',
            'class' => ''
        ) ,
        'groups' => $escrow_fields,
    );

    $pp_group_fields    = array();
    $pp_group_fields[]  = array(
        'args' => array(
            'title' => __("Payment Test Mode", ET_DOMAIN) ,
            'id' => 'payment-test-mode',
            'class' => 'payment-test-mode  et-refresh',
            'desc' => __("Enabling this will allow you to test payment without charging your account.", ET_DOMAIN) ,
            // 'name' => 'currency'
        ) ,
        'fields' => array(
            array(
                'id' => 'test-mode',
                'type' => 'switch',
                'title' => __("Align", ET_DOMAIN) ,
                'name' => 'test_mode',
                'class' => 'option-item bg-grey-input  et-refresh'
            )
        )
    );
    $pp_group_fields[] = array(
        'args' => array(
            'title' => __("Manual Transfer", ET_DOMAIN) ,
            'id' => 'manual_transfer-escrow',
            'class' => '',
            'desc' => __("Enabling this will allow you to manually transfer the money when the project's completed.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'manual_transfer',
                'type' => 'switch',
                'title' => __("Manual Transfer", ET_DOMAIN) ,
                'name' => 'manual_transfer',
                'class' => ''
            )
        )
    );
    $use_escrow      = ae_get_option( 'use_escrow', 0 );
    $escrow_using    = ae_get_option('escrow_system_using', true);

    //$escrow_using = fre_get_escrow_using();


    $escrow_options     = array();
     $escrow_options[0] = __('Set an escrow system',ET_DOMAIN);
    $credit_txt =  $using_pp_txt = '';
    global $has_at_least_escrow;
    $has_at_least_escrow = false;

    if( ae_get_option('user_credit_system', false) ){
        if($escrow_using === 'credit_escrow' && $use_escrow)
            $credit_txt = __(' (Enabling)',ET_DOMAIN);
        else if(!$escrow_using || $escrow_using == '' ){
            $credit_txt =__(' (Default)',ET_DOMAIN);
        }
        $escrow_options['credit_escrow'] =  __("Fre Credit System", ET_DOMAIN).$credit_txt;
        $has_at_least_escrow= true;
    } else if(  $escrow_using === 'credit_escrow' ){
        $escrow_options['credit_escrow'] = 'NA';
    }

    $escrow_options = apply_filters('fre_escrow_options', $escrow_options, $escrow_using, $use_escrow);

    if( is_show_pp_adaptive() ){

        if($escrow_using === 'paypal_adaptive' && $use_escrow) $using_pp_txt = __(' (Enabling)',ET_DOMAIN);

        $escrow_options['paypal_adaptive'] =  __("PayPal Adaptive Escrow", ET_DOMAIN).$using_pp_txt;
        $has_at_least_escrow = true;
    }
    $select_desc = __("Refresh page after set this option,to see to match setting.", ET_DOMAIN);
    if(! $has_at_least_escrow){
        $select_desc = __('There is no escrow system available in your site',ET_DOMAIN);
        $escrow_options['credit_escrow'] = 'No Escrow Avaible';
    }
    $pp_group_fields[] = array(
        'args' => array(
            'title' => __("Select to Enabling an Scrow System", ET_DOMAIN) ,
            'id' => 'escrow-using',
            'class' => '',
            'desc' => $select_desc,
        ) ,
        'fields' => array(
            array(
                'id' => 'escrow_system_using',
                'type' => 'select',
                'title' => __("Escrow System", ET_DOMAIN) ,
                'name' => 'escrow_system_using',
                'class' => 'et-refresh',
                'data' => $escrow_options
            )
        )
    );
    if( is_show_pp_adaptive() ){
        if( $escrow_using === 'paypal_adaptive' ){
           $pp_group_fields =  get_pp_adaptive_settings($pp_group_fields);
        } // pp_adaptive_fields
    }

    $sections[] = array(
        'args' => array(
            'title' => __("Gateways", ET_DOMAIN) ,
            'id' => 'escrow-gateways',
            'icon' => '$',
            'class' => 'payment-gateways'
        ) ,
        'groups' => apply_filters('fre_escrow_payment_gateway_settings', $pp_group_fields, $escrow_using)
    );

    $temp = array();
    foreach ($sections as $key => $section) {
        $temp[] = new AE_section($section['args'], $section['groups'], $options);
    }

    $orderlist = new AE_container(array(
        'class' => 'escrow-settings',
        'id' => 'settings',
    ) , $temp, $options);
    $pages[] = array(
        'args' => array(
            'parent_slug' => 'et-overview',
            'page_title' => __('Escrow', ET_DOMAIN) ,
            'menu_title' => __('ESCROW CONFIGURATION', ET_DOMAIN) ,
            'cap' => 'administrator',
            'slug' => 'fre-escrow',
            'icon' => '%',
            'desc' => __("Setting up a trustworthy environment for freelancers and employers.", ET_DOMAIN)
        ) ,
        'container' => $orderlist
    );

    return $pages;
}


function action_before_save_option($name){
    if($name == 'escrow_paypal_api'){
        $value = $_REQUEST['value'];
        $exploded = array();
        parse_str($value, $exploded);
        if(isset($exploded['username']) && !empty($exploded['username'])){
            $ppadaptive = AE_PPAdaptive::get_instance();
            $order_data = array(
                    'accountIdentifier.emailAddress' =>  $exploded['username'],
                    'matchCriteria' => 'NONE',
                    'requestEnvelope' => 'en_US'
                );
            $response = $ppadaptive->getVerifiedAccount($order_data);

            if($response->responseEnvelope->ack == 'Failure'){
                $error = $response->error;
                $mess = $error[0]->message;
                $response = array(
                    'success' => false,
                    'msg' => $mess
                );
                wp_send_json($response);
                return;
            }
        }
    }
}
// disable veryfied paypal business account.
// add_action('ae_before_save_option', 'action_before_save_option', 10, 1);