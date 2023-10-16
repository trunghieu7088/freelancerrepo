<?php
add_action('wp_enqueue_scripts', 'add_custom_css');
function add_custom_css()
{                 
     wp_enqueue_style('all-custom-css', get_stylesheet_directory_uri().'/assets/css/custom-css.css');
}