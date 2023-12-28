<?php
require 'guzzle/vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

require('add-required-page.php');
require('add-assets-files.php');
require('admin-subscription.php');

//register post type for subscription plan
function register_subscription_plan_post_type() {
    $labels = array(
        'name'               => _x( 'Subscription Plans', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'Subscription Plan', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'Subscription Plans', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'Subscription Plan', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'Add New', 'subscription_plan', 'textdomain' ),
        'add_new_item'       => __( 'Add New Subscription Plan', 'textdomain' ),
        'new_item'           => __( 'New Subscription Plan', 'textdomain' ),
        'edit_item'          => __( 'Edit Subscription Plan', 'textdomain' ),
        'view_item'          => __( 'View Subscription Plan', 'textdomain' ),
        'all_items'          => __( 'All Subscription Plans', 'textdomain' ),
        'search_items'       => __( 'Search Subscription Plans', 'textdomain' ),
        'not_found'          => __( 'No subscription plans found', 'textdomain' ),
        'not_found_in_trash' => __( 'No subscription plans found in trash', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'subscription_plan' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
    );

    register_post_type( 'subscription_plan', $args );
}

add_action( 'init', 'register_subscription_plan_post_type' );


//register post type for subscriptions
function register_subscription_post_type() {
    $labels = array(
        'name'               => _x( 'Custom Subscription', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'Custom Subscription', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'Custom Subscriptions', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'Custom Subscription', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'Add New', 'custom_subscription', 'textdomain' ),
        'add_new_item'       => __( 'Add New Subscription', 'textdomain' ),
        'new_item'           => __( 'New Subscription', 'textdomain' ),
        'edit_item'          => __( 'Edit Subscription', 'textdomain' ),
        'view_item'          => __( 'View Subscription', 'textdomain' ),
        'all_items'          => __( 'All Subscriptions', 'textdomain' ),
        'search_items'       => __( 'Search Subscription', 'textdomain' ),
        'not_found'          => __( 'No subscriptions found', 'textdomain' ),
        'not_found_in_trash' => __( 'No subscriptions found in trash', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'custom_subscription' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
    );

    register_post_type( 'custom_subscription', $args );
}

add_action( 'init', 'register_subscription_post_type' );


//add_action('wp_head','add_paypal_sdk');
add_action('wp_footer','add_paypal_sdk');
function add_paypal_sdk()
{
    $client_id=get_option('custom_paypal_client_id');
   // echo '<script src="https://www.paypal.com/sdk/js?client-id=AVKU8yOW0vA-hT6kI7uIlHSOqSRT2nfb-C_qtot59urXnrd2LWIfGwAOkUeFRkfnV1DoEsWi9M0-lssd"></script>';
   echo '<script src="https://www.paypal.com/sdk/js?client-id='.$client_id.'&vault=true&intent=subscription" data-sdk-integration-source="button-factory"></script>';
}


//add_action('wp_head','add_plan_subscription_id_to_js');
function add_plan_subscription_id_to_js()
{
    if(isset($_GET['plan']) && !empty($_GET['plan']))
    {
        
        $args = array(
            'name'           => $_GET['plan'],
            'post_type'      => 'subscription_plan',  // Replace with your post type if it's different
            'posts_per_page' => 1,       // Limit to 1 result
            'post_status' =>'publish',
        );
        
        $plan_info = new WP_Query($args);
        if ($plan_info->have_posts()) 
        {            
            $plan_info->the_post();
            $plan_id = get_post_meta(get_the_ID(),'paypal_plan_id',true);
        }                         
        wp_reset_postdata();
    ?>
        <script type="text/javascript">
            var plan_id='<?php echo $plan_id; ?>';
            //console.log(plan_id);
        </script>
    <?php
    }
}

//show menu if you are super admin
add_action('mje_after_user_dropdown_menu','add_subscription_menu_admin',9999,0);

function add_subscription_menu_admin()
{
    if(is_super_admin())
    {
    ?>
    <li>
		<a href="<?php echo site_url('manage-subscription') ?>">Manage Subscription</a>
	</li>
    <?php
    }

    $wp_current_user=wp_get_current_user();
    if ( in_array( 'client', $wp_current_user->roles, true ) ) {
        $is_client=true;
    }
    else
    {
        $is_client=false;
    }
  
    if(is_user_logged_in() && !$is_client)
    {
    ?>
    <li>
		<a href="<?php echo site_url('mysubscription') ?>">My Subscription</a>
	</li>
    <?php
    }

}

//add subscription menu item to sidebar menu

add_action('mje_after_user_sidebar_menu','add_subscription_menu_to_sidebar',9999,0);

function add_subscription_menu_to_sidebar()
{
    $wp_current_user=wp_get_current_user();
    if ( in_array( 'client', $wp_current_user->roles, true ) ) {
        $is_client=true;
    }
    else
    {
        $is_client=false;
    }
    if(is_user_logged_in() && !$is_client)
    {
    ?>
        <li class="hvr-wobble-horizontal"><a href="<?php echo site_url('mysubscription'); ?>">My Subscription</a></li>
    <?php
    }
}


//convert plan data for display on pricing plan page
function convert_subscription_plan($plan)
{
    $converted_plan['title']=$plan->post_title;
    $converted_plan['description']=get_post_meta($plan->ID,'plan_short_description',true);
    $converted_plan['price_text']=get_post_meta($plan->ID,'price_per_month',true).'$';
    $converted_plan['subtitle']=get_post_meta($plan->ID,'plan_subtitle',true);
    $converted_plan['plan_number_posts']=get_post_meta($plan->ID,'plan_number_posts',true);
    $converted_plan['slug']=$plan->post_name;    
    $converted_plan['transaction_fee']=get_post_meta($plan->ID,'plan_transaction_fee_percent',true) ? get_post_meta($plan->ID,'plan_transaction_fee_percent',true) : 0;
    $converted_plan['transaction_fee_text']=$converted_plan['transaction_fee'].'%';
    $converted_plan['paypal_plan_id']=get_post_meta($plan->ID,'paypal_plan_id',true);
    $converted_plan['is_free_plan']=get_post_meta($plan->ID,'is_free_plan',true) ? get_post_meta($plan->ID,'is_free_plan',true) : false;    
    if($plan->post_status=='publish')
    {
        $plan_status='Active';
    }
    else
    {
        $plan_status='Disabled';
    }
    $converted_plan['plan_status']=$plan_status;
    
    for($advertise_text = 1; $advertise_text <= 6; $advertise_text++)
    {
        $advertise_text_display=get_post_meta($plan->ID,'title_advertisement'.$advertise_text,true) ? get_post_meta($plan->ID,'title_advertisement'.$advertise_text,true) : 'false';        
        $converted_plan['advertisement'][]=$advertise_text_display;
    }
    $converted_plan['advertisement'][]=$plan->post_title;

    return (object)$converted_plan;    
}

// ajax to handle data when the users subscribe plan and make payment
add_action( 'wp_ajax_subscribePlan', 'subscribePlan_function');

// ajax to handle data when the users subscribe free plan
add_action( 'wp_ajax_subscribeFreePlan', 'subscribePlan_function');

function subscribePlan_function()
{
    if (!is_user_logged_in()) {        
        die('something went wrong');
    }
    if (!wp_verify_nonce($_POST['subscribe_plan_nonce'],'subscribe_plan_nonce')) {
        die('something went wrong');
    } 
    extract($_POST);
    $current_user=wp_get_current_user();    
    $plan_info=get_post($wp_plan_id);
    $transaction_fee=get_post_meta($plan_info->ID,'plan_transaction_fee_percent',true);
    $user_profile_id=get_user_meta( $current_user->ID,'user_profile_id',true);
    $subscribe_plan=array('post_title' =>'subscription-'.$current_user->user_login.'-'.$plan_info->post_name,
                            'post_content'=>'subscription'.$current_user->user_login.' '.$wp_plan_id,
                            'post_status'  => 'publish',
                            'post_author' => $current_user->ID,
                            'post_type' =>'custom_subscription',
                        );
      $subscribe_plan_id=wp_insert_post($subscribe_plan);
      $numberposts=get_post_meta($wp_plan_id,'plan_number_posts',true);
      
      $price=get_post_meta($wp_plan_id,'price_per_month',true);

      //handle for free plan
      if($price==0 || $price=='0')
      {
        $paypal_subscription_id='free_plan_no_card';
        $paypal_paymentSource='free_plan_no_payment_source';
        $paypal_order_id='free_plan_no_paypal_order_id';        
      }
      //end

      if($subscribe_plan && !is_wp_error($subscribe_plan))                      
      {

        //set cancelled status for the previous subscription
         //get subscription status and subscription ID
         $old_subscription_status=get_post_meta($user_profile_id,'subscription_status',true);

         $current_subscription_plan=get_post_meta($user_profile_id,'current_subscription_plan',true);
 
         if($old_subscription_status=='Active' &&  $current_subscription_plan)
         {
            update_post_meta($current_subscription_plan,'subscription_status','Cancelled');
         }
        //end

         update_post_meta($subscribe_plan_id,'wp_plan_id',$wp_plan_id);
         update_post_meta($subscribe_plan_id,'paypal_plan_id',$paypal_plan_id);
         update_post_meta($subscribe_plan_id,'paypal_order_id',$paypal_order_id);
         update_post_meta($subscribe_plan_id,'paypal_subscription_id',$paypal_subscription_id);
         update_post_meta($subscribe_plan_id,'plan_remain_post',$numberposts);
         update_post_meta($subscribe_plan_id,'plan_name',$plan_info->post_title);
         update_post_meta($subscribe_plan_id,'paypal_paymentSource',$paypal_paymentSource);         
         update_post_meta($subscribe_plan_id,'plan_price',$price);
         update_post_meta($subscribe_plan_id,'subscription_status','Active');
         update_post_meta($subscribe_plan_id,'transaction_fee',$transaction_fee);

        
         
        $subscription_time=new DateTime();
        update_post_meta($subscribe_plan_id,'subscription_date',$subscription_time->format('Y-m-d'));

        
        
         //total renewal times and total paid and last_subscription_date                  

         update_post_meta($subscribe_plan_id,'total_paid',$price);         
         update_post_meta($subscribe_plan_id,'total_renewal_times',1);
         update_post_meta($subscribe_plan_id,'last_subscription_date',$subscription_time->format('Y-m-d'));
         if($price != 0)
         {
            //update this to skip the first time of webhook payment.sale.completed
            update_post_meta($subscribe_plan_id,'skip_first_payment_webhook','yes');
         }
        
         //update for user profile
         update_post_meta($user_profile_id,'current_subscription_plan',$subscribe_plan_id);
         update_post_meta($user_profile_id,'subscription_status','Active');

         //publish all archived mjobs
         get_and_handle_all_mjob_of_user($current_user->ID,'publish');

          //add paypal plan id to free plan collection to user profile , this will prevent users from unsubscribe and re-subscribe free plan
        
          $is_free_plan=get_post_meta($wp_plan_id,'is_free_plan',true);
          if( $is_free_plan== 'true')
         {
           
             $free_plan_collection=get_post_meta($user_profile_id,'free_plan_collection',true);
             if($free_plan_collection && gettype( $free_plan_collection)=='array')
             {
                 array_push($free_plan_collection,$paypal_plan_id);
             }
             else
             {
                 $free_plan_collection=array($paypal_plan_id);
             }
             update_post_meta($user_profile_id,'free_plan_collection',$free_plan_collection);
             
         }
         //end

         //send email when subscribe successfully
                
         $subject= 'New subscription on '.get_bloginfo( 'name' );
         $link='<a href="'.site_url('mysubscription').'">here</a>';

         $message='Dear '.$current_user->display_name.',';

         $message.='<p>Thank you for subscribing to Guide Per Hour!</p>';

         $message.='<p>We are thrilled to have you join our community of experts. Your subscription unlocks a world of opportunities to share your knowledge and skills with a diverse audience eager to learn.</p>';

         $message.='<p>As a valued member, you now have the privilege to start posting your sessions. Whether it\'s one-on-one guidance or in-depth tutorials, your expertise is key in creating meaningful and impactful learning journeys.</p>';

         $message.='<p>We understand the importance of flexibility in your commitments. That’s why at Guide Per Hour, you have the freedom to upgrade or cancel your subscription at any time. This ensures that our platform aligns with your changing needs and preferences.</p>';

         $message.='<p>We are committed to supporting you every step of the way. Should you need any assistance or have queries about setting up your sessions, managing your subscription, or anything else, our dedicated team is here to help.</p>';

         $message.='<p>Welcome to Guide Per Hour, where your expertise meets endless possibilities.</p>';

         $message.='<p>Warm regards,</p>';

         $message.='<p>The Guide Per Hour Team</p>';                        

         subscription_send_mail($current_user->user_email,$subject,$message);
         //end send email
         
         $data['message']='Subscribe successfully';
         $data['success']='true';
         $data['redirect_url']=site_url('/mysubscription/');
      }
      else
      {
        $data['message']='Something wrong ..please refresh !';
        $data['success']='false';
        
      }
      wp_send_json($data);
      die();

}



function convert_subscription($current_subscription)
{
    $converted_subscription['subscription_id']=$current_subscription;
    $converted_subscription['wp_plan_id']=get_post_meta($current_subscription,'wp_plan_id',true);  
    $converted_subscription['remaining_post']=get_post_meta($current_subscription,'plan_remain_post',true);
    $converted_subscription['remaining_post_text']=get_post_meta($current_subscription,'plan_remain_post',true).' Posts';        
    $converted_subscription['plan_name']=get_post_meta($current_subscription,'plan_name',true);
    $converted_subscription['plan_price']=get_post_meta($current_subscription,'plan_price',true);
    $converted_subscription['plan_price_text']=get_post_meta($current_subscription,'plan_price',true).' $';
    $converted_subscription['subscription_date_raw']=get_post_meta($current_subscription,'subscription_date',true);
    $display_time=new DateTime(get_post_meta($current_subscription,'last_subscription_date',true));
    $converted_subscription['subscription_date_show']=$display_time->format('Y-m-d');
    $display_time->modify('+1 month');
    $converted_subscription['next_subscription_date_show']=$display_time->format('Y-m-d');
    
    return (object)$converted_subscription;  
}

//check user if he can post a service
function check_capability_submit_service()
{
    if(is_user_logged_in())
    {
       $user_profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);
       $current_subscription=get_post_meta($user_profile_id,'current_subscription_plan',true);
       $subscription_status=get_post_meta($user_profile_id,'subscription_status',true);
       if($current_subscription && $subscription_status=='Active')
       {          
         // check the rest posts --> if it = 0 redirect to pricing page
          $remaining_post=(int)get_post_meta($current_subscription,'plan_remain_post',true);
          if($remaining_post > 0)
          {
             return true;
          }
          else
          {
             return false;
          }
       }
       else
       {
            return false;
       }
    }
    else
    {
        return false;
    }
}

// function redirect the users if he can not submit a service
add_action('template_redirect','redirect_if_cannot_postjob');

function redirect_if_cannot_postjob()
{    
  global $post;    
  if(isset($post) && !empty($post))
  {
      $template_page=get_post_meta($post->ID,'_wp_page_template',true);       
      if($template_page=='page-post-service.php')
      {
        $is_capable_of_submitJob=check_capability_submit_service();
        if($is_capable_of_submitJob==false)
        {
            wp_redirect(site_url('subscription'));
        }
      }
  }
}

//substract post when post a service successfully

add_action('ae_insert_mjob_post','substract_post_when_submit_post',10,2);

function substract_post_when_submit_post($result,$args)
{
    $user_profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);
    $current_subscription=get_post_meta($user_profile_id,'current_subscription_plan',true);
    if($current_subscription)
    {       
       $remaining_post=(int)get_post_meta($current_subscription,'plan_remain_post',true);
       $remaining_post-=1;
        update_post_meta($current_subscription,'plan_remain_post',$remaining_post);
    }
}

