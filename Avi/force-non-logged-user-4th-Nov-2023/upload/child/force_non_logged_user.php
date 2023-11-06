<?php
function add_non_logged_notify_page()
{
    $args_notifyPage = array(
        'post_status' =>'publish',
        'post_type' => 'page', // Specify the post type as 'page'
        'posts_per_page' => -1, // Retrieve all pages (you can adjust this as needed)
        'meta_query' => array(
            array(
                'key' => '_wp_page_template', // The key for the template
                'value' => 'page-non-logged-notify.php', // Replace with the template file name
                'compare' => '=', // Use '=' to match exactly
            ),
        ),
    );
    $notifyPage_check_exist=get_posts($args_notifyPage);
    if(empty($notifyPage_check_exist))
    {
        $notifyPageGuid= site_url() . "/notify";
        $notify_page = array( 
                         'post_title'     => 'notify',
                         'post_type'      => 'page',
                         'post_name'      => 'notify',                         
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $notifyPageGuid );

      $notify_page_id=wp_insert_post( $notify_page, FALSE ); 
      update_post_meta($notify_page_id,'_wp_page_template','page-non-logged-notify.php');
    }
}
add_action('wp_loaded','add_non_logged_notify_page');

function force_custom_redirects() {
    if ( !is_user_logged_in() ) 
    {      
        if (!is_home() && !is_page('notify') && !is_front_page() && !is_page('tos')
            && !is_page('how-it-works') && !is_page('trust-and-quality') && !is_page('frequently-asked-questions')
            && !is_page('ip-claim') && !is_page('payment-and-withdrawal') && !is_page('privacy-policy')
            && !is_page('tos-2') && !is_page('about-us') && !is_page('guidelines-for-experts') && !is_page('guidelines-for-clients')
            && !is_page('influencer') && !is_page('pioneer')) 
        {
            wp_redirect( site_url('/notify/'));
            die();
        }
    }   
    

}
add_action( 'template_redirect', 'force_custom_redirects' );