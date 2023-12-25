<?php

add_filter( 'template_include', 'register_subscription_page_template', 99 );

function register_subscription_page_template($template)
{
     global $post;
     if(isset($post) && !empty($post))
     {
        $custom_template_slug   = 'custom-subscription-template.php';
        $page_template_slug     = get_page_template_slug( $post->ID );

        if( $page_template_slug == $custom_template_slug ){
        return CUSTOM_SUBSCRIPTION_MJE_PATH .'/'.$custom_template_slug;
        }
    }
    return $template;
}


add_filter( 'template_include', 'register_manage_subscription_page_template', 99 );

function register_manage_subscription_page_template($template)
{
     global $post;
     if(isset($post) && !empty($post))
     {
        $custom_template_slug   = 'custom-manage-subscription.php';
        $page_template_slug     = get_page_template_slug( $post->ID );

        if( $page_template_slug == $custom_template_slug ){
        return CUSTOM_SUBSCRIPTION_MJE_PATH .'/'.$custom_template_slug;
        }
    }
    return $template;
    
}

add_filter( 'template_include', 'register_detail_subscription_page_template', 99 );

function register_detail_subscription_page_template($template)
{
     global $post;
     if(isset($post) && !empty($post))
     {
        $custom_template_slug   = 'custom-detail-subscription-plan.php';
        $page_template_slug     = get_page_template_slug( $post->ID );

        if( $page_template_slug == $custom_template_slug ){
        return CUSTOM_SUBSCRIPTION_MJE_PATH .'/'.$custom_template_slug;
        }
    }
    return $template;
}

add_filter( 'template_include', 'register_my_subscription_page_template', 99 );

function register_my_subscription_page_template($template)
{
     global $post;
     if(isset($post) && !empty($post))
     {
        $custom_template_slug   = 'my-subscription-template.php';
        $page_template_slug     = get_page_template_slug( $post->ID );

        if( $page_template_slug == $custom_template_slug ){
        return CUSTOM_SUBSCRIPTION_MJE_PATH .'/'.$custom_template_slug;
        }
    }
    return $template;
}


add_action('wp_loaded','addRequiredPage');

function addRequiredPage()
{
    $args_subscriptionPage = array(
        'post_status' =>'publish',
        'post_type' => 'page', // Specify the post type as 'page'
        'posts_per_page' => -1, // Retrieve all pages (you can adjust this as needed)
        'meta_query' => array(
            array(
                'key' => '_wp_page_template', // The key for the template
                'value' => 'custom-subscription-template.php', // Replace with the template file name
                'compare' => '=', // Use '=' to match exactly
            ),
        ),
    );
    $subscriptionPage_check_exist=get_posts($args_subscriptionPage);
    if(empty($subscriptionPage_check_exist))
    {
        $SubscriptionPageGuid = site_url() . "/subscription";
        $Subscription_page = array( 
            'post_title'     => 'subscription',
            'post_type'      => 'page',
            'post_name'      => 'subscription',                         
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_author'    => 1,
            'menu_order'     => 0,
            'guid'           => $SubscriptionPageGuid );

        $subscription_page_id=wp_insert_post( $Subscription_page, FALSE ); 
        update_post_meta($subscription_page_id,'_wp_page_template','custom-subscription-template.php');
    }

    $args_managesubscriptionPage = array(
        'post_status' =>'publish',
        'post_type' => 'page', // Specify the post type as 'page'
        'posts_per_page' => -1, // Retrieve all pages (you can adjust this as needed)
        'meta_query' => array(
            array(
                'key' => '_wp_page_template', // The key for the template
                'value' => 'custom-manage-subscription.php', // Replace with the template file name
                'compare' => '=', // Use '=' to match exactly
            ),
        ),
    );

    $managesubscriptionPage_check_exist=get_posts($args_managesubscriptionPage);
    if(empty($managesubscriptionPage_check_exist))
    {
        $manageSubscriptionPageGuid = site_url() . "/manage-subscription";
        $manageSubscription_page = array( 
            'post_title'     => 'manage-subscription',
            'post_type'      => 'page',
            'post_name'      => 'manage-subscription',                         
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_author'    => 1,
            'menu_order'     => 0,
            'guid'           => $manageSubscriptionPageGuid );

        $managesubscription_page_id=wp_insert_post( $manageSubscription_page, FALSE ); 
        update_post_meta($managesubscription_page_id,'_wp_page_template','custom-manage-subscription.php');
    }

   $args_detail_subscription = array(
        'post_status' =>'publish',
        'post_type' => 'page', // Specify the post type as 'page'
        'posts_per_page' => -1, // Retrieve all pages (you can adjust this as needed)
        'meta_query' => array(
            array(
                'key' => '_wp_page_template', // The key for the template
                'value' => 'custom-detail-subscription-plan.php', // Replace with the template file name
                'compare' => '=', // Use '=' to match exactly
            ),
        ),
    );

    $args_detail_subscription_check_exist=get_posts($args_detail_subscription);
    if(empty($args_detail_subscription_check_exist))
    {
        $args_detail_subscription_check_exist_PageGuid = site_url() . "/subscribe";
        $args_detail_subscription_check_exist_page = array( 
            'post_title'     => 'subscribe',
            'post_type'      => 'page',
            'post_name'      => 'subscribe',                         
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_author'    => 1,
            'menu_order'     => 0,
            'guid'           => $args_detail_subscription_check_exist_PageGuid );

        $args_detail_subscription_page_id=wp_insert_post( $args_detail_subscription_check_exist_page, FALSE ); 
        update_post_meta($args_detail_subscription_page_id,'_wp_page_template','custom-detail-subscription-plan.php');
    }
    
    $args_my_subscription = array(
        'post_status' =>'publish',
        'post_type' => 'page', // Specify the post type as 'page'
        'posts_per_page' => -1, // Retrieve all pages (you can adjust this as needed)
        'meta_query' => array(
            array(
                'key' => '_wp_page_template', // The key for the template
                'value' => 'my-subscription-template.php', // Replace with the template file name
                'compare' => '=', // Use '=' to match exactly
            ),
        ),
    );

    $args_my_subscription_check_exist=get_posts($args_my_subscription);

    if(empty($args_my_subscription_check_exist))
    {
        $args_my_subscription_check_exist_PageGuid = site_url() . "/mysubscription";
        $args_my_subscription_check_exist_page = array( 
            'post_title'     => 'mysubscription',
            'post_type'      => 'page',
            'post_name'      => 'mysubscription',                         
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_author'    => 1,
            'menu_order'     => 0,
            'guid'           => $args_my_subscription_check_exist_PageGuid );

        $args_my_subscription_page_id=wp_insert_post( $args_my_subscription_check_exist_page, FALSE ); 
        update_post_meta($args_my_subscription_page_id,'_wp_page_template','my-subscription-template.php');
    }
}
