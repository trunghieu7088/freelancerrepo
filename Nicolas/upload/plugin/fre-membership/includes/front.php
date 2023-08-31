<?php

function a_paypal_subcriber_button($package){

	global $wpdb, $user_ID;

	$test_mode = 	(boolean) ae_get_option('membership_mode', true);

	$sandbox_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	$live_url = "https://www.paypal.com/cgi-bin/webscr";
	$paypal_url = $live_url;
	if($test_mode){
		$paypal_url = $sandbox_url;
	}

	$return_page_id = ae_get_option('membership_successful_return');
	$return_url = home_url();
	if($return_page_id){
		$return_url = get_permalink($return_page_id);
	}
	$number_months = (int)$package->et_subscription_time;
	if($number_months< 1 || $number_months > 12)
		$number_months = 1;
	$notify_url = home_url('?paypalListener=paypal_standard_IPN');
	$paypal_api = fre_get_paypal_membership_api();
	$paypal_business = $paypal_api->paypal_business; // lab@etlab.com

	$custom = $user_ID.'||'.$package->post_type;
	$price = get_subscribe_price($package->et_price);

	?>
	<form action="<?php echo $paypal_url;?>" method="post">

	    <!-- Identify your business so that you can collect the payments. -->
	    <input type="hidden" name="business" value="<?php echo trim($paypal_business);?>">

	    <!-- Specify a Subscribe button. -->
	    <input type="hidden" name="cmd" value="_xclick-subscriptions">

	    <!-- Identify the subscription. -->
	    <input type="hidden" name="item_name" value="<?php echo $package->post_title;?>">
	    <input type="hidden" name="item_number" value="<?php echo $package->sku;?>">
	    
	    <!-- Set the terms of the regular subscription. -->
	    <input type="hidden" name="currency_code" value="<?php echo fre_get_currency_code();?>">
	    <input type="hidden" name="a3" value="<?php echo $price;?>">
	    <input type="hidden" name="p3" value="<?php echo $number_months;?>">
	    <input type="hidden" name="t3" value="M">
	    <input type="hidden" name="custom" value="<?php echo $custom;?>">
	    <!--
	    	a3 - amount to billed each recurrence
			p3 - number of time periods between each recurrence
			t3 - time period (D=days, W=weeks, M=months, Y=years)
			!-->

	    <!-- Set recurring payments until canceled. -->
	    <input type="hidden" name="src" value="1">

	    <input type="hidden" name="notify_url" value="<?php echo $notify_url?>">
	    <input type="hidden" name="return" value="<?php echo $return_url?>">
	    <input type="hidden" name="cancel_return" value="<?php echo home_url();?>/cancel/">


	    <!-- Have PayPal generate usernames and passwords. -->
	    <!-- <input type="hidden" name="usr_manage" value="1">!-->

	    <!-- Display the payment button. -->
	    <!--
	    <input type="image" name="submit"
	    src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribe_LG.gif"
	    alt="Subscribe">
	    !-->
	    <button class="btn collapsed  select-payment" type="submit" title=""> <?php _e('SELECT','enginethemes');?>	</button>
	    <img alt="" width="1" height="1"
	    src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" >
	</form>
<?php }

function save_credit_history_subscriber($plan){

	global $user_ID;
	$price = get_subscribe_price($plan->et_price);
	$history_post = array(
        'post_type' => 'fre_credit_history',
        'post_status' => 'publish',
        'post_author' => $user_ID,
        // 'post_title' => sprintf(__('Subscribe to a plan %s','enginethemes'),$plan->post_title),
        'post_title' => 'Subscribe',
        'post_content' => 'charge ' . $price
    );

    $history_id = wp_insert_post($history_post);

    if( !is_wp_error($history_id) ){
		update_post_meta( $history_id, 'history_type', 'subscribe');
    	update_post_meta( $history_id, 'amount', $price );
    	update_post_meta( $history_id, 'currency', fre_get_currency_code());
    	update_post_meta( $history_id, 'history_status', 'completed');
    	update_post_meta( $history_id, 'subscribe_pack_sku', $plan->sku);
    	update_post_meta( $history_id, 'info_changelog', 'Subscriber plan'.$plan->post_title);
    }
}

function get_subscription_by_sub_api_id($api_subscr_id, $user_id = 0){

	global $wpdb;
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

	$sql = $wpdb->prepare("SELECT * FROM {$tbl_subscriptions} sub WHERE sub.api_subscr_id = %s ORDER BY sub.id DESC", $api_subscr_id);

	$record 	= $wpdb->get_row($sql, OBJECT);
	if( !isset($record->id) || $record->id == NULL ){
		return false;
	}

	return $record;


}
/**
 * check subscriber of user.
 * @return false or object.
 * @since new_version.
*/
function get_mebership_of_member($user_id = 0){

	global $wpdb, $user_ID;
	// $tbl_member = $wpdb->prefix . 'membership';
	// $tbl_order 	= $wpdb->prefix . 'membership_order';

	$tbl_member 		= $wpdb->prefix . 'fre_membership';
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';


	if( ! is_user_logged_in() && !$user_id ) return false;

	if( !$user_id ) $user_id = $user_ID;

	$pack_type 	= get_pack_type_of_user($user_id);
	$sql 	= "SELECT * FROM {$tbl_member} m LEFT JOIN  {$tbl_subscriptions} sub ON   m.subscr_id = sub.id WHERE m.user_id = $user_id   ORDER BY sub.id DESC";

	$record 	= $wpdb->get_row($sql, OBJECT);

	return $record;
}
/**
 * Kiểm tra xem người dùng hiện tại đã cố free và chưa hết hạn chưa.
 * Input: người dùng đã logged;
 **/

