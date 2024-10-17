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
        
        $converted_request=$this->get_info_request($request_item);

        $subject=$admin_data->getValue('notify_admin_new_request_subject');
        $customer_subject=$admin_data->getValue('notify_customer_confirm_request_subject');

        $content=nl2br($admin_data->getValue('notify_customer_confirm_request_content'));
        
        //apply real data to email template
        
        //departure
        $content=str_ireplace('[departure_date]',$converted_request->departure_date,$content);
        $content=str_ireplace('[departure_address]',$converted_request->departure_address,$content);
        $content=str_ireplace('[departure_city]',$converted_request->departure_city,$content);
        $content=str_ireplace('[postal_code_depart]',$converted_request->postal_code_depart,$content);

        //arrival
        $content=str_ireplace('[arrival_date]',$converted_request->arrival_date,$content);
        $content=str_ireplace('[arrival_address]',$converted_request->arrival_address,$content);
        $content=str_ireplace('[arrival_city]',$converted_request->arrival_city,$content);
        $content=str_ireplace('[postal_code_arrival]',$converted_request->postal_code_arrival,$content);

        //contact method & name
        $content=str_ireplace('[customer_name]',$converted_request->last_name.' '.$converted_request->first_name,$content);
        $content=str_ireplace('[last_name]',$converted_request->last_name,$content);
        $content=str_ireplace('[phone_number]',$converted_request->phone_number,$content);
        $content=str_ireplace('[email_notification]',$converted_request->email_notification,$content);

        //send to the customer to confirm.
        $this->custom_send_email($converted_request->email_notification,$customer_subject,$content);

        //remove introduction text just keep information
        $start_pos = strpos($content, 'LE DÉMÉNAGEMENT');
        $end_pos = strpos($content, 'QUESTIONS ET REMARQUES');
        if ($start_pos !== false && $end_pos !== false) {            
            $admin_content = substr($content,$start_pos ,$end_pos - $start_pos);    
        }
    
        //send notification to admin.
        $this->custom_send_email(get_bloginfo('admin_email'),$subject,$admin_content);

    }

    function get_info_request($request)
    {
        //contact method
        $request->phone_number=get_post_meta($request->ID,'phone_number',true) ? : '';
        $request->email_notification=get_post_meta($request->ID,'email_notification',true) ? : '';

        $request->last_name=get_post_meta($request->ID,'last_name',true);
        $request->first_name=get_post_meta($request->ID,'first_name',true);
        //departure
        $depature_date=get_post_meta($request->ID,'departure_date',true);
        $departure_date_instance = DateTime::createFromFormat('Y-m-d', $depature_date);
        $request->departure_date= $departure_date_instance->format('Y-M-d');

        $request->departure_address=get_post_meta($request->ID,'departure_address',true);
        $request->postal_code_departure=get_post_meta($request->ID,'postal_code_depart',true);
        $departure_city_id=get_post_meta($request->ID,'departure_city_id',true);
        $departure_city=get_term_by('ID',(int)$departure_city_id,'city');
        if($departure_city)
        {
            $request->departure_city=$departure_city->name;
        }
        else
        {
            $request->departure_city='';
        }
       

        //arrival
        $arrival_date=get_post_meta($request->ID,'arrival_date',true);
        $arrival_date_instance = DateTime::createFromFormat('Y-m-d', $arrival_date);
        $request->arrival_date= $arrival_date_instance->format('Y-M-d');

        $request->arrival_address=get_post_meta($request->ID,'arrival_address',true);
        $request->postal_code_arrival=get_post_meta($request->ID,'postal_code_arrival',true);
        $arrival_city_id=get_post_meta($request->ID,'arrival_city_id',true);
        $arrival_city=get_term_by('id',(int)$arrival_city_id,'city');        
        if($arrival_city)
        {
            $request->arrival_city=$arrival_city->name;
        }
        else
        {
            $request->arrival_city='';
        }

        return $request;
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