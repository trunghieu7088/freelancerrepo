<?php
function my_theme_enqueue_styles() {

    $parent_style = 'jobcareertheme';

    wp_register_style('custom-new-style',get_stylesheet_directory_uri().'/assets/customcss/customstyle.css');
    wp_register_style( 'custom-new-common',get_stylesheet_directory_uri().'/assets/customcss/custom-common.css');
    wp_register_style( 'swiper-css','https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css');

    wp_enqueue_style( 'custom-new-style');
    wp_enqueue_style( 'custom-new-common');
    wp_enqueue_style( 'swiper-css');

}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

add_action('wp_enqueue_scripts', 'add_custom_js_order_message');
function add_custom_js_order_message()
{

    wp_enqueue_script('custom-home-new-js', get_stylesheet_directory_uri().'/assets/customjs/homecustom.js', array(
        'front'
    ), ET_VERSION, true);
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js', array(
        'front'
    ), ET_VERSION, true);

}