//add menu subscription to top header menu 

add_action('mje_before_user_dropdown_menu','add_subscription_to_top_headerMenu',999,0);

function add_subscription_to_top_headerMenu()
{
    $wp_current_user=wp_get_current_user();
    if ( in_array( 'client', $wp_current_user->roles, true ) ) {
        $is_client=true;
    }
    else
    {
        $is_client=false;
    }
    if(!$is_client)
    {
    $htmlLink='<div class="link-post-services">';
    $htmlLink.= '<a href="'.site_url('subscription').'">';
    $htmlLink.=  'Subscription</a></div>';                
    ?>
    <script type="text/javascript">
    (function ($) {
        $(document).ready(function() {
            $("#myAccount").prepend('<?php echo $htmlLink; ?>');
        });
    })(jQuery);
    </script>
    <?php     
    }
}

//unsubscribe plan

add_action( 'wp_ajax_unsubcribe_plan', 'unsubcribe_plan_function');

function unsubcribe_plan_function()
{
    if (!is_user_logged_in()) {        
        die('something went wrong');
    }
    if (!wp_verify_nonce($_POST['unsubcribeNonce'],'unsubcribe_plan_nonce')) {
        die('something went wrong');
    } 
    $current_user=wp_get_current_user();
    $user_profile_id=get_user_meta($current_user->ID,'user_profile_id',true);
    $current_subscription=get_post_meta( $user_profile_id,'current_subscription_plan',true);
    
    //get price of subscription
    $price=get_post_meta( $current_subscription,'plan_price',true);    
    
    extract($_POST);
    //check if the current subscription match with cancel request
    if($subscriptionID ==$current_subscription)
    {
        //set data for user profile
        update_post_meta($user_profile_id,'current_subscription_plan','');
        update_post_meta($user_profile_id,'subscription_status','Cancelled');

        //set data for subscription post
        update_post_meta($subscriptionID,'subscription_status','Cancelled');

        //unsubscribe with Paypal by API
        $paypal_subscription_id=get_post_meta($subscriptionID,'paypal_subscription_id',true);
        $wp_user_info=$current_user->ID.' - '.$current_user->display_name;
        if($paypal_subscription_id)
        {
            if($price > 0)
            {
                cancel_paypal_subscription($paypal_subscription_id,$wp_user_info);
            }
           
            

            //publish or archive mjob
            //archive here because cancel subscription will archive all mjobs
            get_and_handle_all_mjob_of_user($current_user->ID,'archive');
        }
        $data['message']='Cancel subscription successfully';
        $data['success']='true';        
    }
    else
    {
        $data['message']='something went wrong...Please refresh';
        $data['success']='false';        
    }
       
    wp_send_json($data);
    die();

}

