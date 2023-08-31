<?php
function membership_mailing_fields(){
    $email_fields   = array();
    $email_fields[]   =  array(
            'args' => array(
                'title' => __("Admin's email who will receive the notification", 'enginethemes') ,
                'id' => 'fre_membership_admin_emails',
                'class' => '',
                'desc' => __("Set admin's emails who will receive the notification. Each email is separated by comma(s).", 'enginethemes')
            ) ,
            'fields' => array(
                array(
                    'id' => 'fre_membership_admin_emails',
                    'type' => 'text',
                    'title' => __("Set admin's emails who will receive the notification. Each email is separated by comma(s).", 'enginethemes') ,
                    'name' => 'fre_membership_admin_emails',
                    'placeholder' => __("abc@example.com, cde@example.com ", 'enginethemes') ,
                    'class' => 'multiemails',
                    'default'=> get_option('admin_email'),
                )
            )
        );

	$email_fields[]=   array(
        'args' => array(
            'title' => __("Membership Email Template", 'enginethemes') ,
            'id' => 'private-message-mail-description-group',
            'class' => '',
            'name' => 'membership_mailing'
        ) ,
        'fields' => array(
        	array(
	            'id' => 'mail-description',
	            'type' => 'desc',
	            'title' => __("Mail description here", 'enginethemes') ,
	            'text' => __("You can use placeholders to include some specific content.", 'enginethemes') . '<a class="icon btn-template-help payment" data-icon="?" href="#" title="View more details"></a>' . '<div class="cont-template-help payment-setting">
	                     [user_login] : ' . __("user's details you want to send mail", 'enginethemes') . '<br />
	                     [plan_name], [plan_price] [expiration_date] : ' . __(" Plan info", 'enginethemes') . '

	                 </div>',
	            'class' => '',
	            'name' => 'mail_description'
            )
        )
    );

    $email_fields[]=    array(
         'args' => array(
             'title' => __("New Subscriber.", 'enginethemes') ,
             'id' => 'fre-subscriber-successful',
             'class' => '',
             'name' => '',
             'desc' => __("Send to buyers after they purchase successfully", 'enginethemes'),
             'toggle' => false
         ) ,
         'fields' => array(
            array(
                 'id' => 'subscriber_successful_mail_template',
                 'type' => 'editor',
                 'title' => '',
                 'name' => 'subscriber_successful_mail_template',
                 'class' => '',
                 'reset' => 1
            )
         )
    );

    $email_fields[]=    array(
         'args' => array(
             'title' => __("Subscription will expire soon", 'enginethemes') ,
             'id' => 'fre-membership-expired',
             'class' => '',
             'name' => '',
             'desc' => __("Send to membership when subscription will expire soon in next time.", 'enginethemes'),
             'toggle' => false
         ) ,
         'fields' => array(
             array(
                 'id' => 'fre_membership_expiry_soon_email_template',
                 'type' => 'editor',
                 'title' => '',
                 'name' => 'fre_membership_expiry_soon_email_template',
                 'class' => '',
                 'reset' => 1
             )
         )
    );
    $email_fields[]=     array(
        'args' => array(
            'title' => __("Subscription plan has been renewed successfully", 'enginethemes') ,
            'id' => '',
            'class' => '',
            'name' => '',
            'desc' => __("Send to members when their subscription plan has been renewed successfully.", 'enginethemes'),
            'toggle' => false
        ) ,
        'fields' => array(
            array(
                'id' => 'fre_membership_auto_renew_success_email',
                'type' => 'editor',
                'title' => __("Auto renew email template to", 'enginethemes') ,
                'name' => 'fre_membership_auto_renew_success_email',
                'class' => '',
                'reset' => 1
            )
        )
    );
    $email_fields[]=    array(
        'args' => array(
            'title' => __("Unable to renew subscription plan", 'enginethemes') ,
            'id' => '',
            'class' => '',
            'name' => '',
            'desc' => __("Send to membership when their subscription will expired today and [no enough credit to renew or inactive the fre_credit extension].", 'enginethemes'),
            'toggle' => false
        ) ,
        'fields' => array(
            array(
                'id' => 'fre_membership_auto_renew_fail_email',
                'type' => 'editor',
                'title' => __("Auto renew email template to", 'enginethemes') ,
                'name' => 'fre_membership_auto_renew_fail_email',
                'class' => '',
                'reset' => 1
            )
        )
    );
    $email_fields[]=     array(
        'args' => array(
            'title' => __("Notify admins when members cancel their subscriptions", 'enginethemes') ,
            'id' => '',
            'class' => '',
            'name' => '',
            'desc' => __("Send to admin when a member cancels his/her subscriptions.", 'enginethemes'),
            'toggle' => false
        ) ,
        'fields' => array(
            array(
                'id' => 'fre_cancel_membership_admin_email',
                'type' => 'editor',
                'title' => '',
                'name' => 'fre_cancel_membership_admin_email',
                'class' => '',
                'reset' => 1
            )
        )
    );
     $email_fields[]=     array(
        'args' => array(
            'title' => __("Notify subscribers when they cancel their subscriptions", 'enginethemes') ,
            'id' => '',
            'class' => '',
            'name' => '',
            'desc' => __("Send to members after they cancel their subscriptions", 'enginethemes'),
            'toggle' => false
        ) ,
        'fields' => array(
            array(
                'id' => 'fre_cancel_membership_email',
                'type' => 'editor',
                'title' => '',
                'name' => 'fre_cancel_membership_email',
                'class' => '',
                'reset' => 1
            )
        )
    );
    return $email_fields;
}

