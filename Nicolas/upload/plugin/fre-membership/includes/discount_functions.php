<?php
add_action('wp_enqueue_scripts', 'add_custom_discount_js');
function add_custom_discount_js()
{    

    wp_enqueue_script('custom-discount-js', get_stylesheet_directory_uri().'/assets/js/custom-discount.js', array(
                'front'
            ), ET_VERSION, true);
}



function add_custom_discount_manager()
{
	     $args = array(
	     	  'public' => true,
	     	  'show_ui' =>true,
	     	  'show_in_menu'=>true,
            'labels' => array(
                'name' => __("Discount", 'enginethemes'),
                'singular_name' => __('Discounts', 'enginethemes'),
                'add_new' => __('Add New', 'enginethemes'),
                'add_new_item' => __('Add New Discount', 'enginethemes'),
                'edit_item' => __('Edit Discount', 'enginethemes'),
                'new_item' => __('New Discount', 'enginethemes'),
                'all_items' => __('All Discounts', 'enginethemes'),
                'view_item' => __('View Discounts', 'enginethemes'),
                'search_items' => __('Search Discounts', 'enginethemes'),
                'not_found' => __('No Discounts found', 'enginethemes'),
                'not_found_in_trash' => __('No Discounts found in Trash', 'enginethemes'),
                'parent_item_colon' => '',
                'menu_name' => __('Discounts', 'enginethemes')
            ),

            'menu_icon' => 'dashicons-tickets-alt'
        );
        register_post_type('discountfre',$args);
}
add_action('init','add_custom_discount_manager');

function discount_meta_box()
{
 add_meta_box( 'discount-box', 'Discount code Options', 'discount_info_output', 'discountfre' );
}
add_action( 'add_meta_boxes', 'discount_meta_box' );

function discount_info_output($post)
{
	
	$discount_code_name=get_post_meta($post->ID,'discount_code',true) ? get_post_meta($post->ID,'discount_code',true) : wp_generate_password(6,false,false);
	//$discount_code_status=get_post_meta($post->ID,'discount_status',true) ? get_post_meta($post->ID,'discount_status',true) : '';
	$discount_code_used=get_post_meta($post->ID,'used',true) ? get_post_meta($post->ID,'used',true) : 'available';
	$discount_percent=get_post_meta($post->ID,'discount_percent',true) ? get_post_meta($post->ID,'discount_percent',true) : 0;
	$discount_code_user_id=get_post_meta($post->ID,'user_id_discount',true);
	echo  '<h3>Discount Code ( Generate automatically )</h3>';	
	echo '<input type="text" id="discount_code_name" readonly="true" name="discount_code_name" value="'.$discount_code_name.'" placeholder="This is generated automatically">';

	echo  '<h3>Discount %</h3>';	
	echo '<input type="number" id="discount_percent" name="discount_percent" value="'.$discount_percent.'" placeholder="% Discount">';

	echo '<h3>Status : <span>'.$discount_code_used.'</span></h3>';	

	echo '<h3>Assign code for the employers:</h3>';
	    $args = array();
           $default = array(    
            'role__in' => array('employer','freelancer'),
              'number' =>-1,           

           );
          $args = wp_parse_args( $args, $default );
           $user_list_query = new WP_User_Query($args);
	echo '<select id="user_id_discount" name="user_id_discount">';
		foreach($user_list_query->get_results() as $user_item)
		{
			if($discount_code_user_id==$user_item->ID)
			{
				echo '<option selected="selected" value="'.$user_item->ID.'">';
					echo $user_item->display_name;
				echo '</option>';
			}
			else
			{
				echo '<option value="'.$user_item->ID.'">';
					echo $user_item->display_name;
				echo '</option>';
			}
			
		}
	echo '</select>';

	//echo '<h3>Status :</h3>';
	//$status_array=array('active','disabled');

	/*echo '<select id="discount_status" name="discount_status">';
		//echo '<option value="active">Active</option>';
		//echo '<option value="disabled">Disabled</option>';
	foreach($status_array as $status_item)
	{
		if($discount_code_status == $status_item)
		{
			echo '<option value="'.$status_item.'">'.$status_item.'</option>';
		}
		else
		{
			echo '<option value="active">Active</option>';
			echo '<option value="disabled">Disabled</option>';
		}
	}
	echo '</select>'; */
	
}