function cancel_paypal_subscription($subscriptionID,$wp_user_info)
{
    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);

    $response = $client->post('billing/subscriptions/'.$subscriptionID.'/cancel', [
        'headers' => [
            'Authorization' => 'Bearer '.$access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',                        
        ],
        'json' => [
            'reason' => $wp_user_info.' cancel the subscription manually',
        ],
    ]
    );
    $body = $response->getBody();
    $unsubscribe_data = json_decode($body, true);
    return $unsubscribe_data;

}

function get_and_handle_all_mjob_of_user($userID,$action)
{         
    $update_status=$action;
    $args=array('post_type' => 'mjob_post',
                'posts_per_page' => -1,
                'author'=> $userID,                
                'post_status' =>array('publish','archive'),
                 );
    $query = new WP_Query($args);    
    if ($query->have_posts()) 
    {    
        while ($query->have_posts())
        {
            $query->the_post();                                 
            $archive_post = array(
                'ID' => get_the_ID(),
                'post_status' => $update_status,
            );
            wp_update_post($archive_post);                                          
        }
    }    
    wp_reset_postdata();      
}

function capability_to_resubscribe($paypal_id)
{
    $user_profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);
    
    $current_subscription=get_post_meta($user_profile_id,'current_subscription_plan',true);
  
    $subscription_status=get_post_meta($user_profile_id,'subscription_status',true);
    
    $remaining_post=(int)get_post_meta($current_subscription,'plan_remain_post',true);
    
    $paypal_plan_id=get_post_meta($current_subscription,'paypal_plan_id',true);        
   
    if($subscription_status=='Active' && $remaining_post > 0 && $paypal_id== $paypal_plan_id) // still able to use plan and can not re-subscribe
    {
        return false;        
    }
    else
    {
        return true;        
    }

}

