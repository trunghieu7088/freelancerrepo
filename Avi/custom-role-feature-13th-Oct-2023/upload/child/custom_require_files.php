<?php
add_action('wp_enqueue_scripts', 'add_custom_js_slider');
function add_custom_js_slider()
{            

     wp_enqueue_script('swiper-slider-js', get_stylesheet_directory_uri().'/assets/js/swiper.js', array(
                'front'
            ), ET_VERSION, true);

      wp_enqueue_script('custom-swiper-slider-js', get_stylesheet_directory_uri().'/assets/js/custom-swiper.js', array(
                'front'
            ), ET_VERSION, true);
     
     wp_enqueue_style('swiper-css', get_stylesheet_directory_uri().'/assets/css/swiper.css');
     wp_enqueue_style('all-custom-css', get_stylesheet_directory_uri().'/assets/css/all-custom-css.css');
}

add_action('wp_enqueue_scripts', 'add_customOrder_js');
function add_customOrder_js()
{
    wp_dequeue_script('custom-order');
    wp_enqueue_script('custom-order-offer', get_stylesheet_directory_uri().'/assets/js/custom-order.js', array(
        'jquery',
        'underscore',
        'backbone',
        'appengine',
        'front',
        'ae-message-js'
    ), ET_VERSION, true);
}

add_action('wp_enqueue_scripts', 'override_custom_payment_method_js');
function override_custom_payment_method_js()
{
    wp_dequeue_script('payment-method');
    wp_enqueue_script('payment-method-custom', get_stylesheet_directory_uri().'/assets/js/payment-method.js', array(
        'jquery',
        'underscore',
        'backbone',
        'appengine',
        'front'
    ), ET_VERSION, true);
}

//custom code 6th October 2023
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

//end

//custom code custom role feature 13th Oct 2023
add_action('wp_enqueue_scripts','override_mjobAuthJS');
function override_mjobAuthJS()
{
    wp_dequeue_script('mjob-auth'); 
    wp_enqueue_script('mjob-auth', get_stylesheet_directory_uri() . '/assets/js/mjob-auth.js', array(
        'jquery',
        'underscore',
        'backbone',
        'appengine',
        'front'
    ), ET_VERSION, true);
}


//end