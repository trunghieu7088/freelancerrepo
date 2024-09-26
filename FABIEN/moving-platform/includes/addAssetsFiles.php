<?php
add_action('wp_enqueue_scripts', 'addAssetsFiles',1);
function addAssetsFiles()
{    
    
    wp_enqueue_style( 'custom-moving-platform-bootstrap-grid', MOVING_PLATFORM_URL. 'assets/bootstrap4-6-2/css/bootstrap-grid.min.css', array(), MOVING_PLATFORM_VERSION ) ;    

    wp_enqueue_style( 'custom-moving-platform-style', MOVING_PLATFORM_URL. 'assets/css/moving_platform.css', array(), MOVING_PLATFORM_VERSION ) ;

    wp_enqueue_style( 'custom-moving-toastr', MOVING_PLATFORM_URL. 'assets/css/toastr.css', array(), MOVING_PLATFORM_VERSION ) ;

    wp_enqueue_style( 'custom-moving-jquery-ui-style', MOVING_PLATFORM_URL. 'assets/jquery-ui/jquery-ui.min.css', array(), MOVING_PLATFORM_VERSION ) ;
  
}

add_action('wp_enqueue_scripts', 'add_custom_js_handler',999);
function add_custom_js_handler()
{
    wp_enqueue_script('moving-platform-bootstrap-js', MOVING_PLATFORM_URL.'assets/bootstrap4-6-2/js/bootstrap.bundle.min.js', array(), MOVING_PLATFORM_VERSION);    

    wp_enqueue_script('moving-platform-handle-js', MOVING_PLATFORM_URL.'assets/js/moving_platform_frontend.js', array('jquery'), MOVING_PLATFORM_VERSION);    

    wp_enqueue_script('moving-platform-toastr-js', MOVING_PLATFORM_URL.'assets/js/toastr.js', array('jquery'), MOVING_PLATFORM_VERSION);    
    
    wp_enqueue_script('moving-platform-jquery-ui-js', MOVING_PLATFORM_URL.'assets/jquery-ui/jquery-ui.min.js', array(), MOVING_PLATFORM_VERSION);        

    wp_enqueue_script('moving-platform-jquery-validate-js', MOVING_PLATFORM_URL.'assets/jquery.validate.min.js', array(), MOVING_PLATFORM_VERSION);    

    if(is_page_template('moving_post_request_page.php'))
    {
        wp_enqueue_script('plupload-all');
    }
    
    //add stripe lib 
    if(is_page_template('all-requests.php'))
    {
        wp_enqueue_script('moving-platform-stripe-payment-js', MOVING_PLATFORM_URL.'assets/js/stripe_payment_handle.js', array('jquery'), MOVING_PLATFORM_VERSION);    
    }
    
     //add stripe lib 
     if(is_page_template('checkout-requests.php'))
     {
         wp_enqueue_script('moving-platform-multiple-payment-js', MOVING_PLATFORM_URL.'assets/js/multiple_payment_handle.js', array('jquery'), MOVING_PLATFORM_VERSION);    
     }

    
}

add_action('wp_enqueue_scripts', 'importTomSelect',999);
function importTomSelect()
{

    wp_enqueue_script('tom-select-js', MOVING_PLATFORM_URL.'/assets/tom-select/tom-select.js', array(
        'jquery',          
    ), '1.0', true); 

    wp_enqueue_style('tom-select-css', MOVING_PLATFORM_URL.'/assets/tom-select/tom-select.css');
  
}

//add fancybox
add_action('wp_enqueue_scripts','add_fancybox_css',1);

function add_fancybox_css()
{
    wp_enqueue_style('css-fancybox', MOVING_PLATFORM_URL.'/assets/fancybox/fancybox.css');
}

add_action('wp_enqueue_scripts','add_fancybox_js',999);

function add_fancybox_js()
{
    wp_enqueue_script('js-fancybox', MOVING_PLATFORM_URL.'/assets/fancybox/fancybox.js', array(
        'jquery'
    ), MOVING_PLATFORM_VERSION, true); 
}

//add js to admin
add_action('admin_enqueue_scripts', 'custom_admin_enqueue_scripts',99);

function custom_admin_enqueue_scripts()
{
    if(is_admin())
    {
        wp_enqueue_script('custom-bulk-import-admin-js', MOVING_PLATFORM_URL.'/assets/js/custom-admin.js', array(
            'jquery'
        ), MOVING_PLATFORM_VERSION, true); 

        wp_enqueue_script('tom-select-js', MOVING_PLATFORM_URL.'/assets/tom-select/tom-select.js', array(
            'jquery',          
        ), '1.0', true); 

        wp_enqueue_style('tom-select-css', MOVING_PLATFORM_URL.'/assets/tom-select/tom-select.css');

    }
    
}