function generate_code_automatically( $post_id,$post,$update)
{
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
		update_post_meta($post_id,'discount_code',$_POST['discount_code_name']);		
		update_post_meta($post_id,'discount_percent',$_POST['discount_percent']);
		update_post_meta($post_id,'user_id_discount',$_POST['user_id_discount']);
		update_post_meta($post_id,'used','available');


	
		if(isset($_POST['user_id_discount']))
		{
			//send an email for the user
			$mailer_custom=AE_Mailing::get_instance();	
			$profile_name=get_userdata($_POST['user_id_discount']);
			$content='<h3>Admin has sent you a '.$_POST['discount_percent'].'% discount code : '.$_POST['discount_code_name'].'</h3>';
			$mailer_custom->wp_mail( $profile_name->user_email,
									'Membership Discount Code',$content);

			//send notification for user
			$content_noti='type=discount_code_noti&amp;sender='.$post->post_author.'&amp;disount_code='.$_POST['discount_code_name'].'&amp;';	
			$title='Admin has sent you a discount code';
		  	$notification = array(
                'post_content' => $content_noti,
                'post_excerpt' => $content_noti,
                'post_status' => 'publish',
                'post_author' =>$_POST['user_id_discount'],
                'post_type' => 'notify',
                'post_title' => $title,                
            );
			$discount_noti = Fre_Notification::getInstance();

        	$noti = $discount_noti->insert($notification);
        	if($noti)
        	{
        		update_post_meta($noti,'discount_code_noti',$_POST['discount_code_name']);
        		update_post_meta($noti,'discount_percent_noti',$_POST['discount_percent']);
        	}
        	
		}

		
		
}

add_action('publish_discountfre','generate_code_automatically',10,3);

//add_action( 'save_post_discount', 'generate_code_automatically', 10, 3 );






add_action('edit_post_discountfre','update_discountfre',10,4);

function update_discountfre($post_id, $post)
{
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	update_post_meta($post_id,'discount_percent',$_POST['discount_percent']);
}

add_filter( 'template_include', 'discount_change_page_template', 99 );
function discount_change_page_template($template)
{
     global $post;
    $custom_template_slug   = 'discount-template.php';
    $page_template_slug     = get_page_template_slug( $post->ID );

    if( $page_template_slug == $custom_template_slug ){
       return FRE_MEMBERSHIP_PATH .'/'.$custom_template_slug;
    }

    return $template;

}


