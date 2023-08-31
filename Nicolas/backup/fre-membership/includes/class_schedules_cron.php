<?php

class Fre_Membership_Schedules{
	function __construct(){
		add_filter('cron_schedules', array($this, 'add_schedules_event') , 99);
		add_action('excute_membership_event', array($this,'auto_checking_subscription') );
		//add_action('excute_membership_event', array($this,'check_log_only'), 99 );
		// add_action('wp_footer',array($this,'debug_cron_job') );
	}

	function add_schedules_event( $schedules ) {

		$number_days = 7;
		$time_check = 3600*24*$number_days;
		// release mode time: 1/2 day.
		// develop time: per 1 hours.
		$schedules['every_six_hours'] = array(
			'interval' =>  2*60*60, // 60 == minly. 3600 == hourly.43200 = 1/2 day. 28800=  8 hours. 86400 == daily. 604800 == weekly,
			'display' => __('Check and send expiring soon email for Membership.')
		);

		return $schedules;

	}

	/**
	  Auto run each 1/2 day.
	  * time set in the filter of mebership_schedule_event value
	**/
	function auto_checking_subscription() {

		$now 	= time();
		$next_time_run_cron	= (int) get_option('next_time_checking_cron', false);
		if( $now > $next_time_run_cron ){
			$this->fre_membership_excute_email_expired_soon();
			$this->fre_membership_excute_auto_deduct();

			// important;
			$next_check = time() + 60*60; // 1 h = 60*60; check 1 lần.
			update_option('next_time_checking_cron', $next_check);

		}

	}

	/*
		check and send email to membership that help them know they will expired in next xx days.
	*/
	function fre_membership_excute_email_expired_soon(){
		// check and wend email to membersip who nearly expired in xxx days.
		global $wpdb;
		$membership = $wpdb->prefix . 'membership';
		$subject 	= __('Your subscription will expire soon.','fre_membership');
		$message 	=  ae_get_option('fre_membership_expiry_soon_email_template');
		$mailing 	=  AE_Mailing::get_instance();
		$next_week 	=  strtotime("+1 week");

		$tbl_member 	= $wpdb->prefix . 'fre_membership';
		$tbl_subs 	= $wpdb->prefix . 'fre_subscriptions';

		$sql = "SELECT *  FROM $tbl_member m LEFT JOIN $tbl_subs sub ON m.subscr_id = sub.id
			WHERE sub.expiry_time < $next_week AND sub.is_sent_mail = 0 AND sub.payment_status = 'completed' LIMIT 50 ";
		$list_user = $wpdb->get_results($sql);

		// duyệt list user
		$message = str_ireplace( '[profile_link]', et_get_page_link('profile'), $message );

		$sub_ids = $mails = array();
		foreach ($list_user as $key => $subsriber) {
			$message 	= str_ireplace( '[user_login]',$subsriber->user_login, $message );
			$sub_ids[] 	= $subsriber->subscr_id;
			$mails[] 	= $subsriber->user_email;
			$mailing->wp_mail( $subsriber->user_email, $subject, $message);
			//insert_notification_expiry_soon($subsriber);
		}

		// đánh dấu tình trạng đã gửi email notiication.
		if( !empty($sub_ids) ){
			$whereIn = implode(',', $sub_ids);
			$sql_update = "UPDATE $tbl_subs SET is_sent_mail = 1 WHERE id IN (".$whereIn.") ";
			$wpdb->query($sql_update);
			et_cron_log(' List emails expiring soon '. join(",", $mails));
		} else {
			et_cron_log(' Checked and no subsriber expiring soon.');
		}

	}

