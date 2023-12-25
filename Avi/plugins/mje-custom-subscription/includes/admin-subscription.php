<?php
require 'guzzle/vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;


define('PAYPAL_API_URL','https://api-m.sandbox.paypal.com/v1/');

require('admin-manage-layout.php');

//function to get Paypal Access token
function get_Paypal_access_token()
{

    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);

    $custom_paypal_client_id=get_option('custom_paypal_client_id');
    $custom_paypal_secret=get_option('custom_paypal_secret');
    

    //try to get paypal access token first 
    //if it does not exist or exipre --> fetch new access token
    $paypal_access_token=get_option('paypal_access_token');
    $paypal_access_token_expire_time=get_option('paypal_access_token_expires_at',0);
    $current_time = time();

    if( $custom_paypal_client_id &&  $custom_paypal_secret )
    {
        if ($paypal_access_token_expire_time > $current_time && $paypal_access_token) 
        {
           return $paypal_access_token;
        } 
        else 
        {
           
            $response = $client->post('oauth2/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
              /*  'auth' => [
                    'AVKU8yOW0vA-hT6kI7uIlHSOqSRT2nfb-C_qtot59urXnrd2LWIfGwAOkUeFRkfnV1DoEsWi9M0-lssd',
                    'EGXmYtx_PTAT4-2JbzFxjHOt_tz93tAYoZBmH_KGJw0pyYv_fcxT3y9suDizTgpUzf_Teqt5RuMzZtZ8',
                ],*/
                'auth' => [
                    $custom_paypal_client_id,
                    $custom_paypal_secret,
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);
            
            $body = $response->getBody();
            $data = json_decode($body, true);         
            //$data['access_token'];
            if(!empty($data['access_token']))
            {
                $expires_at=time()+ $data['expires_in'];
                update_option('paypal_access_token',$data['access_token']);
                update_option('paypal_access_token_expires_at', $expires_at);
                $paypal_access_token=$data['access_token'];           
            }
            return $paypal_access_token;
        
        }
    }
    else
    {
        return false;
    }
   
   
}


//get subscription plans
function custom_get_list_plans()
{   
    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);
    
    $response = $client->get('billing/plans', [        
        'headers' => [
            'Authorization' => 'Bearer '.$access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Prefer' => 'return=representation',
        ],
        'query' =>[
            'page_size' => '20',
            'page' => '1',
            'total_required' => 'true',
        ],
    ]);
    
    $body = $response->getBody();
    $data = json_decode($body, true);
    $plan_array=[];
    $wp_plan_array=[];
    foreach($data['plans'] as $plan)
    {
        $wp_plan=find_plan_by_paypal_plan_id($plan['id']);
        if($wp_plan)
        {
            $plan['wp_plan_status']=$wp_plan->post_status;
            $plan_array[]=$plan;     
            $wp_plan_array[]=convert_subscription_plan($wp_plan);
        }              
    }

    //add free plans to wp_plan list because free plan is not submitted to Paypal
    $free_plan=get_all_free_plans();    

    $data['plans']=$plan_array;
    //$data['wp_plan']= $wp_plan_array;
    $data['wp_plan']=array_merge($wp_plan_array,$free_plan);
    return $data;
}

function find_plan_by_paypal_plan_id($paypal_plan_id)
{
    $args_plan=array('numberposts'=>1,
                    'post_status' =>array('publish','archive'),
                    'post_type'=>'subscription_plan',
                    'meta_query' => array(
                        array(
                            'key' => 'paypal_plan_id',
                            'value' => $paypal_plan_id,
                            'compare' => '=',
                        ),
                    ),
                );
    $plan_array=get_posts($args_plan);
    if($plan_array)
    {
        foreach($plan_array as $plan)
        {
            $wp_plan=get_post($plan->ID);
        }
    }
    else
    {
        $wp_plan=false;
    }
    return $wp_plan;
}


//ajax action to handle paypal info when submit form
add_action( 'wp_ajax_update_paypal_info', 'update_paypal_info_function' );

