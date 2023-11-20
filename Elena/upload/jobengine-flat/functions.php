<?php



require_once('customizer.php');

add_action('wp_print_styles', 'my_print_css',1000);

function my_print_css(){

	wp_deregister_style('customization');

	wp_deregister_style('screen');

	

	wp_register_style('customization', et_get_customize_css_path_child());

	wp_register_style('screen', get_stylesheet_directory_uri().'/css/screen.css');

}



add_action('after_setup_theme','child_theme_init');

function child_theme_init(){

	remove_action('init', 'et_customizer_init');

	add_action('init','je_customizer_init');

}


// for debug

add_filter('je_is_expand_parent_categories_list','je_is_expand_parent_categories_list');

function je_is_expand_parent_categories_list(){

	return false;

}

require('custom_upload_cv.php');

?>