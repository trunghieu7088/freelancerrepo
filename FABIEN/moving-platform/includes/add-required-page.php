<?php

// ADD post service page TEMPLATE
add_filter( 'template_include', 'register_custom_page_template', 99 );
function register_custom_page_template($template)
{
     global $post;
     if(isset($post) && !empty($post))
     {        
        
        $custom_templates = [
            'moving_post_request_page.php',
            'all-requests.php',
            'checkout-requests.php'            
        ];

        $page_template_slug     = get_page_template_slug( $post->ID );
      
        if (in_array($page_template_slug, $custom_templates)) {
            return MOVING_PLATFORM_PATH . '/pages/' . $page_template_slug;
        }
    }
    return $template;
}

//function to create page automatically
function custom_check_and_create_page($page_template,$page_slug)
{
    $args_page = array(
        'post_status' =>'publish',
        'post_type' => 'page', // Specify the post type as 'page'
        'posts_per_page' => -1, // Retrieve all pages (you can adjust this as needed)
        'meta_query' => array(
            array(
                'key' => '_wp_page_template', // The key for the template
                'value' => $page_template, // Replace with the template file name
                'compare' => '=', // Use '=' to match exactly
            ),
        ),
    );

    $args_page_check=get_posts($args_page);

    if(empty($args_page_check))
    {
        $args_page_Guid = site_url() . "/".$page_slug;
        $custom_created_page = array( 
            'post_title'     => $page_slug,
            'post_type'      => 'page',
            'post_name'      => $page_slug,                         
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_author'    => 1,
            'menu_order'     => 0,
            'guid'           => $args_page_Guid );

        $created_page_id=wp_insert_post( $custom_created_page, FALSE ); 
        update_post_meta($created_page_id,'_wp_page_template',$page_template);
    }    
}


add_action('wp_loaded','addRequiredPage');

function addRequiredPage()
{
    //post request page
    custom_check_and_create_page('moving_post_request_page.php','moving-post-request');

    //all requests page
    custom_check_and_create_page('all-requests.php','all-requests');

    //checkout requests
    custom_check_and_create_page('checkout-requests.php','checkout-requests');

}