function update_paypal_info_function()
{
     if (!is_super_admin()) {        
        die('something went wrong');
    }
    
    if (!wp_verify_nonce($_POST['paypal_info_nonce'],'paypal_info_nonce')) {
        die('something went wrong');
    } 
    update_option('custom_paypal_client_id',$_POST['client_id']);
    update_option('custom_paypal_secret',$_POST['secret_key']);    
    $data['message']='Update Paypal information successful';
    $data['success']='true';
    wp_send_json($data);
    die();
}

function create_subscription_plan_process($access_token,$product_id,$subscription_plan_info)
{
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);    
    try
    {  
       $response = $client->post('billing/plans', [
                    'headers' => [
                        'Authorization' => 'Bearer '.$access_token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',            
                        'Prefer' => 'return=representation',
                    ],
                    'json' => [
                        "product_id" => $product_id,                                                
                        "name" => $subscription_plan_info['plan_name'],
                        "description" => $subscription_plan_info['plan_description'],                        
                        "status" => $subscription_plan_info['plan_status'],
                        "billing_cycles" => [
                            [
                                "frequency" => [
                                    "interval_unit" => "MONTH",
                                    "interval_count" => "1",
                                ],
                                "tenure_type" => $subscription_plan_info['plan_tenure_type'],
                                "sequence" => $subscription_plan_info['plan_sequence'],
                                "total_cycles" => "0",
                                "pricing_scheme" => [
                                    "fixed_price" => [
                                        "value" => (string)$subscription_plan_info['plan_price'],
                                        "currency_code" => $subscription_plan_info['plan_currency'],
                                    ]
                                ]
                            ],                
                        ],
                        "payment_preferences" => [
                            "service_type" => "PREPAID",
                            "auto_bill_outstanding" => "true",
                            "setup_fee" => [
                                "value" => "0",
                                "currency_code" =>  $subscription_plan_info['plan_currency'],
                            ],
                            "setup_fee_failure_action" => "CONTINUE",
                            "payment_failure_threshold" => "3"
                        ],       
                        
                    ],    
                ]);   
        $body = $response->getBody();
        $subscription_data = json_decode($body, true);
        return $subscription_data;
    }
    catch(ClientException $e)
    {
        echo Psr7\Message::toString($e->getRequest());
        echo Psr7\Message::toString($e->getResponse());
    }

}

//ajax to handle data when submit plans for editing
add_action( 'wp_ajax_edit_subscription_plans_paypal', 'edit_subscription_plans_paypal_function');

