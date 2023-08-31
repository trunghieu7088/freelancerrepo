<?php
function get_pp_adaptive_settings($pp_group_fields = array() ){
	$api_link = " <a class='find-out-more' target='_blank' rel='noopener noreferrer' href='http://docs.enginethemes.com/article/293-set-up-the-paypal-adaptive-in-escrow-system' >"
                    .__("Read more here", ET_DOMAIN).
                " <span class='icon' data-icon='i' ></span></a>";
	$no_support_link = "<a class='find-out-more' target='_blank' href='https://developer.paypal.com/docs/archive/adaptive-payments/'>View Detail <span class='icon' data-icon='i' ></span></a>";

    $des =sprintf(__("<strong class='warning-important'>Important:</strong> <b>Adaptive Payments is not available</b> for new integrations. %s.", ET_DOMAIN), $no_support_link);
    $des.='<br />';
    $des.=sprintf( __("You need to apply for PayPal Adaptive API and configure correctly to use this feature %s.", ET_DOMAIN), $api_link);

    $pp_group_fields[] = array(
        'args' => array(
        'title' => __("Paypal Adaptive Settings", ET_DOMAIN) ,
        'id' => 'use-escrow-paypal',
        'class' => '',
        'name' => 'escrow_paypal',
        'desc' => $des,
        ) ,
        'fields' => array(
            array(
            'id' => 'use_escrow_paypal',
            'type' => 'text',
            'label' => __("Your paypal business email", ET_DOMAIN) ,
            'name' => 'business_mail',
            'class' => 'require multiemails'
            ),
            array(
                'id' => 'paypal_fee',
                'type' => 'select',
                'title' => __("Paypal fees", ET_DOMAIN) ,
                'label' => __("Paypal fees", ET_DOMAIN) ,
                'name' => 'paypal_fee',
                'class' => 'required',
                'data' => array(
                // 'SENDER' => __("Sender pays all fees", ET_DOMAIN) ,
                'PRIMARYRECEIVER' => __("Admin will pay all fees", ET_DOMAIN),
                'EACHRECEIVER' => __("Both admin & freelancer pay the fee", ET_DOMAIN),
                'SECONDARYONLY' => __("Freelancers will pay all fees", ET_DOMAIN)
                )
            )
        )
    );
    $pp_group_fields[] =array(
        'args' => array(
            'title' => __("Paypal Adaptive API", ET_DOMAIN) ,
            'id' => 'use-escrow-paypal',
            'class' => '',
            'name' => 'escrow_paypal_api',
            // 'desc' => __("Your Paypal Adaptive API", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'username',
                'type' => 'text',
                //'title' => __("Your paypal API username", ET_DOMAIN) ,
                'name' => 'username',
                'label' => __("Your paypal API username", ET_DOMAIN) ,
                'class' => ''
            ),
            array(
                'id' => 'password',
                'type' => 'text',
                //'placeholder' => __("Your paypal API password", ET_DOMAIN) ,
                'label' => __("Your paypal API password", ET_DOMAIN) ,
                'name' => 'password',
                'class' => ''
            ),
            array(
                'id' => 'signature',
                'type' => 'text',
                'label' => __("Your paypal API signature", ET_DOMAIN) ,
                'name' => 'signature',
                'class' => ''
            ),
            array(
                'id' => 'appID',
                'type' => 'text',
                'label' => __("Your Paypal Adaptive AppID", ET_DOMAIN) ,
                'name' => 'appID',
                'class' => ''
            )
        )
    ); // end pp fields
	return $pp_group_fields;
}