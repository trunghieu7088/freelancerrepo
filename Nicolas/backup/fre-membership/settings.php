<?php

/**
 * Private message setting
 * @param Array $pages setting of payment gateways
 */
require_once FRE_MEMBERSHIP_PATH . '/admin/setting_fields.php';

if( !function_exists('fre_membership_menu' ) ){

    function fre_membership_menu( $pages ){
        $options            = AE_Options::get_instance();
        $log_file_url       = home_url('wp-content/uploads').'/member_log.css';
        $setting_fields = array();

        $setting_fields1[]=    array(
            'args' => array(
                'title' => __("Number days send notification before", 'enginethemes') ,
                'id' => 'fre_membership_number_daily',
                'class' => '',
                'desc' => __("Number day system check and send notification email to membership who will expired within this number days.", 'enginethemes')
            ) ,
            'fields' => array(
                array(
                    'id' => 'number_days_auto_check_membership',
                    'type' => 'text',
                    'title' => __("Set number day the system auto check and send notification.", 'enginethemes') ,
                    'name' => 'number_days_auto_check_membership',
                    'placeholder' => __("7", 'enginethemes') ,
                    'class' => '',
                    'default'=> 7
                )
            )
        );
        $setting_fields[]=    array(
            'args' => array(
                'title' => __("Number of projects an Employer can post for free every month.", 'enginethemes') ,
                'id' => 'fre_number_free_post',
                'class' => '',
                'desc' => __("Number of projects an Employer can post for free every month.", 'enginethemes')
            ) ,
            'fields' => array(
                array(
                    'id' => 'fre_number_free_post',
                    'type' => 'text',
                    'title' => __("Set number day the system auto check and send notification.", 'enginethemes') ,
                    'name' => 'fre_number_free_post',
                    'placeholder' => __("0", 'enginethemes') ,
                    'class' => '',
                    'default'=> 0,
                )
            )
        );
        $list_pages         = fre_membership_get_list_page('[fre_membership_plans]');
        $setting_fields[] = array(
                'args' => array(
                    'title' => __("Membership Page", 'enginethemes') ,
                    'id' => 'fre_membership_plans',
                    'class' => '',
                    'desc' => __("Select a page for your users to show list membership plans. (Only pages having shortcode [fre_membership_plans] are displayed here.)", 'enginethemes')
                ) ,
                'fields' => array(
                    array(
                        'id' => 'fre_membership_plans',
                        'type' => 'select',

                        'name' => 'fre_membership_plans',

                        'class' => '',
                        'data' => $list_pages
                    ),

                )
            );
        $list_pages =    fre_membership_get_list_page('[fre_membership_checkout]');
        $setting_fields[] = array(
                'args' => array(
                    'title' => __("Membership Checkout Page", 'enginethemes') ,
                    'id' => 'fre_membership_checkout',
                    'class' => '',
                    'desc' => __("Select a page for your users to show the checkout form. (Only pages having shortcode [fre_membership_checkout] are displayed here.)", 'enginethemes')
                ) ,
                'fields' => array(
                    array(
                        'id' => 'fre_membership_checkout',
                        'type' => 'select',
                        //'title' => __("Select a page for your users to Deposit Credit. (Only pages having shortcode [membersip_checkout] are displayed here.)", 'enginethemes') ,
                        'name' => 'fre_membership_checkout',
                        'placeholder' => __("eg:deposit", 'enginethemes') ,
                        'class' => '',
                        'default'=> 0,
                        'data' => $list_pages
                    ),

                )
            );
        $list_pages =    fre_membership_get_list_page('[membership_successful_return]');

        $setting_fields[] = array(
            'args' => array(
                'title' => __("Successful Checkout Return Page", 'enginethemes') ,
                'id' => 'membership_successful_return',
                'class' => '',
                'desc' => __("After checkout successul. System auto redirect to this page. (Only pages having shortcode [membership_successful_return] are displayed here.)", 'enginethemes')
            ) ,
            'fields' => array(
                array(
                    'id' => 'membership_successful_return',
                    'type' => 'select',
                    'name' => 'membership_successful_return',
                    'placeholder' => __("eg:deposit", 'enginethemes') ,
                    'class' => '',
                    'default'=> 0,
                    'data' => $list_pages
                ),

            )
        );

        $setting_fields[] = array(
            'args' => array(
                'title' => __("Cancel Checkout Return Page", 'enginethemes') ,
                'id' => 'membership_cancel_return',
                'class' => '',
                'desc' => __("Select a page for your users to redirect when they cancel checkout.", 'enginethemes')
            ) ,
            'fields' => array(
                array(
                    'id' => 'membership_cancel_return',
                    'type' => 'select',
                    'name' => 'membership_cancel_return',
                    'placeholder' => __("eg:deposit", 'enginethemes') ,
                    'class' => '',
                    'default'=> 0,
                    'data' => $list_pages
                ),

            )
        );
        // end group
        if( MEMBERSHIP_DEBUG ){

            $setting_fields[] = array(
                'args' => array(
                    'title' => __("Debug Log File", 'enginethemes') ,
                    'id' => 'debug_log_file',
                    'class' => '',
                    'desc' => '',
                ) ,
                'fields' => array(
                    array(
                        'id' => 'fre_membership_debug',
                        'type' => 'desc',
                        'name' => 'fre_membership_debug',
                        'class' => '',
                        'text' => __("Check Debug log fie<a target='_blank' href='{$log_file_url}'> member_log.css </a>", 'enginethemes'),
                    )
                )
            );
        }
        $section = array(
            'args' => array(
                'title' => __("Membership Settings", 'enginethemes') ,
                'id' => 'membership-settings1',
                'icon' => 'y',
                'class' => ''
            ) ,
            'groups' => $setting_fields,
        );


         $sections[] = array(
        'args' => array(
            'title' => __("General Settings ", 'enginethemes') ,
            'id' => 'membership-settings3',
            'icon' => 'y',
            'class' => ''
        ) ,
        'groups' => $setting_fields
    );

    $sections[] = array(
        'args' => array(
            'title' => __("Payment Gateway", 'enginethemes') ,
            'id' => 'payment-gateway',
            'icon' => 'G',
            'class' => ''
        ) ,
        'groups' => membership_setting_payment_fields(),

    );

    $plan_fields = array();
    $nav_label = __("Employer Membership Plans", 'enginethemes');

    if( ae_get_option('disable_plan', false ) ){
         $section_link = admin_url('admin.php?page=et-settings#section/payment-settings');
        $plan_fields[] = array(
            'args' => array(
                'title' => $nav_label ,
                'id' => 'debug_log_file',
                'class' => '',
                'desc' => __("Employer Membership Plans is disabled. Please disable <a href='{$section_link}'> 'Free to submit listing'</a> option firsts.",'enginethemes'),

            ) ,
            'fields' => array(
                array(
                    'id' => 'fre_membership_debug',
                    'type' => 'desc',
                    'name' => 'fre_membership_debug',
                    'class' => '',
                    'text' => '',
                )
            )
        );

    } else {
       $plan_fields[] = array(
            'type' => 'list',
            'args' => array(
                'title' => $nav_label ,
                'id' => 'list-membership-plan',
                'custom_field' => 'pack',
                'id'    => 'list-package',
                'class' => 'list-package',
                'desc'  => '',
                'name'  => 'pack',

            ) ,
            'fields' => array(
                'form' => FRE_MEMBERSHIP_PATH.'/admin/templates/package-form.php',
                'form_js' => FRE_MEMBERSHIP_PATH.'/admin/templates/package-form-js.php',
                'js_template' => FRE_MEMBERSHIP_PATH.'/admin/templates/package-js-item.php',
                'template' => FRE_MEMBERSHIP_PATH.'/admin/templates/package-item.php',
                'fullpath'=>true
            )
        );

    }
    if( ae_get_option('pay_to_bid', false ) ){
        $plan_fields[]=     array(
            'type' => 'list',
            'args' => array(
                'title'        => __( "Freelancer Membership Plans", 'enginethemes' ),
                'id'           => 'list-package',
                'class'
                   => 'list-package',
                'desc'         => 'Enable pay to bid to use list this.',
                'name'         => 'bid_plan',
                'custom_field' => 'bid_plan'
            ),

            'fields' => array(
                'form'        => FRE_MEMBERSHIP_PATH.'/admin/templates/bid-plan-form.php',
                'form_js'     => FRE_MEMBERSHIP_PATH.'/admin/templates/bid-plan-form-js.php',
                'js_template' => FRE_MEMBERSHIP_PATH.'/admin/templates/bid-plan-js-item.php',
                'template'    => FRE_MEMBERSHIP_PATH.'/admin/templates/bid-plan-item.php',
                'fullpath'=>true
            )
        );
    } else {
        $section_link = admin_url("admin.php?page=et-settings#section/freelancer-settings");
        $plan_fields[] = array(
            'args' => array(
                'title' => __("Freelancer Membership Plans", 'enginethemes') ,
                'id' => 'debug_log_file',
                'class' => '',
                'desc' => __("Freelancer Membership Plans is disabled.Please enable <a href='{$section_link}'> 'Pay to Bid' </a> option first.",'enginethemes'),

            ) ,
            'fields' => array(
                array(
                    'id' => 'fre_membership_debug',
                    'type' => 'desc',
                    'name' => 'fre_membership_debug',
                    'class' => '',
                    'text' => '',
                )
            )
        );
    }


    $sections[] = array(
        'args' => array(
            'title' => __("Membership Plans", 'enginethemes') ,
            'id' => 'membership-plans',
            'icon' => 'U',
            'class' => ''
        ) ,
        'groups' => $plan_fields,

    );

    $sections[] = array(
        'args' => array(
            'title' => __("Email Settings", 'enginethemes') ,
            'id' => 'email-settings',
            'icon' => 'M',

            'class' => ''
        ) ,
        'groups' => membership_mailing_fields(),

    );

    $temp = array();
    foreach ($sections as $key => $section) {
        $temp[] = new AE_section($section['args'], $section['groups'], $options);
    }

        $orderlist = new AE_container(array(
            'class' => 'fre-membership',
            'id' => 'settings',
        ) , $temp, $options);
        $pages[] = array(
            'args' => array(
                'parent_slug' => 'et-overview',
                'page_title' => __('Membership', 'enginethemes') ,
                'menu_title' => __('MEMBERSHIP', 'enginethemes') ,
                'cap' => 'administrator',
                'slug' => 'fre-membership',
                'icon' => 'U',
                'desc' => __("Bridging the gap between Employers and Freelancers", 'enginethemes')
            ) ,
            'container' => $orderlist
        );
        return $pages;
    }
}
add_filter('ae_admin_menu_pages', 'fre_membership_menu', 99);
/**
  * add default template to setting page
  * @param array $default
  * @return array $default
  * @since 1.0
  * @package FREELANCEENGINE
  * @category PRIVATE MESSAGE
  * @author Jack Bui
  */
function fre_membership_default_option( $default ){
    return $default;
}
add_filter( 'fre_default_setting_option', 'fre_membership_default_option' );

/**
  * set default email
  *
  * @param array $mail_template
  * @return void
  * @since 1.0
  * @package FREMEMBERSHIP
  * @category FREMEMBERSHIP
  * @author danng
  */
function fre_membership_default_email($mail_template){

    $mail_template['fre_membership_expiry_soon_email_template'] = get_df_expired_soon_email_template();

    $mail_template['fre_membership_auto_renew_success_email']   = get_df_auto_renew_success_email_template();
    $mail_template['fre_membership_auto_renew_fail_email']      = get_df_auto_renew_fail_email_template();
    //$mail_template['fre_membership_cancel_membership_email']    = get_df_cancel_membership();
    $mail_template['fre_cancel_membership_email']               = get_cancel_membership();
    $mail_template['fre_cancel_membership_admin_email']         = get_cancel_membership_admin();

    $mail_template['subscriber_successful_mail_template']       = get_df_subscriber_successful_mail_template();

    return $mail_template;
}
add_filter('fre_default_setting_option', 'fre_membership_default_email');