function edit_subscription_plans_paypal_function()
{
    if (!is_super_admin()) {        
        die('something went wrong');
    }
    
    if (!wp_verify_nonce($_POST['create_plan_paypal_nonce'],'create_plan_paypal_nonce')) {
        die('something went wrong');
    } 
    $check_price=(int)$_POST['plan_price'];
    extract($_POST);
    $new_plan_updated=array('ID'=>$wp_plan_id_edit,
                            'post_title'=>$plan_name,                            
                            'post_content' => $plan_description,    
                            );
    $paypal_plan_id=get_post_meta($wp_plan_id_edit,'paypal_plan_id',true);
    //update free plan
    if($check_price==0 || $check_price=='0')
    {   

        $update_result=wp_update_post($new_plan_updated,true);
        if(!is_wp_error($update_result))
        {
            update_post_meta($wp_plan_id_edit,'price_per_month',$plan_price);
            update_post_meta($wp_plan_id_edit,'plan_currency',$plan_currency);
            update_post_meta($wp_plan_id_edit,'plan_short_description',$plan_description);
            update_post_meta($wp_plan_id_edit,'plan_subtitle',$plan_subtitle);
            update_post_meta($wp_plan_id_edit,'plan_transaction_fee_percent',$transaction_fee);            
            update_post_meta($wp_plan_id_edit,'plan_number_posts',$number_posts);    
    
            for($advertise_text = 1; $advertise_text <= 6; $advertise_text++)
            {
                $advertise_text_display=$_POST['title_advertisement'.$advertise_text] ? $_POST['title_advertisement'.$advertise_text] : 'false';
                update_post_meta($wp_plan_id_edit,'title_advertisement'.$advertise_text,$advertise_text_display);
            }
    
            $data['message']='Updated plan successfully';
            $data['success']='true';
            $data['redirect_url']=site_url('/manage-subscription?task=subscriptionmanage');
        }
        else
        {
            $data['message']='Failed to update plan..Please refresh ';
            $data['success']='false';
        }
       
        wp_send_json($data);
        die();
    }

    //update paid plan
    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);

    $response_update = $client->patch('billing/plans/'.$paypal_plan_id, [
        'headers' => [
            'Authorization' => 'Bearer '.$access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',                        
        ],
        'json' => [
            [
                'op' => 'replace',
                'path' => '/name',
                'value' => $plan_name,
            ],
            [
                'op' => 'replace',
                'path' => '/description',
                'value' => $plan_description,
            ],
        ],
    
    ]);

    $response_update_pricing = $client->post('billing/plans/'.$paypal_plan_id.'/update-pricing-schemes', [
        'headers' => [
            'Authorization' => 'Bearer '.$access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',                        
        ],
        'json' =>[
            
            'pricing_schemes' => [
                [
                    'billing_cycle_sequence' => 1,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => $plan_price,
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ]

        ],
    
    ]);

    update_post_meta($wp_plan_id_edit,'price_per_month',$plan_price);
    update_post_meta($wp_plan_id_edit,'plan_currency',$plan_currency);
    update_post_meta($wp_plan_id_edit,'plan_short_description',$plan_description);
    update_post_meta($wp_plan_id_edit,'plan_subtitle',$plan_subtitle);
    update_post_meta($wp_plan_id_edit,'plan_transaction_fee_percent',$transaction_fee);            
    update_post_meta($wp_plan_id_edit,'plan_number_posts',$number_posts);    

    for($advertise_text = 1; $advertise_text <= 6; $advertise_text++)
    {
        $advertise_text_display=$_POST['title_advertisement'.$advertise_text] ? $_POST['title_advertisement'.$advertise_text] : 'false';
        update_post_meta($wp_plan_id_edit,'title_advertisement'.$advertise_text,$advertise_text_display);
    }

    $data['message']='Updated plan successfully';
    $data['success']='true';
    $data['redirect_url']=site_url('/manage-subscription?task=subscriptionmanage');

     
    wp_send_json($data);
    die();

}


// ajax to handle data when submit plans for creating
add_action( 'wp_ajax_create_subscription_plans_paypal', 'create_subscription_plans_paypal_function');

