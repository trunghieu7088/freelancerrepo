<?php
function add_custom_suggested_message_manager()
{
	     $args = array(
	     	  'public' => true,
	     	  'show_ui' =>true,
	     	  'show_in_menu'=>true,
            'labels' => array(
                'name' => __("Smessage", 'enginethemes'),
                'singular_name' => __('Smessages', 'enginethemes'),
                'add_new' => __('Add New', 'enginethemes'),
                'add_new_item' => __('Add New Smessage', 'enginethemes'),
                'edit_item' => __('Edit Smessage', 'enginethemes'),
                'new_item' => __('New Smessage', 'enginethemes'),
                'all_items' => __('All Smessages', 'enginethemes'),
                'view_item' => __('View Smessages', 'enginethemes'),
                'search_items' => __('Search Smessages', 'enginethemes'),
                'not_found' => __('No Microjobs found', 'enginethemes'),
                'not_found_in_trash' => __('No Microjobs found in Trash', 'enginethemes'),
                'parent_item_colon' => '',
                'menu_name' => __('Smessages', 'enginethemes')
            ),

            'menu_icon' => 'dashicons-format-chat'
        );
        register_post_type('smessage',$args);
}
add_action('init','add_custom_suggested_message_manager');