function membership_setting_payment_fields(){
    $testmode = ae_get_option('membership_mode', true);
    $pm_gateway = array();

    $pm_gateway[] =  array(
        'args'   => array(
            'title' => __( "Payment Sanbox(Test Mode)", 'enginethemes' ),
            'id'    => 'membership_mode',
            'class' => 'membership_mode',

        ),
        'fields' => array(
            array(
                'id'    => 'membership_mode',
                'type'  => 'switch',
                'title' => __( "membership_mode", 'enginethemes' ),
                'name'  => 'membership_mode',
                'class' => 'option-item bg-grey-input et-refresh'
            )
        )
    );
    $pm_gateway[] =  array(
        'args'   => array(
            'title' => __( "Enable Stripe Payment Gateway", 'enginethemes' ),
            'id'    => 'enable_mebership_stripe',
            'class' => 'enable_mebership_stripe',
            'desc'  => __( "Enabling this will allow checkout via stripe.", 'enginethemes' ),
        ),
        'fields' => array(
            array(
                'id'    => 'enable_mebership_stripe',
                'type'  => 'switch',
                'title' => __( "Enable Stripe", 'enginethemes' ),
                'name'  => 'enable_mebership_stripe',
                'class' => 'option-item bg-grey-input et-refresh'
            )
        )
    );


    $stripe_fields = array();

    $mebership_stripe = ae_get_option('enable_mebership_stripe', false);
    if($mebership_stripe){
        if($testmode){

            $stripe_fields[] =  array(
                'id' => 'test_publishable_key',
                'type' => 'text',
                'name' => 'test_publishable_key',
                'label' => __("Test Stripe Publishable key", 'enginethemes') ,
                'class' => '',
                'placeholder' => 'ex: pk_test_...'
            );

            $stripe_fields[] =  array(
                'id' => 'test_secret_key',
                'type' => 'text',
                'name' => 'test_secret_key',
                'label' => __("Test Stripe Secret key", 'enginethemes') ,
                'class' => '',
                'placeholder' => 'ex: sk_test_...'
            );

            $stripe_fields[] = array(
                'id' => 'test_signing_secret',
                'type' => 'text',
                'name' => 'test_signing_secret',
                'label' => __("Test Signing secret", 'enginethemes') ,
                'class' => '',
                'desc' => __('Visit <a href="https://dashboard.stripe.com/test/webhooks">Webhook</a> to get Test Signing secret','enginethemes'),
                'placeholder' =>'ex: whsec_...',
            );
        } else {

            $stripe_fields[] =  array(
                'id' => 'live_publishable_key',
                'type' => 'text',
                'name' => 'live_publishable_key',
                'label' => __("Live Stripe Publishable key", 'enginethemes') ,
                'class' => '',
                'placeholder' => 'ex: pk_live_...',
            );
            $stripe_fields[] =  array(
                'id' => 'live_secret_key',
                'type' => 'text',
                'name' => 'live_secret_key',
                'label' => __("Live Stripe Secret key", 'enginethemes') ,
                'class' => '',
                'placeholder' => 'ex: sk_live_...',
            );

            $stripe_fields[] = array(
                'id' => 'live_signing_secret',
                'type' => 'text',
                'name' => 'live_signing_secret',
                'label' => __("Live Signing secret", 'enginethemes') ,
                'class' => '',
                'desc' => __('Visit <a href="https://dashboard.stripe.com/webhooks">Webhook</a> to get Live Signing secret','enginethemes'),
                'placeholder' =>'ex: whsec_...',
            );
    }

    $docs = "<p> <strong>NOTE:</strong> 1 webhook have 1 Signing secret and we have to 'Add endpoit' first to get the Signing secret.</p>";
    $endpoint_url = home_url().'/?rest_route=/stripewebhooks/v1/task';
    $webhook_dashboard = "https://dashboard.stripe.com/webhooks";
    if($testmode){
        $webhook_dashboard = "https://dashboard.stripe.com/test/webhooks";
    }
    $id = "'#endpoint_url'";
    $btn_copy = "<span class='btn-copy-endpoint' id='btn_copy' onclick=\"copyToClipboard('#endpoint_url')\">Copy</span>";
    $stripe_fields[] = array(
        'id' => 'Redirect URL',
        'type' => 'desc',
        'name' => 'redirect_uri',
        'label' => __("Redirect URI", 'enginethemes') ,
        'class' => '',

        'text' => '<div class="form-item stripe-import-warning"><p><img src="'.MEMBERSHIP_URL.'/assets/img/alert.png" width = 20><strong>'.__('Endpoint URL in Webhook settings','enginethemes'). '</strong></p><input class="endpoint-copy" disabled id="endpoint_url" value ="'.$endpoint_url.'" />  &nbsp; '.$btn_copy.'<p>Copy and paste this URL on your Stripe <a target="_blank" href="'.$webhook_dashboard.'">Webhook Endpoint URL </a>.</p> '.$docs.'</div>',
    );

    $pm_gateway[]=    array(
        'args' => array(
            'title' => __("Stripe API", 'enginethemes') ,
            'class' => '',
            'id' => 'membership_stripe_api',
            'name' => 'membership_stripe_api'
            //'desc' => __("Publishable key.", 'enginethemes')
        ) ,
        'fields' => $stripe_fields
    );
}

$pm_gateway[] =  array(
        'args'   => array(
            'title' => __( "Enable PayPal PayMent Gateway", 'enginethemes' ),
            'id'    => 'enable_mebership_paypal',
            'class' => 'enable_mebership_paypal',
            'desc'  => __( "Enabling this will allow checkout via PayPal.", 'enginethemes' ),
        ),
        'fields' => array(
            array(
                'id'    => 'enable_mebership_paypal',
                'type'  => 'switch',
                'title' => __( "Enable PayPal", 'enginethemes' ),
                'name'  => 'enable_mebership_paypal',
                'class' => 'option-item bg-grey-input et-refresh'
            )
        )
    );

    $enable_paypal = ae_get_option('enable_mebership_paypal', false);
    $paypal_fields = array();
    $label = "Live PayPal API";
    if( $enable_paypal ){
        $note = "<p><strong>NOTE:</strong> Application Type: <strong>Merchant – Accept payments as a merchant (seller)</strong> ";
        $link = "<a target='_blank' href='https://developer.paypal.com/developer/applications'>PayPal Application</a>";
        $tut = "<div class='form-item stripe-import-warning'><p>Create 1 Application in  $link  first, then  get Client ID and Secret(Sandbox Mode). {$note}</p></div>";
        if( $testmode ){
             $label = "Sandbox PayPal API";
             $paypal_fields[] =  array(
            'id' => 'paypal_business',
            'type' => 'text',
            'name' => 'test_paypal_business',
            'label' => __("Sandbox PayPal Business Email", 'enginethemes') ,
            'class' => '',
            'placeholder' => 'PayPalEmail',
        );

        $paypal_fields[] = array(
            'id' => 'test_client_id',
            'type' => 'text',
            'name' => 'test_client_id',
            'label' => __("Sandbox Client ID", 'enginethemes') ,
            'class' => '',
            'desc' => '',
            'placeholder' =>'Get Client ID Application',
        );
        $paypal_fields[] = array(
            'id' => 'test_secret_key',
            'type' => 'text',
            'name' => 'test_secret_key',
            'label' => __("Sandbox Secret", 'enginethemes') ,
            'class' => '',
            'desc' => '',
            'placeholder' =>'',
        );

        } else {

            $tut = "<div class='form-item stripe-import-warning'><p>Create 1 Application in  $link  first, then  get Client ID and Secret(Live Mode).  {$note}</p></div>";
            $paypal_fields[] =  array(
                'id' => 'paypal_business',
                'type' => 'text',
                'name' => 'paypal_business',
                'label' => __("Live PayPal Business Email", 'enginethemes') ,
                'class' => '',
                'placeholder' => 'PayPalEmail',
            );

            $paypal_fields[] = array(
                'id' => 'client_id',
                'type' => 'text',
                'name' => 'client_id',
                'label' => __("Live Client ID", 'enginethemes') ,
                'class' => '',
                'desc' => '',
                'placeholder' =>'',
            );
            $paypal_fields[] = array(
                'id'     => 'secret_key',
                'type'  => 'text',
                'name'  => 'secret_key',
                'label' => __("Live Secret", 'enginethemes') ,
                'class' => '',
                'desc'   => '',
               'placeholder' =>'',
            );
        }

        $paypal_fields[] = array(
            'type' => 'desc',
            'name' => 'redirect_uri',
            'label' => '',
            'class' => '',
            'text' => $tut,
        );

        $pm_gateway[]=    array(
            'args' => array(
                'title' => $label,
                'id' => 'membership_paypal_api',
                'name' => 'membership_paypal_api',
                'class' => '',
                //'desc' => __("Publishable key.", 'enginethemes')
            ) ,
            'fields' => $paypal_fields
        );
    }
    return $pm_gateway;
}
function memberhsip_add_custom_css(){?>

    <style type="text/css">
    .stripe-import-warning img{
        position: relative;
        top: 5px;
        margin-right: 10px;
    }

    .endpoint-copy {
        border: 1px solid #ccc;
        padding: 9px 15px;
        border-radius: 3px;
        display: inline-block;
        min-width: 500px;
        color: #3a3a3a !important;
        font-weight: 500;
        font-size: 15px;
    }
    .btn-copy-endpoint{
        border: 1px solid #ccc;
       padding: 7px 15px;
        cursor: pointer;
        font-weight: 500;
        color: #000;
    }
    .et_subscription_time{
        width: 280px;
    }
    </style>
<?php }

add_action('admin_footer', 'memberhsip_add_custom_css', 9999);
function my_custom_admin_head() {
    $page = isset($_GET['page']) ? $_GET['page'] : '';

     global $hook_suffix; //page_fre-membership

    if($page == 'fre-membership' || substr($page,0,14) == 'fre-membership'   ){ ?>
         <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

         <script type="text/javascript">
            function copyToClipboard(element) {
              var $temp = $("<input>");

              $("body").append($temp);
              $temp.val($(element).val()).select();
              document.execCommand("copy");
              $temp.remove();
              jQuery("#btn_copy").addClass('hightlight');
              var span = document.getElementById("btn_copy");
              span.innerHTML  = "Copied"
            }
        </script>
        <?php
    }



}
add_action( 'admin_head', 'my_custom_admin_head' );