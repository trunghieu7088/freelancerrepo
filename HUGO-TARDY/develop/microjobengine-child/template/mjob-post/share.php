<div class="sharing">
    <?php
    /**
     * Fire action before mjob detail dock bar
     *
     * @since 1.3.1
     * @author Tat Thien
     */
    do_action( 'mje_before_mjob_dock', $mjob_post );
    ?>
    <ul class="link-social list-share-social addthis_toolbox addthis_default_style">
        <?php
        /**
         * Fire action before social list
         *
         * @since 1.3.1
         * @author Tat Thien
         */
        do_action( 'mje_before_mjob_social_list', $mjob_post );
        ?>
        <!-- <li class="facebook"><a href="<?php echo $mjob_post->permalink; ?>" class="addthis_button_facebook face" title="<?php _e('Facebook', 'enginethemes'); ?>"><i class="fa fa-facebook"></i></a></li>
        <li class="twitter"><a href="<?php echo $mjob_post->permalink; ?>" class="addthis_button_twitter twitter" title="<?php _e('Twitter', 'enginethemes'); ?>"><i class="fa fa-twitter"></i></a></li>
        <li class="google"><a href="https://plus.google.com/share?url=<?php echo $mjob_post->permalink; ?>" class=" google" title="<?php _e('Google', 'enginethemes'); ?>" target="_blank" ><i class="fa fa-google-plus"></i></a></li> -->
        <!-- custom code 17th Feb 2024 -->
        <div class="custom-social-mjob-wrapper">
            <?php 
                $user_profile_id=get_user_meta($mjob_post->post_author,'user_profile_id',true);
                $custom_social_links=array('email','website','facebook','instagram','linkedin','myspace','pinterest','soundcloud','tumblr','twitter','youtube');                

            ?>
            <?php foreach($custom_social_links as $social_item) : ?>
                 <?php $social_link=get_post_meta($user_profile_id,'custom_'.$social_item,true); ?>
                 <?php if($social_link): ?>
                    <?php if( $social_item=='email'): ?>                   
                        <a class="custom-social-icon" href="mailto:<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php else: ?>
                        <a class="custom-social-icon" href="<?php echo $social_link; ?>" target="_blank"><img src="<?php echo get_stylesheet_directory_uri().'/assets/img/social_icons/'.$social_item.'.png'; ?>"></a>
                    <?php  endif; ?>
                 <?php  endif; ?>
            <?php endforeach; ?>
        </div>
        <!-- end custom code 17th Feb 2024 -->
        <?php
        /**
         * Fire action after social list
         *
         * @since 1.3.1
         * @author Tat Thien
         */
        do_action( 'mje_after_mjob_social_list', $mjob_post );
        ?>
    </ul>
    <?php
    /**
     * Fire action before mjob detail dock bar
     *
     * @since 1.3.1
     * @author Tat Thien
     */
    do_action( 'mje_after_mjob_dock', $mjob_post );
    ?>
</div>