//auto renew free plan

add_action('wp_loaded','auto_renew_free_plan');

function auto_renew_free_plan()
{
    if(is_user_logged_in())
    {    
        $user_profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);

        //get subscription status and subscription ID
        $subscription_status=get_post_meta($user_profile_id,'subscription_status',true);

        $current_subscription_plan=get_post_meta($user_profile_id,'current_subscription_plan',true);

        if($subscription_status=='Active' &&  $current_subscription_plan)
        {
            $wp_plan=get_post_meta($current_subscription_plan,'wp_plan_id',true);
            $transaction_fee=get_post_meta($wp_plan,'plan_transaction_fee_percent',true);
            if($wp_plan)
            {
                $is_free_plan=get_post_meta($wp_plan,'is_free_plan',true);
                if($is_free_plan=='true')
                {
                    //get latest subscription date
                    $last_subscription_date=get_post_meta($current_subscription_plan,'last_subscription_date',true);
                    $last_subscription_date_timestamp = DateTime::createFromFormat('Y-m-d', $last_subscription_date);
                    $last_subscription_date_timestamp->modify('+1 month');
                    //compare the next renewal time with the current time
                    if(strtotime($last_subscription_date_timestamp->format('Y-m-d')) <= strtotime(date('Y-m-d')))
                    {                       
                        
                        //update total renewal times
                        $total_renewal_times=(int)get_post_meta($current_subscription_plan,'total_renewal_times',true);
                        $total_renewal_times+=1;                        
                        update_post_meta($current_subscription_plan,'total_renewal_times',$total_renewal_times);
                        
                        //renew the number of posts
                        $number_posts_of_plan=get_post_meta($wp_plan,'plan_number_posts',true);
                        update_post_meta($current_subscription_plan,'plan_remain_post',$number_posts_of_plan);                        

                        //update last time renew
                        update_post_meta($current_subscription_plan,'last_subscription_date',date('Y-m-d'));
                        
                        update_post_meta($current_subscription_plan,'transaction_fee',$transaction_fee);

                        //send email when renew free plan successfully
                        $wp_plan_info=get_post($wp_plan);
                        $wp_subscription=get_post($current_subscription_plan);
                        $user_subscription=get_user_by('ID',$wp_subscription->post_author);
                       
                            
                        $subject= 'New subscription on '.get_bloginfo( 'name' );
                        $link='<a href="'.site_url('mysubscription').'">here</a>';
                     
                        $message='The '.$wp_plan_info->post_title.' has automatically renew successfully';       
                        $message.='<p> Please click '.$link.' for more information';                    
                                                    
                        subscription_send_mail($user_subscription->user_email,$subject,$message);
                        //end send email
                    }                

                }
            }
        }

    }
}


