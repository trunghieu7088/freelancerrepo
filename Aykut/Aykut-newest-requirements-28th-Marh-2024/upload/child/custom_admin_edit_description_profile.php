<?php
// Add meta box to the post editor page
function add_custom_description_meta_box() {
    add_meta_box(
        'custom_profile_description_meta_box', // Unique ID
        'Profile Description', // Box title
        'display_custom_post_meta_box', // Content callback function
        'mjob_profile', // Post type
        'normal', // Context
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'add_custom_description_meta_box');

// Display the meta box content
function display_custom_post_meta_box($post) {
    // Retrieve the current value of the custom field
    $custom_profile_description = get_post_meta($post->ID, 'profile_description', true);
    if(!$custom_profile_description)
    {
        $custom_profile_description='';
    }   
    wp_editor($custom_profile_description, 'custom_profile_description', array(
        'media_buttons' => false,
        'textarea_rows' => 15,          
        'tinymce' => array(
            'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,undo,redo',
            'toolbar2'      => '',
            'toolbar3'      => '',
        ),
    ));

    ?>

    <?php
}

// Save the meta box data
function save_custom_description_mjob_profile($post_id,$post) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if the user has permission to edit the post
    if (!current_user_can('manage_options', $post_id)) {
        return;
    }

    // Save custom field data
    if (isset($_POST['custom_profile_description'])) {
        update_post_meta($post_id, 'profile_description', sanitize_text_field($_POST['custom_profile_description']));
    }
}
add_action('save_post_mjob_profile', 'save_custom_description_mjob_profile',999,2);
