<?php

function add_page_for_realtime_function()
{
$PageGuid = site_url() . "/realtime-page";
$check_exist=get_page_by_title('realtime-page');
      if(empty($check_exist))
      {
        $realtime_page = array( 'post_title'     => 'realtime-page',
                         'post_type'      => 'page',
                         'post_name'      => 'realtime-page',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $realtime_page_id=wp_insert_post( $realtime_page, FALSE ); 
      add_post_meta($realtime_page_id,'_wp_page_template','page-realtime.php');
      }

}

add_action( 'init', 'add_page_for_realtime_function' );

add_action('wp_enqueue_scripts', 'add_custom_js_realtime');
function add_custom_js_realtime()
{    
  
   wp_enqueue_style('seen-style', get_stylesheet_directory_uri(). '/assets/css/seenstyle.css', array(), '1.0', 'all');
  if (is_single() && ('ae_message' == get_post_type() || 'mjob_order' == get_post_type() ))   
  {
  	  wp_enqueue_script('custom-realtime-js', get_stylesheet_directory_uri().'/assets/js/custom-realtime.js', array(
                'front'
            ), ET_VERSION, true);

    wp_enqueue_script('custom-pusher-js', get_stylesheet_directory_uri().'/assets/js/pusher.js', array(
                'front'
            ), ET_VERSION, true);
  }
  
}

add_action('wp_enqueue_scripts', 'override_conversationjs');
function override_conversationjs()
{    
    wp_deregister_script('conversation');
    wp_enqueue_script('conversation', get_stylesheet_directory_uri() . '/assets/js/conversation.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front',
            'mjob-auth',
            'ae-message-js'), ET_VERSION, true);

}

add_action( 'wp_ajax_show_typing_text_normalmessage', 'show_typing_text_normalmessage_init' );

function show_typing_text_normalmessage_init() {


                require __DIR__ . '/vendor/autoload.php';
                     $options = array(
                        'cluster' => 'eu',
                        'useTLS' => true
                      );
                      $pusher = new Pusher\Pusher(
                        '648b4b78f093044403e3',
                        'd0a7967a325552e597c0',
                        '1626253',
                        $options
                      );

             // doan code nay dung de gui typing den user
     		if ( is_user_logged_in() )
            {                
            	$from_user=get_the_author_meta('display_name',$_POST['from_user']);
                $data['message']=$from_user.' is typing a message ....';    
                $data['conversation_order_id']=$_POST['real_order_id_send'];
                $data['normal_conversation_order_id']=$_POST['normal_order_id_send'];
                
                $pusher->trigger('presence-typing-indicator-channel'. $_POST['to_user'], 'order-message-typing-event-'.$_POST['real_order_id_send'], $data);

                $pusher->trigger('presence-typing-indicator-channel'. $_POST['to_user'], 'normal-message-typing-event-'.$_POST['normal_order_id_send'], $data);
            }       
 	// doan code nay chi de hoan thanh ajax ko co nhieu y nghia
    $r_response['string']='true';    
    wp_send_json_success($r_response);
    die();//bắt buộc phải có khi kết thúc
}

add_action( 'wp_ajax_sendSeenAlertNormal', 'sendSeenAlertNormal_init' );