function create_subscription_plans_paypal_function()
{    
    if (!is_super_admin()) {        
        die('something went wrong');
    }
    
    if (!wp_verify_nonce($_POST['create_plan_paypal_nonce'],'create_plan_paypal_nonce')) {
        die('something went wrong');
    } 

    //handle for free plan ( price =0)
    $check_price=(int)$_POST['plan_price'];
    if($check_price==0 || $check_price=='0')
    {
        $wp_subscription_plan=array(
            'post_title'   => $_POST['plan_name'],
            'post_type' => 'subscription_plan',
            'post_status'  => 'publish',
            'post_author'  => (int)get_current_user_id(),  
            'post_content'   => $_POST['plan_description'],              
        );  
        $wp_subscription_plan_id=wp_insert_post($wp_subscription_plan,true);
        if(!empty($wp_subscription_plan_id) && !is_wp_error($wp_subscription_plan_id))
        {
            $paypal_id_random=uniqid(rand(1,30));
            update_post_meta($wp_subscription_plan_id,'paypal_plan_id',$paypal_id_random);
            update_post_meta($wp_subscription_plan_id,'price_per_month',0);
            update_post_meta($wp_subscription_plan_id,'plan_currency',$_POST['plan_currency']);
            update_post_meta($wp_subscription_plan_id,'plan_short_description',$_POST['plan_description']);
            update_post_meta($wp_subscription_plan_id,'plan_subtitle',$_POST['plan_subtitle']);
            update_post_meta($wp_subscription_plan_id,'plan_transaction_fee_percent',$_POST['transaction_fee']);            
            update_post_meta($wp_subscription_plan_id,'plan_number_posts',$_POST['number_posts']);
            update_post_meta($wp_subscription_plan_id,'is_free_plan','true');

            for($advertise_text = 1; $advertise_text <= 6; $advertise_text++)
            {
                $advertise_text_display=$_POST['title_advertisement'.$advertise_text] ? $_POST['title_advertisement'.$advertise_text] : 'false';
                update_post_meta($wp_subscription_plan_id,'title_advertisement'.$advertise_text,$advertise_text_display);
            }
            $data['message']='Create plan successfully';
            $data['success']='true';
            $data['redirect_url']=site_url('/manage-subscription?task=subscriptionmanage');
        }
        else
        {
            $data['message']='Something went wrong when create subscription plan';
            $data['success']='false';
        }
      
        wp_send_json($data);
        die();
    }

    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);
    
    $response = $client->post('catalogs/products', [
        'headers' => [
            'Authorization' => 'Bearer '.$access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',            
            'Prefer' => 'return=representation',
        ],
        'json' => [
            "name" => $_POST['plan_name']. ' Product for plan',
            "description" => $_POST['plan_description'].' Description Product for plan',
            "type" => $_POST['product_type'],
            "category" => $_POST['product_category'],  
        ],
    
    ]);
    
    $body = $response->getBody();
    $data = json_decode($body, true);
    if(!empty($data['id']))
    {
        $subscription_plan_info=array('plan_name' => $_POST['plan_name'],
                                      'plan_price'=>$_POST['plan_price'],
                                     'plan_description'=>$_POST['plan_description'],
                                     'plan_currency'=>$_POST['plan_currency'],
                                     'plan_tenure_type'=>$_POST['plan_tenure_type'],
                                     'plan_sequence'=>$_POST['plan_sequence'],
                                     'plan_status'=>$_POST['plan_status'],
                                    );
        $subscription_plan_created=create_subscription_plan_process($access_token,$data['id'], $subscription_plan_info);
        if(!empty($subscription_plan_created['id']))
        {
            //comment out because this can be only used for debug
          //  $data['subscription_info']= $subscription_plan_created; 
            $wp_subscription_plan=array(
                     'post_title'   => $_POST['plan_name'],
                     'post_type' => 'subscription_plan',
                     'post_status'  => 'publish',
                     'post_author'  => (int)get_current_user_id(),  
                     'post_content'   => $_POST['plan_description'],              
            );  
         

            $wp_subscription_plan_id=wp_insert_post($wp_subscription_plan,true);
       /*     if(is_wp_error($wp_subscription_plan_id))
            {
                update_user_meta(get_current_user_id(),'loi',$wp_subscription_plan_id->get_error_message());
                $data['message']=$wp_subscription_plan_id->get_error_message();
                $data['success']='true';
                die();
            } */
            if(!empty($wp_subscription_plan_id) && !is_wp_error($wp_subscription_plan_id))
            {
                update_post_meta($wp_subscription_plan_id,'paypal_plan_id',$subscription_plan_created['id']);
                update_post_meta($wp_subscription_plan_id,'price_per_month',$_POST['plan_price']);
                update_post_meta($wp_subscription_plan_id,'plan_currency',$_POST['plan_currency']);
                update_post_meta($wp_subscription_plan_id,'plan_short_description',$_POST['plan_description']);
                update_post_meta($wp_subscription_plan_id,'plan_subtitle',$_POST['plan_subtitle']);
                update_post_meta($wp_subscription_plan_id,'plan_transaction_fee_percent',$_POST['transaction_fee']);
                //update_post_meta($wp_subscription_plan_id,'plan_duration_post',$_POST['plan_duration']);
                update_post_meta($wp_subscription_plan_id,'plan_number_posts',$_POST['number_posts']);

                for($advertise_text = 1; $advertise_text <= 6; $advertise_text++)
                {
                    $advertise_text_display=$_POST['title_advertisement'.$advertise_text] ? $_POST['title_advertisement'.$advertise_text] : 'false';
                    update_post_meta($wp_subscription_plan_id,'title_advertisement'.$advertise_text,$advertise_text_display);
                }
            }

            $data['message']='Create plan successfully';
            $data['success']='true';
            $data['redirect_url']=site_url('/manage-subscription?task=subscriptionmanage');
        }       
        else
        {
            $data['message']='Something went wrong when create subscription plan';
            $data['success']='false';
        }    
    }
    else
    {
        $data['message']='Something went wrong please check your API';
        $data['success']='false';
    }    
    wp_send_json($data);
    die();
}

