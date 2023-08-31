<?php



add_action('wp_enqueue_scripts', 'override_postservicejs');

function override_postservicejs()

{    

    wp_deregister_script('post-service');

    wp_enqueue_script('post-service', get_stylesheet_directory_uri().'/assets/js/post-service.js', array(

                'jquery','underscore','backbone','appengine'

            ), ET_VERSION, true);





}



//add_action('init','override_postservicejs');



function get_all_degress_for_posting_mjob()

{

	$terms = get_terms( array(

    'taxonomy' => 'degree',

    'hide_empty' => false,

	) );

	return $terms;

}



function get_all_kindwork_for_customOrderForm()

{

    $terms = get_terms( array(

    'taxonomy' => 'kindwork',

    'hide_empty' => false,

    ) );

    return $terms;

}