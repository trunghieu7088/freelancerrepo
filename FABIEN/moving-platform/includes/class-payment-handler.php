<?php
class Payment_Handler
{
    public static $instance;

    function __construct(){        
		       
		$this->init_hook();     

	}
    function init_hook(){

		add_action('init',array($this, 'register_post_type_moving_payment' ));

        add_action('wp_ajax_single_checkout',array($this,'single_checkout_action'),99);                   
        add_action('wp_ajax_complete_order_request',array($this,'complete_order_request_action'),99);                   
        
        add_action('wp_ajax_m_checkout_requests',array($this,'m_checkout_requests_action'),99);     
        add_action('wp_ajax_complete_multiple_order_request',array($this,'complete_multiple_order_request_action'),99);                                 
        
	}

    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

     //generate moving payment custom post type
    function register_post_type_moving_payment()
    {
         $labels = array(
             'name'               => _x( 'Moving Payment', 'post type general name', 'textdomain' ),
             'singular_name'      => _x( 'Moving Payment', 'post type singular name', 'textdomain' ),
             'menu_name'          => _x( 'Moving Payments', 'admin menu', 'textdomain' ),
             'name_admin_bar'     => _x( 'Moving Payment', 'add new on admin bar', 'textdomain' ),
             'add_new'            => _x( 'Add New', 'Moving Payment', 'textdomain' ),
             'add_new_item'       => __( 'Add New Moving Payment', 'textdomain' ),
             'new_item'           => __( 'New Moving Payment', 'textdomain' ),
             'edit_item'          => __( 'Edit Moving Payment', 'textdomain' ),
             'view_item'          => __( 'View Moving Payment', 'textdomain' ),
             'all_items'          => __( 'All Moving Payments', 'textdomain' ),
             'search_items'       => __( 'Search Moving Payment', 'textdomain' ),
             'not_found'          => __( 'No Moving Payments found', 'textdomain' ),
             'not_found_in_trash' => __( 'No Moving Payments found in trash', 'textdomain' ),
         );
     
         $args = array(
             'labels'             => $labels,
             'public'             => true,
             'publicly_queryable' => true,
             'show_ui'            => true,
             'show_in_menu'       => true,
             'query_var'          => true,
             'rewrite'            => array( 'slug' => 'moving_payment' ),
             'capability_type'    => 'post',
             'has_archive'        => true,
             'hierarchical'       => false,
             'menu_position'      => null,
             'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
         );
     
         register_post_type( 'moving_payment', $args );
    }