//create webhook on paypal via API
add_action('wp_loaded','create_paypal_webhook');

function create_paypal_webhook()
{    
    $webhook_listnerID=get_option('paypal_webhook_listnerID',false);
    if(empty($webhook_listnerID) || !isset($webhook_listnerID) || $webhook_listnerID==false)
    {        
        $access_token=get_Paypal_access_token();
        if($access_token)
        {
                $client = new Client([
                    'base_uri' => PAYPAL_API_URL,
                ]);
        
                $response = $client->post('notifications/webhooks', [
                    'headers' => [
                        'Authorization' => 'Bearer '.$access_token,
                        'Content-Type' => 'application/json',            
                    ],
                    'json' => [
                        'url' => site_url().'/wp-admin/admin-ajax.php?action=paypal_webhook_listener',
                        'event_types' => [
                            [
                                'name' => 'PAYMENT.SALE.COMPLETED',
                            ],                
                        ],
                    ],
                ]
                );
                $body = $response->getBody();
                $webhook_data = json_decode($body, true);
                if($webhook_data['id'])
                {
                    update_option('paypal_webhook_listnerID',$webhook_data['id']);
                }
    
        }
       
    }
    
}

add_action('wp_ajax_paypal_webhook_listener', 'paypal_webhook_listener');
add_action('wp_ajax_nopriv_paypal_webhook_listener', 'paypal_webhook_listener');