function add_page_for_discountfre()
{
    global  $wp_query, $post;
    $args=array();
      $default = array(
          'post_type' => 'page',
          'post_status' => array('published'),
          //'numberposts'=>1,
           'posts_per_page'=>1,   
          'post_title' =>'Discount Code List',
              
    );
    $args = wp_parse_args( $args, $default );
    $discount_page = new WP_Query($args);
$PageGuid = site_url() . "/discount-list";

      if($discount_page->have_posts())
      {
        $discountfre_page = array( 'post_title'     => 'Discount Code List',
                         'post_type'      => 'page',
                         'post_name'      => 'discount-list',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $discountfre_page_id=wp_insert_post( $discountfre_page, FALSE ); 
      update_post_meta($discountfre_page_id,'_wp_page_template','discount-template.php');
      }

}

add_action( 'init', 'add_page_for_discountfre' );

add_action( 'wp_ajax_check_discount_code', 'check_discount_code_init' );

function check_discount_code_init() {
 
 global $post;
 	if(!is_user_logged_in())
 	{
 		$data['message']='something went wrong';
 		wp_send_json_success( $data);
 		die();
 	}
 	

		$args=array();
  		$default = array(
  			  'post_type' => 'discountfre',
  			  'post_status' => array('published'),
  			  //'numberposts'=>1,
  			   'posts_per_page'=>1,   
  			  'meta_key' =>'discount_code',
  			  'meta_query' =>array(
                            'relation' => 'AND', 
                            array(
                                    'key'=> 'discount_code',
                                    'value' => $_POST['discount_code_name'],
                                    'compare' => '='
                                    ),
                         /*   'relation' => 'AND', 
                                array(
                                    'key' => 'user_id_discount',
                                    'value' => $_POST['user_id_discount'],
                                    'compare' => '='
                                ),*/
                             'relation' => 'AND', 
                                array(
                                    'key' => 'used',
                                    'value' => 'available',
                                    'compare' => '='
                                ),
                        ),            
  	);
  	$args = wp_parse_args( $args, $default );
  	$discount_collection = new WP_Query($args);   	
  	if($discount_collection->have_posts())
  	{

	   	while($discount_collection->have_posts())	  	
	   {
	   		$discount_collection->the_post();
	   		$result['discount_percent']=get_post_meta($post->ID,'discount_percent',true);
	   		$result['discount_code_id']=$post->ID;
	   }	
   			$result['discount_code_name']=$_POST['discount_code_name']; 
   		    $result['check']='ok';

          $user_get_discount=get_user_by('id',$_POST['user_id_discount']);
          if(in_array('employer', $user_get_discount->roles))
          {
            $pack_type='pack';
          }
          if(in_array('freelancer', $user_get_discount->roles))
          {
            $pack_type='bid_plan';
          }

   		    $pack=membership_get_pack($_POST['discount_sku'],$pack_type);

          //neu la 100% thi tinh kieu khac
          if($result['discount_percent']==100)
          {
             $result['discount_price_raw']=$pack->et_price;
             $result['decrease_price']=fre_price_format($pack->et_price);
            
              $result['discount_price']=fre_price_format(0);
          }
          else
          {
              $result['decrease_price']=fre_price_format(($result['discount_percent']*$pack->et_price)/100);
            $result['discount_price']=fre_price_format($pack->et_price - $result['decrease_price']);
            $result['discount_price_raw']=$pack->et_price - $result['decrease_price'];
          }
   		  
    }   
    else
    {
    	$result['check']='no';
    }
    
    wp_send_json_success($result);
 
   	  die();
}

add_action('after_save_membership','custom_after_save_membership_function_discount',15,0);

function custom_after_save_membership_function_discount()
{
  $custom_current_user=wp_get_current_user();
  $user_profile_id=get_user_meta($custom_current_user->ID,'user_profile_id',true);
  if(in_array('freelancer', $custom_current_user->roles))
  {
            if($user_profile_id)
  {
    $discount_first_time=get_post_meta($user_profile_id,'discount_first_time',true);
    if(!$discount_first_time)
    {
      $discount_info=array(
                          'post_type'=>'discountfre',
                          'post_status'=>'publish',
                          'post_author' =>$custom_current_user->ID,
                          'post_title'=>'Discount First Time - '.$custom_current_user->display_name,

                          );
      $discount_info_id=wp_insert_post($discount_info);
      $Percent_discount_100=wp_generate_password(6,false,false);
      $discount_percent=100;
    update_post_meta($discount_info_id,'discount_code',$Percent_discount_100);    
    update_post_meta($discount_info_id,'discount_percent',100);
    update_post_meta($discount_info_id,'user_id_discount',$custom_current_user->ID);
    update_post_meta($discount_info_id,'used','available');

    //update meta for profile de lan sau khoi nhan discount nua
    update_post_meta($user_profile_id,'discount_first_time','given');  

      $mailer_custom=AE_Mailing::get_instance();  
     // $profile_name=get_userdata($_POST['user_id_discount']);
      $content='<h3>Thanks for your first purchase, Admin has sent you a '.$discount_percent.'% discount code : '.$Percent_discount_100.'</h3>';
      $mailer_custom->wp_mail( $custom_current_user->user_email,
                  'Membership Discount Code',$content);

      //send notification for user
      $content_noti='type=discount_code_noti&amp;sender='.$custom_current_user->ID.'&amp;disount_code='.$Percent_discount_100.'&amp;'; 
      $title='Admin has sent you a discount code';
        $notification = array(
                'post_content' => $content_noti,
                'post_excerpt' => $content_noti,
                'post_status' => 'publish',
                'post_author' =>$custom_current_user->ID,
                'post_type' => 'notify',
                'post_title' => $title,                
            );
      $discount_noti = Fre_Notification::getInstance();

          $noti = $discount_noti->insert($notification);
          if($noti)
          {
            update_post_meta($noti,'discount_code_noti',$Percent_discount_100);
            update_post_meta($noti,'discount_percent_noti',$discount_percent);
          }


    }
    
    }
  }

}

add_action( 'wp_ajax_renewWithDiscount', 'renewWithDiscount_init' );

function renewWithDiscount_init() 
{
  global $post,$wpdb;

  $discount_code=$_REQUEST['discount_code_name'];
  $plan_sku=$_REQUEST['plan_sku'];
  $pack_type=$_REQUEST['pack_type'];
  $current_user_id=get_current_user_id();

  $args=array();
      $default = array(
          'post_type' => 'discountfre',
          'post_status' => array('published'),
          //'numberposts'=>1,
           'posts_per_page'=>1,   
          'meta_key' =>'discount_code',
          'meta_query' =>array(
                            'relation' => 'AND', 
                            array(
                                    'key'=> 'discount_code',
                                    'value' => $discount_code,
                                    'compare' => '='
                                    ),                    
                             'relation' => 'AND', 
                                array(
                                    'key' => 'used',
                                    'value' => 'available',
                                    'compare' => '='
                                ),
                        ),            
    );
    $args = wp_parse_args( $args, $default );
    $discount_collection = new WP_Query($args);
    $subscribed   = is_subscriber_available();
    if($subscribed) 
    {
        if($discount_collection->have_posts())
        {
            while($discount_collection->have_posts())     
           {
              $discount_collection->the_post();
              $discount_percent=(int)get_post_meta($post->ID,'discount_percent',true);
              $discount_code_id=$post->ID;
           }    
           if($discount_percent==100)
           {
              
              $current_subscription=get_current_subscription_by_id_discount((int)$current_user_id);
              //compare plan sku before update
              if($plan_sku == $current_subscription->plan_sku)
              {
                $current_pack=fre_get_plan_by_sql($plan_sku);
                $et_price=$current_pack->et_price;

                $et_subscription_time=$current_pack->et_subscription_time;

                $payment_gw=$current_subscription->payment_gw;

                $api_subscr_id=$current_subscription->api_subscr_id;

               

                //cong ca so luong post cu
                $number_of_posts=(int)$current_subscription->remain_posts+(int)$current_pack->et_number_posts;

                $string = "+{$et_subscription_time} months";
                $expiry_time  = strtotime($string);

                 $args = array(
                'user_id'           => $current_user_id,
                'plan_sku'          => $plan_sku,
                'pack_type'         => $pack_type,
                'price'             => $et_price,
                'currency'          => fre_get_currency_code(),
                'api_subscr_id'     => $api_subscr_id,
                'remain_posts'      => $number_of_posts,
                'expiry_time'       => $expiry_time,
                'payment_gw'        => $payment_gw,
                'payment_status'    => 'paid', // paid
                'test_mode'         => 0,
              );
            
                $args  = (object)$args;
                $result = fre_mebership_save_subscrition($args);
                $subscr_id = (int) $result;
            
                $user_info=get_user_by('id',$current_user_id);
                if($subscr_id > 0)
                {
                  $m_args = array(
                      'user_id'       => $current_user_id,
                      'user_email'    => $user_info->user_email,
                      'user_login'    => $user_info->user_login,
                      'subscr_id'         => $subscr_id,
                  );
                  $m_args  =(object)$m_args;
                  fre_save_membership( $m_args );
                  update_post_meta($discount_code_id,'used','used');
                  $msg='You have renewed the package successfully';  
                  wp_send_json(array('success' => true, 'msg' =>$msg ) );
                }
                else
                {
                  $msg='fail to renew the package';                  
                  wp_send_json(array('success' => false, 'msg' =>$msg ) );
                }

                
              }
              else
              {
                $msg='something went wrong';                  
                wp_send_json(array('success' => false, 'msg' =>$msg ) );
              }
            
           }
           else
           {  
            $msg='Invalide code';   
             wp_send_json(array('success' => false, 'msg' =>$msg ) );
           }

        }
        else
        {  

          $msg='Invalid code';
            wp_send_json(array('success' => false, 'msg' =>$msg ) );
        }
    }
    else
    {
      $msg = 'Your subscription is not available';
      wp_send_json(array('success' => false, 'msg' =>$msg ) );
    }
       
   die();

}

 function get_current_subscription_by_id_discount($user_id){

  global $wpdb;
  $tbl_subscriptions  = $wpdb->prefix . 'fre_subscriptions';  
  $sql = $wpdb->prepare("SELECT * FROM {$tbl_subscriptions} sub WHERE sub.user_id = %d ORDER BY sub.id DESC LIMIT 1", $user_id);

  $record   = $wpdb->get_row($sql, OBJECT);
  if( !isset($record->id) || $record->id == NULL ){
    return false;
  }

  return $record;
}
