<?php

require('custom-fields-get.php');
require('verify_seller_function.php');
require('new_change.php');
require('custom_require_files.php');

add_action('wp_enqueue_scripts', 'override_customorderjs');

function override_customorderjs()

{    

    wp_deregister_script('custom-order');

    wp_enqueue_script('custom-order', get_stylesheet_directory_uri().'/assets/js/custom-order.js', array(

                'front',    

                 'jquery',

                'underscore',

                'backbone',

                'appengine',

                'front',

                'ae-message-js'

            ), ET_VERSION, true);





}


function custom_archive_title($title) {
    if (is_post_type_archive('mjob_post')) {
      $title = 'Angebote';
    }
    return $title;
  }
  add_filter('post_type_archive_title', 'custom_archive_title',9999,1);

