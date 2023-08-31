<?php

add_action('wp_enqueue_scripts', 'add_custom_js', 20120207);
function add_custom_js()
{
    wp_dequeue_script('custom-order');
    wp_enqueue_script('custom-order-offer', get_stylesheet_directory_uri().'/assets/js/custom-order.js', array('jquery'),20151110,true);
}
