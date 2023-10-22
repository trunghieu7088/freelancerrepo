<?php
function ETChatbox_generate_page_shortcode($shortcode, $post_title){
    $name       = $shortcode;
    $content    = '['.$shortcode.']';
    $page_set   = (int) get_option($name, false);
    if( $page_set > 0 ){
        $page = get_post($page_set);
        if($page){  return; }
    }
    $args = array(
        'posts_per_page'   => 1,
        'orderby'          => 'title',
        'order'            => 'DESC',
        'post_type'        => 'page',
        'post_status'      => 'publish',
        's'                => $content,
    );
    $page = new WP_Query( $args );
    if( ! $page->have_posts() ){
        $args = array(
            'post_title'    => $post_title,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_content'  => $content,
        );
        $page_id = wp_insert_post($args);
        if( ! is_wp_error($page_id) ){
            $page_template = get_post_meta($page_id, '_wp_page_template', true);
             if($page_template != 'page.php'){
                update_post_meta( $page_id, '_wp_page_template', 'page.php' );
            }
        }
        update_option($name, $page_id);
    }
}

function set_etchatbox_default_values()
{
	ETChatbox_generate_page_shortcode('et_chatbox_front_shortcode','ET Chat Box');
}




//add_filter( 'theme_page_templates', 'add_et_chatbox_page_template' ,20,3);
function add_et_chatbox_page_template($templates)
{
	 $templates['page-et-chat-box.php'] = __( 'ET Chat Box Page', 'text-domain' );	
	 $templates['page-realtime.php'] = __( 'Realtime Page', 'text-domain' );


   return $templates;
}


//add_filter( 'template_include', 'pt_change_page_template', 99 );
function pt_change_page_template($template)
{
   /* if (is_page()) {
        $meta = get_post_meta(get_the_ID());

        if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
            $template = $meta['_wp_page_template'][0];
        }
    }

    return $template; */

     global $post;
    $custom_template_slug   = 'page-et-chat-box.php';
    $page_template_slug     = get_page_template_slug( $post->ID );

    if( $page_template_slug == $custom_template_slug ){
        return plugin_dir_path( __FILE__ ) . $custom_template_slug;
    }

    return $template;

}

//add_filter( 'template_include', 'realtime_change_page_template', 99 );
function realtime_change_page_template($template)
{
   /* if (is_page()) {
        $meta = get_post_meta(get_the_ID());

        if (!empty($meta['_wp_page_template'][0]) && $meta['_wp_page_template'][0] != $template) {
            $template = $meta['_wp_page_template'][0];
        }
    }

    return $template; */

     global $post;
    $custom_template_slug   = 'page-realtime.php';
    $page_template_slug     = get_page_template_slug( $post->ID );

    if( $page_template_slug == $custom_template_slug ){
        return plugin_dir_path( __FILE__ ) . $custom_template_slug;
    }

    return $template;

}


//add_action('init','create_custom_page_template');
function create_custom_page_template()
{
	global $wp_filesystem;
    // Initialize the WP filesystem.
    if (empty($wp_filesystem)) {
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }       
    $save_file_to = get_stylesheet_directory() . '/page-full-width.php';
    $content='<?php
/**
 * Template Name:  RealTime Full Page
 */ ?>';
    $content.='<?php get_header(); ?>';

    $content.='<?php get_footer(); ?>';
$wp_filesystem->put_contents($save_file_to, $content, 0644);
}

