<?php

require('custom-fields-get.php');
require('verify_seller_function.php');


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





