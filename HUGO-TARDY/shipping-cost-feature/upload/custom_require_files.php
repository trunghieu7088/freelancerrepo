<?php
add_action('wp_enqueue_scripts', 'add_custom_css_js_files',10);
function add_custom_css_js_files()
{             
    wp_dequeue_script('checkout-handle');
    wp_deregister_script( 'checkout-handle' );
    wp_enqueue_script('checkout-handle', get_stylesheet_directory_uri().'/assets/js/checkout-handle.js', array(
    'jquery',
    'underscore',
    'backbone',
    'appengine',
    'front'), ET_VERSION, true);
    wp_enqueue_style('all-custom-css', get_stylesheet_directory_uri().'/assets/css/all-custom-css.css');
}