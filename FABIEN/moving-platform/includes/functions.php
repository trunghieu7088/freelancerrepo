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