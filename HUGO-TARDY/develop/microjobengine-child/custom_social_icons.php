<?php
function display_custom_social_icons($user_profile_id)
{
    $custom_social_links=array('email','website','facebook','instagram','linkedin','myspace','pinterest','soundcloud','tumblr','twitter','youtube');               
    ?>
    <div class="custom_social_icon_wrapper">
    <?php foreach($custom_social_links as $social_item) : ?>
                 <?php $social_link=get_post_meta($user_profile_id,'custom_'.$social_item,true); ?>
                 <?php if($social_link): ?>
                    <?php if($social_item=='email'): ?>
                        <a href="mailto:<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php else: ?>
                        <a href="<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php  endif; ?>
                 <?php  endif; ?>
            <?php endforeach; ?>
    </div>
    <?php
}

function display_custom_social_icons_block($user_profile_id)
{
    $custom_social_links=array('email','website','facebook','instagram','linkedin','myspace','pinterest','soundcloud','tumblr','twitter','youtube');               
    ?>    
    <?php foreach($custom_social_links as $social_item) : ?>
                 <?php $social_link=get_post_meta($user_profile_id,'custom_'.$social_item,true); ?>
                 <?php if($social_link): ?>
                    <?php if($social_item=='email'): ?>
                        <a href="mailto:<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php else: ?>
                        <a href="<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php  endif; ?>
                 <?php  endif; ?>
            <?php endforeach; ?>    
    <?php
}

function display_social_icons_mobile_block($user_profile_id)
{
    $custom_social_links=array('email','website','facebook','instagram','linkedin','myspace','pinterest','soundcloud','tumblr','twitter','youtube');               
    ?>
    <div class="custom_social_buttons_mobile_block">
    <?php foreach($custom_social_links as $social_item) : ?>
                 <?php $social_link=get_post_meta($user_profile_id,'custom_'.$social_item,true); ?>
                 <?php if($social_link): ?>
                    <?php if($social_item=='email'): ?>
                        <a href="mailto:<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php else: ?>
                        <a href="<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php  endif; ?>
                 <?php  endif; ?>
            <?php endforeach; ?>    
    </div>
    <?php    
}

//add ajax to handling delete social links

add_action('wp_ajax_delete_social_link_profile','delete_social_link_profile_action');

function delete_social_link_profile_action()
{
    if (!is_user_logged_in()) {
        die();
    }

    extract($_POST);

    $current_profile=get_user_meta(get_current_user_id(),'user_profile_id',true);
    update_post_meta($current_profile,$social_type,'');
    $data['success']='true';
    $data['message']='Removed successfully';
    wp_send_json($data);
    die();
}