function sendSeenAlertNormal_init()
{
   require __DIR__ . '/vendor/autoload.php';
                     $options = array(
                        'cluster' => 'eu',
                        'useTLS' => true
                      );
                      $pusher = new Pusher\Pusher(
                        '648b4b78f093044403e3',
                        'd0a7967a325552e597c0',
                        '1626253',
                        $options
                      );

  if ( is_user_logged_in() )
            {                       
                
                //get conversation ID
                $normal_conversation_id=$_POST['normal_order_id_send'];
                $order_conversation_id=$_POST['real_order_id_send'];
                if($normal_conversation_id)
                {     
                       $status_before_update=get_post_meta($normal_conversation_id,$_POST['from_user'].'_conversation_status',true);
                      //update conversation status to read
                      update_post_meta($normal_conversation_id,$_POST['from_user'].'_conversation_status','read');
                      markSeenMessages($normal_conversation_id,$_POST['to_user']);
                      $latest_reply=get_post_meta( $normal_conversation_id,'latest_reply',true);
                      $data['message']='seen'; // test code
                      if($latest_reply)
                      {
                          $latest_reply_author=get_post_field('post_author',$latest_reply);
                      }

                      if($normal_conversation_id && $status_before_update=='unread'  &&  $latest_reply_author!=get_current_user_id())
                      {
                        $pusher->trigger('presence-typing-indicator-channel'. $_POST['to_user'], 'normal-message-seen-event'.$_POST['to_user'], $data);
                        $r_response['string']='co gui di ne'; 
                         

                      } 
                      else
                      {
                        $r_response['string']='ko gui di';  
                  
                      }     
                }

                if($order_conversation_id)
                {
                  $status_before_update=get_post_meta($order_conversation_id,$_POST['from_user'].'_conversation_status',true);
                      //update conversation status to read
                      update_post_meta($order_conversation_id,$_POST['from_user'].'_conversation_status','read');
                      markSeenMessages($order_conversation_id,$_POST['to_user']);
                      $latest_reply=get_post_meta( $order_conversation_id,'latest_reply',true);
                      $data['message']='seen'; // test code
                      if($latest_reply)
                      {
                          $latest_reply_author=get_post_field('post_author',$latest_reply);
                      }

                      if($order_conversation_id && $status_before_update=='unread'  &&  $latest_reply_author!=get_current_user_id())
                      {
                        $pusher->trigger('presence-typing-indicator-channel'. $_POST['to_user'], 'order-message-seen-event-'.$_POST['to_user'].'-'.$order_conversation_id, $data);
                        $r_response['string']='co gui di ne'; 
                         

                      } 
                      else
                      {
                        $r_response['string']='ko gui di';  
                  
                      }     
                }
                       

                
            }       
  // doan code nay chi de hoan thanh ajax ko co nhieu y nghia
   // $r_response['string']='true';    
    wp_send_json_success($r_response);
    die();//bắt buộc phải có khi kết thúc                      

}

function is_last_seen_message($conversation_id,$message_author)
{
  global $post;
  $latest_reply=(int)get_post_meta($conversation_id,'latest_reply',true);
   $latest_reply_author=get_post_field('post_author',$latest_reply);
  if($message_author== $latest_reply_author)
  {
     $args=array();
  $default = array(
    'post_type'  => 'ae_message',
    'post_status' =>'publish',
    'author' => $message_author,
    'post_parent' => $conversation_id,
    'posts_per_page' => 1,
      'meta_query' => array(
        array(
            'key'     => 'receiver_unread',
            'value'   => '',
        ),
        array(
            'key'     => 'type',
            'value'   => 'message',
        ),
    ),
    'order' =>'DESC',
    'orderby' => 'date',
  
);
    $args = wp_parse_args( $args, $default );
$last_message = new WP_Query( $args );

//$last_message = get_posts( $query );
//var_dump($last_message);
if($last_message->have_posts())
{
    while($last_message->have_posts())
    {
      $last_message->the_post();
        $last_seen_messageID= $post->ID;
    }
    
}
  $receiver_id=get_post_meta( $last_seen_messageID,'to_user',true);
      if(get_post_meta($conversation_id,$receiver_id.'_conversation_status',true)=='unread')
      {
        return $last_seen_messageID;  
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

function markSeenMessages($conversation_id,$message_owner)
{
   global $post;
      $args=array();
  $default = array(
    'post_type'  => 'ae_message',
    'post_status' =>'publish',
    'author' => $message_owner,
    'post_parent' => $conversation_id,
    'posts_per_page' => -1,
      'meta_query' => array(      
        array(
            'key'     => 'type',
            'value'   => 'message',
        ),
    ),
    'order' =>'DESC',
    'orderby' => 'date',
  
);
    $args = wp_parse_args( $args, $default );
$all_messages = new WP_Query( $args );
if($all_messages->have_posts())
{
    while($all_messages->have_posts())
    {
       $all_messages->the_post();
        update_post_meta($post->ID,'receiver_unread','');
        
    }
    
}
}