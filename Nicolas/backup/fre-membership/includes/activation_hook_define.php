<?php

function membership_generate_page_shortcode($shortcode, $post_title){
    $name       = $shortcode;
    $content    = '['.$shortcode.']';
    $page_set   = (int) ae_get_option($name, false);
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
             if($page_template != 'page-full-width.php'){
                update_post_meta( $page_id, '_wp_page_template', 'page-full-width.php' );
            }
        }
        ae_update_option($name, $page_id);
    }
}

function set_membership_default_values(){

	$expired_soon_email = ae_get_option('fre_membership_expiry_soon_email_template','');
	if( empty($expired_soon_email) || is_array($expired_soon_email) ){
		ae_update_option('fre_membership_expiry_soon_email_template', get_df_expired_soon_email_template());
	}
    $thankyou = ae_get_option('subscriber_successful_mail_template','');
    if( empty($thankyou) || is_array($thankyou) ){
        ae_update_option('subscriber_successful_mail_template', get_df_subscriber_successful_mail_template());
    }


    $renew_success = ae_get_option('fre_membership_auto_renew_success_email', '');
    if(empty($renew_success) || is_array($renew_success) ){
        ae_update_option('fre_membership_auto_renew_success_email', get_df_auto_renew_success_email_template() );
    }


    $renew_fail = ae_get_option('fre_membership_auto_renew_fail_email', '');
    if(empty($renew_fail)){
        ae_update_option('fre_membership_auto_renew_fail_email', get_df_auto_renew_fail_email_template() );
    }

    $number_days = ae_get_option('number_days_auto_check_membership', '');
    if(empty($number_days) ){
        ae_update_option('number_days_auto_check_membership', '7');
    }
    $cancel_email = ae_get_option('fre_cancel_membership_admin_email', '');
    if( empty($cancel_email) || is_array($cancel_email) ){
        ae_update_option('fre_cancel_membership_admin_email', get_cancel_membership_admin() );
    }
    $cancel_email = ae_get_option('fre_cancel_membership_email', '');
    if( empty($cancel_email) || is_array($cancel_email) ){
        ae_update_option('fre_cancel_membership_email', get_cancel_membership() );
    }

    membership_generate_page_shortcode('fre_membership_plans','Membership Plans');
    membership_generate_page_shortcode('fre_membership_checkout','Checkout');
    membership_generate_page_shortcode('membership_successful_return', 'Thank you');

}
?>