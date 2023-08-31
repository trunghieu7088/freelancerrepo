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
                        '79f2750396f1ce73fcd0',
                        '01b7e1f73babfb41c11c',
                        '1604376',
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
