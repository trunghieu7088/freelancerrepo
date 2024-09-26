<?php
class Email_Feature
{
    public static $instance;

    function __construct(){        
		       
		$this->init_hook();     

	}
    function init_hook(){

        add_action('custom_hook_after_insert_request',array($this,'send_noti_email_to_admin'),99,1);        
        add_action('custom_hook_after_insert_payment',array($this,'send_noti_email_payment_provider_admin'),99,1);
        
	}

    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function custom_send_email($recipient_email,$subject,$body,$headers=array())
    {
        if(empty($headers))
        {
            $headers = array('Content-Type: text/html; charset=UTF-8', 'From: '.get_option('admin_email'));        
        }        
        wp_mail($recipient_email, $subject, $body, $headers);
    }

    //send email to admin to notify about new request submitted
    function send_noti_email_to_admin($request_id)
    {
        $request_item=get_post($request_id);
        $admin_data=AdminData::get_instance();

        $subject=$admin_data->getValue('notify_admin_new_request_subject');
        $content=$admin_data->getValue('notify_admin_new_request_content');

        $request_info='<p>'.$request_item->post_title.'</p>';

        $content=str_ireplace('[request_title]',$request_info,$content);
        
        $this->custom_send_email(get_bloginfo('admin_email'),$subject,$content);

    }

    //send email to the service proivder to nofity about the payment has been sent successfully
    function send_noti_email_payment_provider_admin($payment_id)
    {
        $payment_item=get_post($payment_id);
        $recipient=get_user_by('ID',$payment_item->post_author);
        $admin_data=AdminData::get_instance();

        $subject=$admin_data->getValue('confirm_new_order_subject');
        $content=$admin_data->getValue('confirm_new_order_content');
        $paid_list='<a href="'.site_url('/all-requests/?mine=yes').'">'.__('Paid list','moving_platform').'</a>';

        $subject=str_ireplace('[payment_id]','#'.$payment_item->ID,$subject);

        $content=str_ireplace('[paid_list]',$paid_list,$content);

        $this->custom_send_email( $recipient->user_email,$subject,$content);

        //also send email to admin about new payment
        $subject_admin=$admin_data->getValue('notify_admin_new_order_subject');
        $content_admin=$admin_data->getValue('notify_admin_new_order_content');

        $payment_info=get_post_meta($payment_item->ID,'budget',true).'€';

        $subject_admin=str_ireplace('[payment_id]','#'.$payment_item->ID,$subject_admin);

        $content_admin=str_ireplace('[payment_price]',$payment_info,$content_admin);

        $this->custom_send_email( get_bloginfo('admin_email'),$subject_admin,$content_admin);
    }   

}

new Email_Feature();