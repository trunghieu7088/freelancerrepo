<?php
/**
 * Get lists page
 * @author ThanhTu
 * @since 1.0
 * @return Array
 */
function fre_get_list_pages(){
    // update option page deposit credit from type slug to ID

    $args = array(
        'posts_per_page'   => -1,
        'offset'           => 0,
        'orderby'          => 'title',
        'order'            => 'DESC',
        'post_type'        => 'page',
        'post_status'      => 'publish',
    );
    $posts_array = new WP_Query( $args );

    $posts_array = $posts_array->posts;
    $array = array();
    $array[0] = __('Select page', ET_DOMAIN);
    foreach ($posts_array as $key => $value) {
        $title = $value->post_title;
        $array[$value->ID] = $title;
        $page_template = get_post_meta($value->ID, '_wp_page_template', true);

    }
    return $array;
}



add_filter('ae_admin_menu_pages', 'fre_pp_subscription_menu');
function fre_pp_subscription_menu($pages) {
    $sections = array();
    $options = AE_Options::get_instance();
    $list_pages         = fre_get_list_pages();

    /**
     * ae fields settings
     */
    $genera_fields = array();
    $genera_fields[] = array(
        'args' => array(
            'title' => __("PayPal API Test Mode", ET_DOMAIN) ,
            'id' => 'pp_test_mode',
            'class' => '',
            'desc' => __("Enabling this will allow users use paypal recruite payment in test mode.Refresh page(press f5) after change this option to get the fields match with the current mode.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'pp_test_mode',
                'type' => 'switch',
                'title' => __("Enbale Test Mode", ET_DOMAIN) ,
                'name' => 'pp_test_mode',
                'class' => '',
                'default' => 1,
            )
        )
    );
    $genera_fields[] = array(
        'args' => array(
            'title' => __("Show Checkout Via Credit Card ", ET_DOMAIN) ,
            'id' => 'allow_credit_btn',
            'class' => '',
            'desc' => __("Enabling this will allow users  checkout directly onsite via  (Visa/master) card.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'allow_credit_btn',
                'type' => 'switch',
                'title' => __("Show Credit Cart Checkout", ET_DOMAIN) ,
                'name' => 'allow_credit_btn',
                'class' => '',
                'default' => 0,
            )
        )
    );
    $genera_fields[] = array(
            'args' => array(
                'title' => __("PayPal Business Account", ET_DOMAIN) ,
                'id' => 'paypal_email',
                'class' => '',
                'desc' => __("Your PayPal email account - Business PayPal Account", ET_DOMAIN)
            ) ,
            'fields' => array(
                array(
                    'id' => 'paypal_email',
                    'type' => 'text',
                    'name' => 'paypal_email',
                    'placeholder' => __("eg:abc@gmail.com", ET_DOMAIN) ,
                    'class' => '',
                    'default'=> '',
                )
            )
        );

    $test_mode = ae_get_option('pp_test_mode');


    if($test_mode){

        $genera_fields[] = array(
            'args' => array(
                'title' => __("Test Client ID ", ET_DOMAIN) ,
                'id' => 'test_pp_client_id',
                'class' => '',
                'desc' => __("Test client ID  in your PAYPAL account dashboard of test mode.", ET_DOMAIN)
            ) ,
            'fields' => array(
                array(
                    'id' => 'test_pp_client_id',
                    'type' => 'text',
                    'name' => 'test_pp_client_id',
                    'placeholder' => __("eg:P-0NP20713UG787752SL2NMREQ", ET_DOMAIN) ,
                    'class' => '',
                    'default'=> 'P-0NP20713UG787752SL2NMREQ',
                )
            )
        );

        $genera_fields[] =array(
            'args' => array(
                'title' => __("Test App Secret  key", ET_DOMAIN) ,
                'id' => 'test_pp_app_secret_key',
                'class' => '',
                'desc' => __("Test secret key in your stripe account dashboard of test mode.", ET_DOMAIN)
            ) ,
            'fields' => array(
                array(
                    'id' => 'test_pp_app_secret_key',
                    'type' => 'text',
                    'name' => 'test_pp_app_secret_key',
                    'placeholder' => __("eg:sk_test_NDTXZMeG0rjCUfDlG00otvwf", ET_DOMAIN) ,
                    'class' => '',
                    'default'=> 'sk_test_NDTXZMeG0rjCUfDlG00otvwf',
                )
            )
        );
        // $genera_fields[] =array(
        //     'args' => array(
        //         'title' => __("Test Endpoint Secret", ET_DOMAIN) ,
        //         'id' => 'endpoint_secret_test',
        //         'class' => '',
        //         'desc' => __("Endpoint secret value in dashboard of your stripe account. <a target='_blank' href='https://dashboard.stripe.com/test/webhooks'> Go this url</a> to get this value.", ET_DOMAIN)
        //     ) ,
        //     'fields' => array(
        //         array(
        //             'id' => 'endpoint_secret_test',
        //             'type' => 'text',
        //             'name' => 'endpoint_secret_test',
        //             'placeholder' => __('eg:whsec_Odsrt0loSRrOuAhfV82q4U8mc5iLtENh', ET_DOMAIN) ,
        //             'class' => '',
        //             'default'=> 'whsec_Odsrt0loSRrOuAhfV82q4U8mc5iLtENh',
        //         )
        //     )
        // );
    } else {
        // real mode



        $genera_fields[] = array(
            'args' => array(
                'title' => __("Live Client ID", ET_DOMAIN) ,
                'id' => 'pp_client_id',
                'class' => '',
                'desc' => __("Publishable key in your stripe account dashboard.", ET_DOMAIN)
            ) ,
            'fields' => array(
                array(
                    'id' => 'pp_client_id',
                    'type' => 'text',
                    'name' => 'pp_client_id',
                    'placeholder' => __("eg:pk_live_dimWgxFyh9nqQPbeOlzpnQOh", ET_DOMAIN) ,
                    'class' => '',
                    'default'=> 'pk_live_dimWgxFyh9nqQPbeOlzpnQOh',
                )
            )
        );

        $genera_fields[] = array(
            'args' => array(
                'title' => __("Live Secret Key", ET_DOMAIN) ,
                'id' => 'pp_secret_key',
                'class' => '',
                'desc' => __("Secret key in your stripe account dashboard.", ET_DOMAIN)
            ) ,
            'fields' => array(
                array(
                    'id' => 'pp_secret_key',
                    'type' => 'text',
                    'name' => 'pp_secret_key',
                    'placeholder' => __("eg:EM08bS6Ghxtxg_1PJeVxBGfo88wzD9uQTe47MNVJutbwCXKGarlez4SsnWg3ODPQOMYYCkCLf-JzjGjO", ET_DOMAIN) ,
                    'class' => '',
                    'default'=> 'sk_live_P6WF5lXRWjuEWH8TkLBTBJkP',
                )
            )
        );
        // $genera_fields[] =   array(
        //     'args' => array(
        //         'title' => __("Live Endpoint Secret", ET_DOMAIN) ,
        //         'id' => 'endpoint_secret',
        //         'class' => '',
        //         'desc' => __("Endpoint secret value in dashboard of your stripe account. <a target='_blank' href='https://dashboard.stripe.com/test/webhooks'> Go this url</a> to get this value.", ET_DOMAIN)
        //     ) ,
        //     'fields' => array(
        //         array(
        //             'id' => 'endpoint_secret',
        //             'type' => 'text',
        //             'name' => 'endpoint_secret',
        //             'placeholder' => __('eg:whsec_hWNF2grNSsrmSeWFPwZIEFtHoKWFMlo0', ET_DOMAIN) ,
        //             'class' => '',
        //             'default'=> 'whsec_hWNF2grNSsrmSeWFPwZIEFtHoKWFMlo0',
        //         )
        //     )
        // );

    }
     $genera_fields[]=  array(
        'args' => array(
            'title' => __("Success Page URL", ET_DOMAIN) ,
            'id' => 'success_page_url',
            'class' => '',
            'desc' => __("The page system auto redirect after checkout successfull.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'success_page_url',
                'type' => 'select',
                'title' => __("The page system auto redirect after checkout successfull.)", ET_DOMAIN) ,
                'name' => 'success_page_url',
                'placeholder' => __("eg:https://yourdomain.com", ET_DOMAIN) ,
                'class' => '',
                'default'=> 0,
                'data' => $list_pages
            )
        )
    );
    $genera_fields[]=   array(
        'args' => array(
            'title' => __("Cancel Page URL", ET_DOMAIN) ,
            'id' => 'cancel_page_url',
            'class' => '',
            'desc' => __("The page system auto redirect after cancel checkout.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'cancel_page_url',
                'type' => 'select',
                'title' => __("The page system auto redirect after cancel checkout.", ET_DOMAIN) ,
                'name' => 'cancel_page_url',
                'placeholder' => __("eg:http://yourdomain.com/cancel/", ET_DOMAIN) ,
                'class' => '',
                'default'=> 0,
                'data' => $list_pages
            )
        )
    );

    $sections[] = array(
        'args' => array(
            'title' => __("General", ET_DOMAIN) ,
            'id' => 'genera_section',
            'icon' => 'F',
            'class' => ''
        ) ,

        'groups' => $genera_fields
    );

    /**
     * ae fields settings
     */


    $sections[] = array(
        'args' => array(
            'title' => __("Employer Package", ET_DOMAIN) ,
            'id' => 'fre_plans',
            'icon' => 'z',
            'class' => ''
        ) ,

        'groups' => array(

            /**
             * package plan list
             */


            array(
                'type' => 'list',
                'args' => array(
                    'title'        => __( "Employer Recruit Payment", ET_DOMAIN ),
                    'id'           => 'list-package',
                    'class'        => 'list-package',
                    'desc'         => 'Enable pay to bid to use list this.',
                    'name'         => 'recruit_pack',
                    'custom_field' => 'recruit_pack'
                ),

                'fields' => array(
                    'form' => TEMPLATEPATH.'/admin-template/package-form.php',
                    'form_js' => TEMPLATEPATH.'/admin-template/package-form-js.php',
                    'js_template' => TEMPLATEPATH.'/admin-template/package-js-item.php',
                    'template' => TEMPLATEPATH.'/admin-template/package-item.php',
                    'fullpath'=>true
                )
            )

        )
    );
    $sections[] = array(
        'args' => array(
            'title' => __("Freelancer Bid Plans", ET_DOMAIN) ,
            'id' => 'emp_plans',
            'icon' => 'z',
            'class' => ''
        ) ,

        'groups' => array(

            /**
             * package plan list
             */
            array(
                'type' => 'list',
                'args' => array(
                    'title' => __("Employer Subscription Plans", ET_DOMAIN) ,
                    'id' => 'list-membership-plan',
                    'custom_field' => 'pack',
                    'id'    => 'list-package',
                    'class' => 'list-package',
                    'desc'  => '',
                    'name'  => 'pack',

                ) ,
                'fields' => array(
                    'form' => TEMPLATEPATH.'/admin-template/package-form.php',
                    'form_js' => TEMPLATEPATH.'/admin-template/package-form-js.php',
                    'js_template' => TEMPLATEPATH.'/admin-template/package-js-item.php',
                    'template' => TEMPLATEPATH.'/admin-template/package-item.php',
                    'fullpath'=>true
                )
            )

        )
    );
    $doc_fields = array();
    $doc_fields[] = array(
        'args' => array(
            'title' => __("Documentation", ET_DOMAIN) ,
            'id' => 'pp_recruit_note',
            'class' => '',
            'desc' => __("Suggest setting to make this plugins works smooth.", ET_DOMAIN)
        ) ,
        'fields' => array(
            array(
                'id' => 'pp_recruit_note',
                'type' => 'desc',
                'title' => __("ALl NOTE", ET_DOMAIN) ,
                'name' => 'pp_recruit_note',
                'class' => '',
                'text' => get_pp_recruit_payment_note(),
                'default' => 1,
            )
        )
    );
    $sections[] = array(
        'args' => array(
            'title' => __("Docs", ET_DOMAIN) ,
            'id' => 'stripe_docs',
            'icon' => 'F',
            'class' => ''
        ) ,

        'groups' => $doc_fields
    );

    foreach ($sections as $key => $section) {
        $temp[] = new AE_section($section['args'], $section['groups'], $options);
    }

    $orderlist = new AE_container(array(
        'class' => 'field-settings',
        'id' => 'settings',
    ) , $temp, $options);

    $pages[] = array(
        'args' => array(
            'parent_slug' => 'et-overview',
            'page_title' => __('Paypal Recruit Payment', ET_DOMAIN) ,
            'menu_title' => __('Paypal Recruit Payment Plans', ET_DOMAIN) ,
            'cap' => 'administrator',
            'slug' => 'paypal-recruit-payment',
            'icon' => 'x',
            'desc' => __("Easily add custom fields for your content", ET_DOMAIN)
        ) ,
        'container' => $orderlist
    );

    return $pages;
}