function is_availbale_free_subscribed($user_id, $log = false){
	global $wpdb;
	$tbl_member 		= $wpdb->prefix . 'fre_membership';
	$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';

	$pack_type 	= get_pack_type_of_user($user_id);

	$sql = $wpdb->prepare("SELECT * FROM {$tbl_member} m LEFT JOIN  {$tbl_subscriptions} sub ON  m.user_id = %d AND m.user_id = sub.user_id
		WHERE sub.price = %d   AND sub.expiry_time > %d AND sub.pack_type = %s GROUP BY m.id, sub.id
 		ORDER BY sub.id DESC", $user_id, 0, time(), $pack_type );

	$record 	= $wpdb->get_row($sql, OBJECT);

	if( !$record || $record->id == NULL ){
		return false;
	}
	return $record;
}

/**
 * Check to know current user are subsciber is available
 */
function is_subscriber_available($subscribed = NULL ){
	if( !$subscribed ){
		$subscribed = get_mebership_of_member();
	}

	if(!$subscribed) return false;
	if( ! isset($subscribed->expiry_time) ){ return false; }
	if( (int) $subscribed->expiry_time < time() ){ return false; }

	if( (int) $subscribed->remain_posts < 1) {  return false; }

	return $subscribed;
}


function fre_membersip_price_format( $subscriber, $style = '<sup>' ) {

	$amount = $subscriber->price;

	$currency 		= fre_get_currency(); // check via fre_multi currencies extension
	$currency_code 	= $subscriber->currency; // USD

	$align = $currency['align'];
	// dafault = 0 == right;
	$icon     = $currency_code;
	$price_format = get_theme_mod( 'decimal_point', 1 );

	$format       = '%1$s';

	switch ( $style ) {
		case 'sup':
			$format = '<sup>%s</sup>';
			break;

		case 'sub':
			$format = '<sub>%s</sub>';
			break;

		default:
			$format = '%s';
			break;
	}

	$number_format = ae_get_option( 'number_format' );
	$decimal       = ( isset( $number_format['et_decimal'] ) ) ? $number_format['et_decimal'] : get_theme_mod( 'et_decimal', 2 );
	$decimal_point = ( isset( $number_format['dec_point'] ) && $number_format['dec_point'] ) ? $number_format['dec_point'] : get_theme_mod( 'et_decimal_point', '.' );
	$thousand_sep  = ( isset( $number_format['thousand_sep'] ) && $number_format['thousand_sep'] ) ? $number_format['thousand_sep'] : get_theme_mod( 'et_thousand_sep', ',' );

	if ( $align != "0" ) {
		$format = $format . '%s';

		return sprintf( $format, $icon, number_format( (double) $amount, $decimal, $decimal_point, $thousand_sep ) );
	} else {
		$format = '%s' . $format;

		return sprintf( $format, number_format( (double) $amount, $decimal, $decimal_point, $thousand_sep ), $icon );
	}
}

function fre_get_free_post_left($user_id = 0){
	global $user_ID;
	if(!$user_id) $user_id = $user_ID;
	$user_meta = 'posted_on_'.date('m').date('Y');
	$posted_this_month = (int) get_user_meta($user_id, $user_meta, true);
	$total_free = get_free_post_of_emp();
	$post_left = $total_free - $posted_this_month;
	return max($post_left, 0);
}

// nếu đang post free => tăng số này lên 1 để lưu lại.
function fre_increase_number_free_posted($user_id = 0){

	if(!$user_id){ global $user_ID; $user_id = $user_ID;}
	$user_meta = 'posted_on_'.date('m').date('Y');
	$posted_count = (int) get_user_meta($user_id, $user_meta, true);
	$posted_count = $posted_count + 1;
	update_user_meta($user_id, $user_meta, $posted_count);
}

function get_free_post_of_emp(){
	return (int) ae_get_option('fre_number_free_post', 0);
}
function get_number_post_of_emp($subscriber = 0){


	if( ! is_user_logged_in() ){
		$free_post_left = get_free_post_of_emp();
		return $free_post_left;
	}

	if( ! $subscriber ){
		$subscriber = get_mebership_of_member();
	}
	$subcription_posts = 0;
	if($subscriber){
		if( $subscriber->expiry_time > time())
			$subcription_posts = (int) $subscriber->remain_posts;
	}

	$free_post_left = fre_get_free_post_left();

	return $free_post_left + $subcription_posts;

}