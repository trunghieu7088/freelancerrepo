<?php
require('addAssetsFiles.php');
require('add-required-page.php');
require('class-moving-platform-main.php');
require('class-bulk-import.php');
require('class-payment-handler.php');
require('class-admin-actions.php');
require('class-email-feature.php');

//load carbon library for admin options
add_action( 'after_setup_theme', 'custom_video_call_crb_load',900,0 );
function custom_video_call_crb_load() {    
    if ( ! function_exists( 'carbon_get_post_meta' ) ) {
    require_once MOVING_PLATFORM_PATH . '/includes/carbon/vendor/autoload.php';
    \Carbon_Fields\Carbon_Fields::boot();
    }
}

add_action('init','import_phpexcel_lib',999);
function import_phpexcel_lib()
{
    if ( is_admin() && current_user_can('administrator') ) {
        require ('phpexcel/vendor/autoload.php');
    }    
}


add_action( 'after_setup_theme', 'custom_load_Stripe_sdk',999,0 );
function custom_load_Stripe_sdk() 
{    
    require_once MOVING_PLATFORM_PATH . '/includes/stripe/vendor/autoload.php';    
}


add_filter('pre_get_document_title', 'function_change_document_title',999);

function function_change_document_title($title) {
    $admin_data=AdminData::get_instance();

    if(is_page_template('moving_post_request_page.php'))
    {
        $title = $admin_data->getValue('post_request_page_title');
    }       
    
    if(is_page_template('all-requests.php'))
    {
        $title = $admin_data->getValue('all_request_page_title');
    }   
    
    if(is_page_template('checkout-requests.php'))
    {
        $title = $admin_data->getValue('checkout_page_title');
    }  
    
    return $title;
}