function paypal_webhook_listener() 
{
   
    $webhook_data = json_decode(file_get_contents('php://input'), true);

    
    if ($webhook_data['event_type'] == 'PAYMENT.SALE.COMPLETED') 
    {
        $subscription=get_subscription_by_ID_from_paypal($webhook_data['resource']['billing_agreement_id']);        
        
        $skip_first_payment_webhook=get_post_meta($subscription->ID,'skip_first_payment_webhook',true);

        if($skip_first_payment_webhook=='yes')
        {
            update_post_meta($subscription->ID,'skip_first_payment_webhook','no');
            http_response_code(200);
            exit;
        }        
        
        $wp_plan=get_post_meta($subscription->ID,'wp_plan_id',true);
        $total_paid=(int)get_post_meta($subscription->ID,'total_paid',true);

        $price_per_month=(int)get_post_meta($wp_plan,'price_per_month',true);
       
        //update total paid
        $total_paid=$price_per_month+$total_paid;
        
        //update total renewal times
        $total_renewal_times=(int)get_post_meta($subscription->ID,'total_renewal_times',true);
        $total_renewal_times+=1;                        
        update_post_meta($subscription->ID,'total_renewal_times',$total_renewal_times);
        
        //renew the number of posts
        $number_posts_of_plan=get_post_meta($wp_plan,'plan_number_posts',true);
        $transaction_fee=get_post_meta($wp_plan,'plan_transaction_fee_percent',true);
        update_post_meta($subscription->ID,'plan_remain_post',$number_posts_of_plan);                        

        //update last time renew
        update_post_meta($subscription->ID,'last_subscription_date',date('Y-m-d'));

        //update total paid
        update_post_meta($subscription->ID,'total_paid',$total_paid);

        //update this because maybe the plan will change this
        update_post_meta($subscription->ID,'transaction_fee',$transaction_fee);
          
        
            //send email when renew successfully
            $wp_plan_info=get_post($wp_plan);            
            $user_subscription=get_user_by('ID',$subscription->post_author);
             
            $subject= 'New subscription on '.get_bloginfo( 'name' );
            $link='<a href="'.site_url('mysubscription').'">here</a>';
            $message='The '.$wp_plan_info->post_title.' has automatically renew successfully';       
            $message.='<p> Please click '.$link.' for more information</p>';            

            subscription_send_mail($user_subscription->user_email,$subject,$message,array());
            //end send email

        
	}

    // Trả về phản hồi cho PayPal (có thể cần được thực hiện tùy thuộc vào yêu cầu của PayPal)
    http_response_code(200);
    exit;
	
}


