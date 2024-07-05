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