//get subscription info from paypal
function get_subscription_id($subscription_id)
{
    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);
    $response = $client->get('billing/subscriptions/'.$subscription_id, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);
    $body = $response->getBody();
    $data = json_decode($body, true);
    return $data;

}

//get all subscription from database

function get_all_subscription()
{
    global $post;
    $args=array('post_type' => 'custom_subscription',
                'posts_per_page' => -1,   
                'order'=> 'DESC',
                'orderby'=> 'date',                 
                );
    $query = new WP_Query($args);
    $data = array();
    if ($query->have_posts()) 
    {           
        while ($query->have_posts()) 
        {
            $query->the_post();
            $status=get_post_meta($post->ID,'subscription_status',true);
            $subscriber=get_user_by('ID',$post->post_author);
            $plan_name=get_post_meta($post->ID,'plan_name',true);
            $paypal_plan_id=get_post_meta($post->ID,'paypal_plan_id',true);
            $plan_price=get_post_meta($post->ID,'plan_price',true);
            $subscription_date=get_post_meta($post->ID,'subscription_date',true);
            $wp_plan_id=get_post_meta($post->ID,'wp_plan_id',true);
            $plan_remain_post=get_post_meta($post->ID,'plan_remain_post',true);
            $paypal_subscription_id=get_post_meta($post->ID,'paypal_subscription_id',true);
            $total_paid=get_post_meta($post->ID,'total_paid',true);
            $last_subscription_date=get_post_meta($post->ID,'last_subscription_date',true);
            if(!$total_paid)
            {
                $total_paid=0;
            }
            $total_renewal_times=get_post_meta($post->ID,'total_renewal_times',true);
            $userInfo='Email: '.$subscriber->user_email.' | Username: '.$subscriber->user_login.' | Display name: '.$subscriber->display_name;
            if($status!=='Active')
            {
                $status='Cancelled';
            }
            $data[]=array(                
                'wp_id'=>$wp_plan_id,
                'status'=>$status,
                'plan_name'=>$plan_name,
                'subscriber'=>$subscriber->display_name,
                'date'=>$subscription_date,
                'price'=>$plan_price,
                'paypalID'=>$paypal_plan_id,
                'remaining_post'=>$plan_remain_post,
                'paypalSubscriptionID'=>$paypal_subscription_id,
                'userInfo'=>$userInfo,
                'total_paid'=>$total_paid.'$',
                'total_renewal_times'=>$total_renewal_times,
                'last_subscription_date'=>$last_subscription_date,
                );
        } 
       
        
    }                         
    wp_reset_postdata();           
    return $data;
}

add_action('wp_head','transfer_subscription_to_JS');

function transfer_subscription_to_JS()
{
    global $post;
    if(isset($post) && !empty($post))
	{
        $template_page=get_post_meta($post->ID,'_wp_page_template',true);
	
        if($template_page=='custom-manage-subscription.php')
		{
            $subscription_list=get_all_subscription();    
            echo '<script type="text/template" id="subscriptionList">' . json_encode($subscription_list) . '</script>';     
        }
        
    }   
    ?>    
    <?php
    
}