function get_subscription_by_ID_from_paypal($subscription_ID_from_paypal)
{
   // $subscription_ID_from_paypal='I-GMAVY4NGFW4X';
    $args_subscription=array('numberposts'=>1,
                    'post_status' =>array('publish'),
                    'post_type'=>'custom_subscription',
                    'meta_query' => array(
                        array(
                            'key' => 'paypal_subscription_id',
                            'value' =>$subscription_ID_from_paypal,
                            'compare' => '=',
                        ),
                    ),
                );
    $subscriptions=get_posts($args_subscription);
    if($subscriptions)
    {
        foreach($subscriptions as $subscription)
        {
            $wp_subscription=get_post($subscription->ID);
        }
    }
    else
    {
        $wp_subscription=false;
    }
    return $wp_subscription;
}




//override tim of redirect user after verify email
add_filter('ae_confirm_user_time_out', 'setRedirectTimer',999,1);

function setRedirectTimer($time)
{
    $time=15000;
    return $time;
}

//redirect users after confirm email to pricing page
add_action('wp_footer', 'force_redirect_after_verify_email', 200);

function force_redirect_after_verify_email()
{
    if (isset($_GET['act']) && $_GET['act'] == "confirm" && $_GET['key']) 
    {
        ?>        
        <script type="text/javascript">
                        (function($) {
                            $(document).ready(function() {                        
                                setTimeout(function() {
                                    window.location.href = "<?php echo site_url('subscription'); ?>";
                                },2000);
                            });
                        })(jQuery);
                    </script>
        <?php
    }
}



//do_action('after_insert_mjob_order', $result, $request);

add_action('after_insert_mjob_order','set_commission_seller_by_package',999,2);

function set_commission_seller_by_package( $result, $request)
{   
    //update_post_meta($result->ID, 'fee_commission_value', $fee_commission_value);
    $seller_id=get_post_meta($result->ID,'seller_id',true);
    if($seller_id)
    {
        $user_profile_id=get_user_meta($seller_id,'user_profile_id',true);
        if($user_profile_id)
        {
            
            $subscription_id=get_post_meta($user_profile_id,'current_subscription_plan',true);
            
            $transaction_fee=get_post_meta($subscription_id,'transaction_fee',true);

            //get mjob_price
            $mjob_price=get_post_meta($result->ID,'mjob_price',true);

            //set seller commisson
            update_post_meta( $result->ID,'seller_commission',$transaction_fee );

            //set seller commission value
            $seller_commission_value=($mjob_price *  $transaction_fee ) / 100;
            update_post_meta( $result->ID,'seller_commission_value',$seller_commission_value );

            //set real money which the seller will receive
            $real_amount=$mjob_price - $seller_commission_value;
            update_post_meta( $result->ID,'real_amount',$real_amount );

        }
    }
}

function subscription_send_mail($email_address,$subject,$message_content)
{
    $email_instance= MJE_Mailing::get_instance();

    $header_template=et_email_template_header();
    $footer_template=et_email_template_footer();
               
    $message=$header_template;
    $message.=$message_content;           
    $message.=$footer_template;
        
    $email_instance->wp_mail($email_address,$subject,$message,array());
}