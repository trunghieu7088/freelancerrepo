<?php
add_action('wp_head','display_custom_meta_description_tag',999);

function display_custom_meta_description_tag()
{
    global $post;

    //add meta description for tag and thread page
   
    if((is_tax('thread_category') || is_tax('fe_tag') ))
    {
        $custom_term_id=(int)get_queried_object_id();
        $term_item=get_term($custom_term_id,get_query_var('taxonomy'));      
        if( $term_item && !is_wp_error($term_item))
        {
            $term_description= $term_item->description;
            if(!$term_description)
            {
                $term_description='';
            }
            echo '<meta name="description" content="'. $term_description.'" />'; 
        }
              
    }

    //add meta description for single thread

    if(is_single() && 'thread'==get_post_type())
    {
        if(!empty($post))
        {
            $custom_description=wp_trim_words(wp_strip_all_tags($post->post_content),50,'...');
            if(!$custom_description)
            {
                $custom_description='';
            }
            echo '<meta name="description" content="'. $custom_description.'" />';  
        }       
    }

    //add meta description for normal page

    if(is_page())
    {
        if(!empty($post))
        {
            $page_description=wp_trim_words(wp_strip_all_tags($post->post_content),50,'...');
            if(!$page_description)
            {
                $page_description='';
            }
            echo '<meta name="description" content="'. $page_description.'" />';  
        }       
    }

    if(is_home() || is_front_page())
    {
        echo '<meta name="description" content="'. get_bloginfo('description').'" />'; 
    }

}