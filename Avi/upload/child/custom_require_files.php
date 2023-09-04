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