add_action( 'wp_ajax_setStatusPlan', 'setStatusPlan_function');

function setStatusPlan_function()
{
    if (!is_super_admin()) {        
        die('something went wrong');
    }
    extract($_POST);
    $wp_plan=find_plan_by_paypal_plan_id($paypalPlanID);
    $update_post=array('ID'=> $wp_plan->ID,'post_status'=>$setStatus);
    $update_result=wp_update_post($update_post,true);
    if(!is_wp_error($update_result))
    {
        $data['message']='Update successfully';
        $data['success']='true';
    }
    else
    {
        $data['message']='Something went wrong!';
        $data['success']='false';
    }
    wp_send_json($data);
    die();

}

add_action( 'wp_ajax_deleteWPPlan', 'deleteWPPlan_function');

function deleteWPPlan_function()
{
    if (!is_super_admin()) {        
        die('something went wrong');
    }
    extract($_POST);
    $wp_plan=find_plan_by_paypal_plan_id($paypalPlanID);
    $delete_result=wp_delete_post($wp_plan->ID,true);
    if(!is_wp_error($delete_result))
    {
        $data['message']='Delete successfully';
        $data['success']='true';
    }
    else
    {
        $data['message']='Something went wrong!';
        $data['success']='false';
    }
    wp_send_json($data);
    die();
}

//get free plans

function get_all_free_plans()
{
    global $post;
    $args=array('post_type' => 'subscription_plan',
                'posts_per_page' => -1,   
                'order'=> 'DESC',
                'orderby'=> 'date', 
                'meta_query'=> array(
                    array(
                        'key'   => 'is_free_plan',
                        'value' => 'true',
                    ),    
                    ),            
                );
    $query = new WP_Query($args);
    $data = array();
    $wp_plan_array=array();
    if ($query->have_posts()) 
    {           
        while ($query->have_posts()) 
        {
            $query->the_post();
            //$wp_plan=find_plan_by_paypal_plan_id($post->ID);
            $wp_plan=get_post($post->ID);
            if($wp_plan)
            {                                  
                $wp_plan_array[]=convert_subscription_plan($wp_plan);
            }              
        }      
        wp_reset_postdata();  
       
    }    
    return $wp_plan_array;               
 
}


add_action( 'wp_ajax_getPaymentInfoPaypal', 'getPaymentInfoPaypal_function');

function getPaymentInfoPaypal_function()
{
    if (!is_super_admin()) {        
        die('something went wrong');
    }
    extract($_POST);
    
    $subscription_info=get_subscription_id($subscriptionID);
   // $transaction_list=list_transaction_for_subscription($subscriptionID);
    
    $data['subscription_info']=$subscription_info;
   // $data['transaction_list']= $transaction_list;
    
    wp_send_json($data);
    die();
}

function list_transaction_for_subscription($subscriptionID)
{
    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);
    $wp_subscription=get_subscription_by_ID_from_paypal($subscriptionID);
    $subscription_date=get_post_meta($wp_subscription->ID,'subscription_date',true);

    $response = $client->get('billing/subscriptions/'.$subscriptionID.'/transactions', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
        'query' => [
            'start_time' =>  $subscription_date.'T00:00:00.000Z',
            'end_time' => date('Y-m-d').'T23:59:59.999Z',
        ],
    ]);
    $body = $response->getBody();
    $data = json_decode($body, true);
    return $data;

}


//get products
//will delete in the future or comment out
function custom_get_list_products()
{   
    $access_token=get_Paypal_access_token();
    $client = new Client([
        'base_uri' => PAYPAL_API_URL,
    ]);
    
    $response = $client->get('catalogs/products', [
        'query' =>[
            'page_size' => '20',
            'page' => '1',
            'total_required' => 'true',
        ],
        'headers' => [
            'Authorization' => 'Bearer '.$access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',            
        ],
    ]);
    
    $body = $response->getBody();
    $data = json_decode($body, true);
    return $data;
}