    function single_checkout_action()
    {
        if(!is_user_logged_in())
        {
            die('');
        }

        if (!wp_verify_nonce($_POST['single_checkout_nonce'],'single_checkout_nonce')) {
            die('');
        } 
        extract($_POST);
        $admin_data=AdminData::get_instance();
        $stripe_instance = new \Stripe\StripeClient($admin_data->getValue('moving_stripe_sk'));

        try {
            // Attempt to create the PaymentIntent
            $custom_paymentIntent = $stripe_instance->paymentIntents->create([
                'amount' => $pay_request_price * 100, // convert EUR to cents
                'currency' => 'eur',
                'payment_method_types' => ['card'],
            ]);
    
            // If successful, return or process the paymentIntent (e.g., send client secret to frontend)
            $data['client_secret']= $custom_paymentIntent->client_secret;
            $data['paid_user_id']=get_current_user_id();
            $data['pay_request_id']=$pay_request_id;
            $data['success']='true';
            wp_send_json($data);
            die();        
    
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle error from Stripe API
            return wp_send_json_error([
                'error' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            // Handle other potential errors
            return wp_send_json_error([
                'error' => __('An error occurred while creating the payment intent. Please try again.','moving_platform'),
            ]);
        }

    }

    function complete_order_request_action()
    {
        if(!is_user_logged_in())
        {
            die('');
        }
        extract($_POST);
    
        //if this is multiple checkout, update args
        $is_multiple_type=false;
        if(isset($is_multiple) && ( $is_multiple==true || $is_multiple=='true'))      
        {
            $is_multiple_type=true;
        }

        if($is_multiple_type)      
        {
            $order_args=array(
                'post_title'=>'Order of m-checkout '.$payment_intentID,
                'post_content'=>'content of m-checkout '.$payment_intentID,
                'post_status'=>'publish',
                'post_type'=>'moving_payment',
                'post_author'=>$user_id,
            );
            
        }
        else
        {
            $request_info=get_post($request_item);    
            $order_args=array(
                'post_title'=>'Order of #'.$request_info->ID.' | '.$request_info->post_title,
                'post_content'=>'Content of '.$request_info->post_title,
                'post_status'=>'publish',
                'post_type'=>'moving_payment',
                'post_author'=>$user_id,
            );
            
        }

        $order_created=wp_insert_post($order_args);

        if($order_created && !is_wp_error($order_created))
        {
            //update stripe payment info
            update_post_meta($order_created,'stripe_payment_intent',$payment_intentID);
            update_post_meta($order_created,'stripe_payment_status',$payment_status);
            update_post_meta($order_created,'stripe_payment_currency',$payment_currency_code);
            update_post_meta($order_created,'stripe_payment_createdDate',$payment_created);

            //update paid request info

            if($is_multiple_type)
            {                
                //update as array, if multiple --> request_items not request_item
                $adding_request=explode(',',$request_items);
                update_post_meta($order_created,'request_item',$adding_request);
            }
            else
            {
                //update as array
                $request_array=array($request_item);
                update_post_meta($order_created,'request_item',$request_array);
            }

            update_post_meta($order_created,'payment_amount',$payment_amount);
            update_post_meta($order_created,'paid_user_id',$user_id);

            $contact_method='';
            $data['request_item']='';
            $data['is_multiple']=$is_multiple_type;

            if(!$is_multiple_type)
            {
                $contact_method=get_post_meta($request_item,'contact_method',true);
                $data['request_item']=$request_item;
            }                      

             //create hook for future custom
             do_action('custom_hook_after_insert_payment',$order_created);

            $data['message']=__('The payment has been completed successfully !','moving_platform');
            $data['success']='true';
            $data['contact_method']=$contact_method;
                       
            //redirect with filter to only show my paid list
            $data['redirect_url']=site_url('/all-requests/?mine=yes'); 
        }
        else
        {
            $data['message']=__('Failed to send payment !','moving_platform');
            $data['success']='false';
        }

        wp_send_json($data);
        die();
    }

    function m_checkout_requests_action()
    {
        if(!is_user_logged_in())
        {
            die('');
        }

        if (!wp_verify_nonce($_POST['m_checkout_nonce'],'m_checkout_nonce')) {
            die('');
        } 
        extract($_POST);
        $admin_data=AdminData::get_instance();
        $stripe_instance = new \Stripe\StripeClient($admin_data->getValue('moving_stripe_sk'));

        try {
            // Attempt to create the PaymentIntent
            $custom_paymentIntent = $stripe_instance->paymentIntents->create([
                'amount' => $total_price * 100, // convert EUR to cents
                'currency' => 'eur',
                'payment_method_types' => ['card'],
            ]);
    
            // If successful, return or process the paymentIntent (e.g., send client secret to frontend)
            $data['client_secret']= $custom_paymentIntent->client_secret;
            $data['paid_user_id']=get_current_user_id();            
            $data['success']='true';
            wp_send_json($data);
            die();        
    
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle error from Stripe API
            return wp_send_json_error([
                'error' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            // Handle other potential errors
            return wp_send_json_error([
                'error' => __('An error occurred while creating the payment intent. Please try again.','moving_platform'),
            ]);
        }

    }

   
}

new Payment_Handler();

function check_paid_request($user_id,$request_id)
{
    //return false if the users is not logged
    if(!is_user_logged_in())
    {
        return false;
    }

    $request_args = array(
        'post_type'   => 'moving_payment',  
        'post_status' => 'publish',  
        'numberposts' => -1,
        'fields'      => 'ids',    
        'author'      => $user_id,                 
        'meta_query'  => array(             
            array(
                'key'   => 'request_item',   
                'value' =>  $request_id,   
                'compare' => 'LIKE',
            ),
        ),        
    );

    $found_request=get_posts($request_args);

    // Return true if at least 1 post is found, else false
     return !empty($found_request);
  
}


function get_paid_list_by_user_id($user_id)
{
    $paid_request_collection=array();

    $paid_list_args = array(
        'post_type'   => 'moving_payment',  
        'post_status' => 'publish',  
        'numberposts' => -1,
        'fields'      => 'ids',    
        'author'      => $user_id,                 
    );

    $paid_list=get_posts($paid_list_args);
    
    if($paid_list)
    {
        foreach($paid_list as $paid_item)
        {
            $adding_item=get_post_meta($paid_item,'request_item',true);
            if(!is_array($adding_item))
            {
                $paid_request_collection[]=$adding_item;
            }
            else
            {
                //if array --> merge
                $paid_request_collection=array_merge($paid_request_collection,$adding_item);
            }
            
        }
    }

    return $paid_request_collection;
}