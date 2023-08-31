<?php

require_once FRE_MEMBERSHIP_PATH . '/admin/membership_user.php';
require_once FRE_MEMBERSHIP_PATH . '/admin/membership-list.php';
function fre_membership_get_list_page($shortcode = ''){

    // update option page deposit credit from type slug to ID
    $page = ae_get_option($shortcode, false);

    if(!is_numeric($page)){
        $page = get_page_by_title( $page );
        if(!empty($page)){
            ae_update_option($shortcode, $page->ID);
        }
    }
    $args = array(
        'posts_per_page'   => -1,
        'offset'           => 0,
        'orderby'          => 'title',
        'order'            => 'DESC',
        'post_type'        => 'page',
        'post_status'      => 'publish',
    );
    if( !empty($shortcode) ){
        $args['s'] = $shortcode;
    }
    $posts_array = new WP_Query( $args );

    $posts_array = $posts_array->posts;
    $array = array();
    $array[0] = __('Select page', 'enginethemes');
    foreach ($posts_array as $key => $value) {
        $title = $value->post_title;
        $array[$value->ID] = $title;
        $page_template = get_post_meta($value->ID, '_wp_page_template', true);
        if($page_template != 'page-full-width.php'){
            // update_post_meta( $value->ID, '_wp_page_template', 'page-full-width.php' );
        }
    }
    wp_reset_query();
    return $array;
}