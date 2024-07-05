<?php
add_action('init', 'AffInit');
function AffInit() { 
        session_start();	
        if(isset($_GET['ref']))			
        {
            $ref=$_GET['ref'];
            $ref_user=get_user_by('login',$ref);
            if( $ref_user )
            {
                $_SESSION['refferal']=$ref_user->ID;
            }		
        }
			      

}

add_action('wp_enqueue_scripts', 'override_paymentjs',99);
function override_paymentjs()
{    

    if (is_singular('mjob_post') || is_page_template('page-process-payment.php')) {
        wp_deregister_script('single-mjob');
        wp_enqueue_script('single-mjob', get_stylesheet_directory_uri() . '/assets/js/single-mjob.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front',
            ), ET_VERSION, true);

    }

    if (is_page_template('page-order.php') || is_page_template('page-process-payment.php')) {
        wp_deregister_script('order-mjob');
   
        wp_enqueue_script('order-mjob', get_stylesheet_directory_uri() . '/assets/js/payment.js', array(
                'jquery',
                'underscore',
                'backbone',
                'appengine',
                'front',
                ), ET_VERSION, true);
    
    }


}


add_action('after_insert_mjob_order','insert_affiliate_info',999,2);

function insert_affiliate_info($result, $request)
{
    if($request['affiliate_username'])
    {
              //check if affiliate user
              $subtotal = 0;
              if( isset( $request['custom_offer_id'] ) ) {
                  $subtotal = get_post_meta( $request['custom_offer_id'], 'custom_offer_budget', true );
              } else {
                  $subtotal = get_post_meta( $request['post_parent'], 'et_budget', true );
              }

              $user_affiliate=get_user_by('login',$request['affiliate_username']);
              if($user_affiliate)
              {
                  //check affiliate
                  global $wpdb; 
                  $affiliate_visit_id = ($request['affiliate_visit_id']) ? $request['affiliate_visit_id'] : 0;                             
                  $affiliate_info = $wpdb->get_row( "SELECT * FROM wp_affiliate_wp_affiliates WHERE user_id = $user_affiliate->ID", ARRAY_A );
                  if($affiliate_info)
                  {
                      $affsettings = get_option('affwp_settings');
                      //$affsettings['referral_rate'];
                      //$affsettings['currency'];
                      $amountAff=mje_get_price_mjob_order_for_buyer($subtotal, $request) * $affsettings['referral_rate'] / 100;
                      
                      $table='wp_affiliate_wp_referrals';
                      $data=array(
                          'affiliate_id'=>$affiliate_info['affiliate_id'],
                          'customer_id'=>get_current_user_id(),
                          'description'=>'MjE Checkout',
                          'status' => 'unpaid',
                          'amount'=>$amountAff,
                          'currency'=>$affsettings['currency'],
                          'context'=>'mje_checkout',
                          'payout_id'=>0,
                          'date'=>date('Y/m/d h:i:s'),
                          'reference' => 'mjob-order-'.$result->ID,
                      );
                      $format=array('%d','%d','%s','%s','%f','%s','%s','%d','%s','%s','%d');
                      if($wpdb->insert($table,$data,$format))
                      {
                          $referral_id= $wpdb->insert_id;
                          $visit_table='wp_affiliate_wp_visits';
                          $data = array(
                                  'referral_id' => $referral_id,
                                  
                              );
                          $where = array(
                                  'visit_id' => $affiliate_visit_id,
                              );
                          $wpdb->update($visit_table, $data, $where);

                       /*   $referral_meta_table='wp_affiliate_wp_referralmeta';
                          $referral_meta_format_insert=array('%d','%d','%s','%s');

                          $referral_meta_array=array(
                                    'referral_id'=>$referral_id,

                          );*/
                      }
                  }
                //  update_user_meta($user_affiliate->ID,'error',$wpdb->last_error);
              }
    }
}


function get_user_by_referralID($referral_id)
{
    global $wpdb;     
    $affiliate_table='wp_affiliate_wp_affiliates';
    $referral_table='wp_affiliate_wp_referrals';

    $query_ref = "SELECT * FROM $referral_table WHERE referral_id = $referral_id";
    $ref_info = $wpdb->get_row($query_ref);

    if($ref_info)
    {
        $aff_id=$ref_info->affiliate_id;
        $aff_user['pay_amount']=$ref_info->amount;
        $aff_user['reference']=$ref_info->reference;
    }

    $query_aff = "SELECT * FROM $affiliate_table WHERE affiliate_id = $aff_id";
    $aff_info = $wpdb->get_row($query_aff);
    $aff_user['id']=$aff_info->user_id;
    
    return $aff_user;
}


add_action('affwp_referrals_do_bulk_action_mark_as_paid','custom_action_mark_paid',999,1);

function custom_action_mark_paid($id)
{  
    $aff_info=get_user_by_referralID($id);
    if($aff_info)
    {        
        $wallet       = AE_WalletAction()->getUserWallet($aff_info['id']);    
        $wallet->balance += $aff_info['pay_amount'];
        AE_WalletAction()->setUserWallet($aff_info['id'], $wallet);

        insert_notification_for_payment_action('paid',$aff_info);
    }    

}

add_action('affwp_referrals_do_bulk_action_mark_as_unpaid','custom_action_mark_unpaid',999,1);

function custom_action_mark_unpaid($id)
{
    $aff_info=get_user_by_referralID($id);
    if($aff_info)
    {        
        $wallet       = AE_WalletAction()->getUserWallet($aff_info['id']);    
        $wallet->balance -= $aff_info['pay_amount'];
        AE_WalletAction()->setUserWallet($aff_info['id'], $wallet);

        insert_notification_for_payment_action('unpaid',$aff_info);
    }    
}

//insert notification after mark paid or unpaid

function insert_notification_for_payment_action($payment_type,$aff_info)
{
    $notification_action = MJE_Notification_Action::get_instance();

    $code = 'type=affiliate_'.$payment_type;
    
    $code .= '&affiliate_reference=' . $aff_info['reference'];
    $noti_created=$notification_action->create( $code, $aff_info['id'] );
    update_post_meta($noti_created,'reference',$aff_info['reference']);
}


//build new notification type

add_action('mje_other_type_notification','add_new_notification_type_affiliate',9999,1);

function add_new_notification_type_affiliate($post)
{
    $custom_code = trim( $post->post_content );
    $custom_code = str_ireplace( '&amp;', '&', $post->post_content );
    $custom_code = strip_tags( $custom_code );

        // Convert string to variables
        parse_str( $custom_code , $custom_result);
        // version 1.3.6
        $type = isset($custom_result['type']) ? $custom_result['type'] : '';
        $reference=get_post_meta($post->ID,'reference',true);
        if($type=='affiliate_paid')
        {
            $post->noti_content=sprintf("Admin has released affiliate payment <strong> %s </strong>.",$reference);
        }
        if($type=='affiliate_unpaid')
        {
            $post->noti_content=sprintf("Admin has reverted affiliate payment <strong> %s </strong>.",$reference);
        }

        $post->noti_link=site_url('affiliate-area/?tab=referrals');
        
}