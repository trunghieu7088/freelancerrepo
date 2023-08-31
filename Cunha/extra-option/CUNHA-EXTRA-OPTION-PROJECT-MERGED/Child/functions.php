<?php
add_action('wp_enqueue_scripts', 'override_profilejs');
function override_profilejs()
{    
    wp_deregister_script('profile');
    wp_enqueue_script('profile', get_stylesheet_directory_uri().'/assets/js/profile.js', array(
                'front',
                'jquery',
				'underscore',
				'backbone',
				'appengine',	
            ), ET_VERSION, true);
}
require('pending_profile_functions.php');
require('discount_functions.php');
require('verify_functions.php');
require('extra_option_project.php');