	/**
	 * check user will expired today and auto deduct credit of theme to renew or email to let them know if they are not enough credit to renew
	*/
	function fre_membership_excute_auto_deduct(){
		global $wpdb;

		$today 		= time() + 2*60*60; // những user cùng ngày nhưng khác giờ.
		$yesterday 	= time(); // những user hết hạn từ hôm qua.
		$tbl_member 	= $wpdb->prefix . 'fre_membership';
		$tbl_subsc 	= $wpdb->prefix . 'fre_subscriptions';

	    $sql = "SELECT *  FROM $tbl_member m LEFT JOIN $tbl_subsc sub ON m.subscr_id = sub.id
				WHERE (  ( CAST(expiration_date AS DATE) = CAST(NOW() AS DATE)
				AND  expiry_time < $today ) OR expiry_time< $yesterday  )
				AND sub.auto_renew = 'active' AND sub.payment_gw = 'fre_credit'
				GROUP BY  m.user_id, sub.id LIMIT 50 ";

		$subscriptions = $wpdb->get_results($sql);

		$ids_renew = $id_fails = array();
		foreach ($subscriptions as $key => $subscriber) {
			$ids[] 			= $subscriber->id;
			$not_enough = false;
			$pack 		= membership_get_pack($subscriber->plan_sku, $subscriber->pack_type);
			if( is_active_fre_credit() && $pack ){

				$user_wallet 	= FRE_Credit_Users()->getUserWallet($subscriber->user_id);
				if( $user_wallet->balance >= $pack->et_price ){
					$ids_renew[] 			= $subscriber->id;
					$user_wallet->balance 	= $user_wallet->balance - $pack->et_price;
					FRE_Credit_Users()->setUserWallet($subscriber->user_id, $user_wallet);
					save_credit_history_subscriber($pack);
					update_auto_renew_success($subscriber, $pack);
					insert_notification_renew_success($subscriber);

				} else {
					$not_enough = true;
					et_cron_log($subscriber->user_email. ' has not enougt credit to renew. Fail');
				}
			}
			if( $not_enough || ! is_active_fre_credit() || !$pack ){
				// nếu 1 plan đã xóa => plan k có giá trị nên k thể auto renew được.
				$id_fails[] = $subscriber->id;
				do_action('after_auto_renew_fail',$subscriber, $pack);
				// insert_notification_renew_fail($subscriber);
			}
		}


		if($id_fails){
			$whereIn = implode(',', $id_fails);
			$slq_update 	= "UPDATE $tbl_subsc SET is_sent_mail = 1 , auto_renew = 'disable' WHERE id IN (".$whereIn.") ";
			$wpdb->query($slq_update);
		}

	}
	function filter_shortcode_content($message, $pack, $subscriber){
		$expiration_date 	= date('d M, Y', $subscriber->expiry_time);
		$message 	= str_ireplace( '[profile_link]', et_get_page_link('profile'), $message );
		$message 	= str_ireplace( '[user_login]', $subscriber->user_login, $message );
		$message 	= str_ireplace( '[plan_name]', $pack->post_title, $message );
		$message 	= str_ireplace( '[plan_price]', fre_membersip_price_format($subscriber), $message );
		$message 	= str_ireplace( '[expiration_date]', $expiration_date, $message );
		$message 	= str_ireplace( '[profile_link]', et_get_page_link('profile'), $message );
		return $message;
	}
	function debug_cron_job(){

		global $user_ID;
		echo 'Time: '.time();
		$t = wp_next_scheduled( 'excute_membership_event' );
		echo 'Time next excute: ';
		var_dump($t);

		$seconds_until_task_will_run =  wp_next_scheduled( 'excute_membership_event' ) - time();
		echo '<br />Number seconds will run schedule: '.$seconds_until_task_will_run;
		$name = wp_get_schedule('excute_membership_event');
		$event = wp_get_scheduled_event('excute_membership_event');
		$is_next = wp_next_scheduled( 'excute_membership_event' );
		$all = wp_get_schedules();
		var_dump($is_next);
		echo '<pre>';

		var_dump($event);
		var_dump($all);
		echo '</pre>';

	}
}

$GLOBALS['membership_cron'] = new Fre_Membership_Schedules();


?>