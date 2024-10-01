<?php
if (!function_exists('ae_render_connect_social_button')) {
    function ae_render_connect_social_button($icon_classes = array(), $button_classes = array(), $before_text = '', $after_text = '')
    {
        // Get social id
        global $current_user;
        $facebook_social_id = get_user_meta($current_user->ID, 'et_facebook_id', true);
        $google_social_id = get_user_meta($current_user->ID, 'et_google_id', true);

        /* check enable option*/
        $use_facebook = ae_get_option('facebook_login');
        $gplus_login = ae_get_option('gplus_login');
        if ($icon_classes == '') {
            $icon_classes = 'fa fa-square-facebook';
        }
        $defaults_icon = array(
            'fb' => 'fa-brands fa-square-facebook',
            'gplus' => 'fa-brands fa-google',
        );
        $icon_classes = wp_parse_args($icon_classes, $defaults_icon);
        $icon_classes = apply_filters('ae_social_icon_classes', $icon_classes);
        $defaults_btn = array(
            'fb' => '',
            'gplus' => '',
            'tw' => '',
            'lkin' => ''
        );
        $button_classes = wp_parse_args($button_classes, $defaults_btn);
        $button_classes = apply_filters('ae_social_button_classes', $button_classes);
        if (($use_facebook && (!empty($facebook_social_id))) || ($gplus_login && !empty($google_social_id))) {
            if ($before_text != '') { ?>
                <div class="socials-head"><?php echo $before_text ?></div>
            <?php } ?>
            <ul class="list-social-connect">
                <?php if ($use_facebook && !empty($facebook_social_id)) { ?>
                    <li>
                        <a href="javascript:void(0)" class="fb facebook_disconnect <?php echo $button_classes['fb']; ?>">
                            <i class="<?php echo $icon_classes['fb']; ?>"></i>
                            <span class="social-text"><?php _e("Connected to Facebook", 'enginethemes'); ?></span>
                        </a>
                    </li>
                <?php } ?>
                <?php if ($gplus_login && !empty($google_social_id)) { ?>
                    <li>
                        <a href="javascript:void(0)" class="gplus gplus_disconnect <?php echo $button_classes['gplus']; ?>">
                            <i class="<?php echo $icon_classes['gplus']; ?>"></i>
                            <span class="social-text"><?php _e("Connected to Google Account", 'enginethemes'); ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <?php
            if ($after_text != '') { ?>
                <div class="socials-footer"><?php echo $after_text ?></div>
<?php
            }
        }
    }
}
