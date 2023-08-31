<?php

class fre_membership_mailing {
	public $mailing;
	public $admin_email;
	function __construct(){
		$this->mailing 		=  AE_Mailing::get_instance();
		$this->admin_email 	= ae_get_option('fre_membership_admin_emails', '');
		if( empty($this->admin_email) ) $this->admin_email = get_option('admin_email');

		add_action('after_save_membership',array($this,'thankyou_subscriber'));
		add_filter('et_mail_css', array($this,'style_email') );
		add_action('subscriber_disable_auto_renew',array($this,'user_disable_auto_renew') );
		add_action('after_auto_renew_success',array($this,'send_mail_after_renew_success'), 10, 2 );
		add_action('after_auto_renew_fail',array($this,'send_mail_after_auto_renew_fail'), 10, 2 );



	}
	function send_mail_after_auto_renew_fail($subscriber, $package){
		$subject_fail 	= __('Could not renew your subscriptoin.','fre_membership');
		$message_fail 	= ae_get_option('fre_membership_auto_renew_fail_email', false);
		if($package){
			$message_fail = $this->filter_shortcode_content($message_fail, $package, $subscriber);
		} else {
			$message_fail = get_df_auto_renew_fail_email_no_pack($subscriber);
		}
		$this->wp_mail( $subscriber->user_email, $subject_fail, $message_fail);
	}
	function send_mail_after_renew_success($subscriber, $package){
		et_cron_log('User account:'.$subscriber->user_email.' has been auto renewew.');

		$message = ae_get_option('fre_membership_auto_renew_success_email', false);

		if($package){
			$message = $this->filter_shortcode_content($message, $package, $subscriber);
		}
		$subject = __('Your subscription has been renewed','enginethemes');
		$this->mailing->wp_mail( $subscriber->user_email, $subject, $message);


	}
	function style_email(){
		ob_start();
		?>
		<style>
		ul li{
			list-style: none; display: block;
		}
		  ul li span{
            width: 130px;
            display: inline-block;
        }
		</style>
		<?php
		return ob_get_clean();
	}
	function thankyou_subscriber($user_id){

		$message 			= ae_get_option('subscriber_successful_mail_template', '');
		if(empty($message))
			$message 		= get_df_subscriber_successful_mail_template();
		$subscription 		= get_mebership_of_member($user_id);
		$pack       		= membership_get_pack($subscription->plan_sku, $subscription->pack_type);
		$expiration_date 	= date('d M, Y', $subscription->expiry_time);
		if($pack){
			$message 	= $this->filter_shortcode_content($message, $pack, $subscription);
		}
		$subject 	= __('Thank you for your subscribe','enginethemes');
		$this->mailing->wp_mail($subscription->user_email, $subject, $message);
		$subject 	= __('There is new subscriber on your website','enginethemes');
		$content 	= subscriber_successful_notify_admin_mail();
		$content 	= $this->filter_shortcode_content($content, $pack, $subscription);
		$this->mailing->wp_mail($this->admin_email, $subject, $content);


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
	/*
	 Send to user after he disable auto renew.
	*/
	function user_disable_auto_renew($subscriber){

		$pack 				= membership_get_pack($subscriber->plan_sku, $subscriber->pack_type);
		$expiration_date 	= date('d M, Y', $subscriber->expiry_time);
		$admin_msg  		= ae_get_option('fre_cancel_membership_admin_email', '');
		if( empty($admin_msg))
			$admin_msg = get_cancel_membership_admin();
		if($pack){
			$admin_msg 	= $this->filter_shortcode_content($admin_msg, $pack, $subscriber);
		}

		$subject = __('Subscriber has disabled auto-renewal.','enginethemes');

        $this->mailing->wp_mail( $this->admin_email, $subject, $admin_msg );

        $user_email = $subscriber->user_email;
        $message 	= ae_get_option('fre_cancel_membership_email', '');
        if(empty($message))
        	$message = get_cancel_membership();
        if($pack){
        	$message = $this->filter_shortcode_content($message,$pack, $subscriber);
		}

        $subject = __('Your subscription has been disable auto-renewal','enginethemes');
        $this->mailing->wp_mail( $user_email, $subject, $message );


	}
}
new fre_membership_mailing();
