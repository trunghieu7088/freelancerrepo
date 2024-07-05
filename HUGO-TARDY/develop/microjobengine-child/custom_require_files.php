<?php
add_action('wp_enqueue_scripts', 'add_custom_css_js_files',10);
function add_custom_css_js_files()
{             
    if (is_page_template('page-order.php')) {
        wp_dequeue_script('checkout-handle');
        wp_deregister_script( 'checkout-handle' );
        wp_enqueue_script('checkout-handle', get_stylesheet_directory_uri().'/assets/js/checkout-handle.js', array(
        'jquery',
        'underscore',
        'backbone',
        'appengine',
        'front'), ET_VERSION, true);
    }
    
    wp_enqueue_script('custom-shippingcost-js', get_stylesheet_directory_uri().'/assets/js/custom-js-shippingcost.js', array(
        'front'
    ), ET_VERSION, true);

    wp_enqueue_script('custom-portfolio-js', get_stylesheet_directory_uri().'/assets/js/custom-portfolio-feature.js', array(
        'front'
    ), ET_VERSION, true);

    wp_enqueue_style('all-custom-css', get_stylesheet_directory_uri().'/assets/css/all-custom-css.css');
    wp_enqueue_style('custom-recruit-css', get_stylesheet_directory_uri().'/assets/css/custom-recruit-feature.css');
}

add_action('wp_enqueue_scripts','override_appenginejs',1);
function override_appenginejs()
{
   // wp_deregister_script('appengine');
   // wp_dequeue_script('appengine');
   
   wp_dequeue_script('front'); 
        wp_enqueue_script('appengine2', get_stylesheet_directory_uri() . '/assets/js/appengine.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front',
            'mjob-auth',
            'ae-message-js'), ET_VERSION, true); 

         
        wp_enqueue_script('front', get_stylesheet_directory_uri() . '/assets/js/front.js', array(
                'jquery',
                'underscore',
                'backbone',
                'appengine',
            ), ET_VERSION, true);
            
          
}

//add lightbox assets

add_action('wp_enqueue_scripts','add_css_lightbox2',1);

function add_css_lightbox2()
{
    wp_enqueue_style('css-lightbox2', get_stylesheet_directory_uri().'/assets/lightbox2/css/lightbox.css');
}

add_action('wp_enqueue_scripts','add_js_lightbox2',999);

function add_js_lightbox2()
{
    wp_enqueue_script('lightbox2-js', get_stylesheet_directory_uri().'/assets/lightbox2/js/lightbox.js', array(
        'jquery'
    ), ET_VERSION, true); 
}
