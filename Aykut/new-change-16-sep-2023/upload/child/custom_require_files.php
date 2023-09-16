<?php
require('custom_message_icon.php');
add_action('wp_enqueue_scripts', 'add_custom_overrideCSS');
function add_custom_overrideCSS()
{                 
     wp_enqueue_style('new-custom-css', get_stylesheet_directory_uri().'/assets/css/new-custom-css.css');
}
