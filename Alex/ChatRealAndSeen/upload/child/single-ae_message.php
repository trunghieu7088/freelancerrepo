<?php
global $post, $ae_post_factory, $user_ID;
$post_object    = $ae_post_factory->get('ae_message');
$current        = $post_object->convert($post);

$from_user      = $current->from_user;
$to_user        = $current->to_user;
$receiver_id    = $to_user;
$is_admin       = $only_view = 0;
$sender_or_receiver = 0;
// Get receiver_id

// my custom code
global $mjob_author_custom, $from_user;
$mjob_author_custom = $receiver_id;
//var_dump($current);
//echo 'id ne : '.$current->id;
$move_to_conversation= $from_user;
if ($user_ID == $from_user) 
{
$move_to_conversation=$to_user;
}
$custom_conversation_guid=mje_get_conversation($user_ID,$move_to_conversation);
$custom_guid=$custom_conversation_guid[0]->guid;


// my custom code


if ($user_ID == $from_user) {
	$receiver_id = $to_user;
    $sender_or_receiver = 1;
} else if ($user_ID == $to_user) {
	$receiver_id = $from_user;
    $sender_or_receiver = 1;
} else if( current_user_can('manage_options') ) {
    $only_view = 1;
    if($current->post_parent > 0){
        $conversation_link = get_permalink($current->post_parent);
        wp_redirect($conversation_link);
    }
} else {
    wp_redirect(et_get_page_link('dashboard'));
}
if($sender_or_receiver && $post->post_parent > 0){
    // fix view message detail from back-end.
    $conversation_link = get_permalink($current->post_parent);
    wp_redirect($conversation_link);
}
// Set converstaion status to read
if( ! $only_view )
 // update_post_meta($post->ID, $user_ID . '_conversation_status', 'read');

//update bang ham sendalertseen roi nen ko can nua ( realtime and seen not seen option)

$receiver_name  = get_the_author_meta('display_name', $receiver_id);
$receiver_url   = get_author_posts_url($receiver_id);
get_header();
?>
<div id="content" class="mjob_conversation_detail_page">
    <div class="block-page">
        <div class="container">
            <div class="row title-top-pages dashboard withdraw no-margin">
                <div class="box-shadow-title">
                    <p class="block-title"><?php printf(__('Conversation <span class="user-conversation">with <a href="%s" class="" target="_blank">%s</a></span>', 'enginethemes'), $receiver_url, $receiver_name);?></p>
                    <a href="<?php echo et_get_page_link('my-list-messages'); ?>" class="btn-back"><i class="fa fa-angle-left"></i><?php _e('Back to message list', 'enginethemes');?></a>
                </div>
            </div>

            <div class="row" style="margin:15px 0 0 0;">
                <?php 
                //custom code here for online / offline
                $profile_id_status=get_user_meta($receiver_id,'user_profile_id',true);
                $custom_online_status=get_post_meta($profile_id_status,'custom_online_status',true) ? get_post_meta($profile_id_status,'custom_online_status',true) : 'offline'; 
                if($custom_online_status=='online')
                {
                    $status_label='label-success';
                    $custom_online_status_text='en ligne';
                }
                else
                {
                    $status_label='label-default';
                     $custom_online_status_text='hors-ligne';
                }
                ?>
                <p id="text-status-normal" class="<?php echo 'label text-uppercase '.$status_label; ?>" style="font-size:14px!important;">
                <?php echo $receiver_name.' est '.$custom_online_status_text;  ?>
                </p>
            </div>

            <div class="row no-margin">
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 conversation-form">
                     <div class="inner-conversation-form single-ae_message.php line-36">  <?php
$post_data = array();
$args = mje_get_default_conversation_query_args($receiver_id);
array_push($args['meta_query'], array(
	'key' => 'parent_conversation_id',
	'value' => $current->ID,
));

$messages_query = new WP_Query($args);
$messages_query->posts = array_reverse($messages_query->posts);
$messages_query->query = array_merge($messages_query->query, array(
	'fetch_type' => 'message',
));

echo '<div class="wrapper-list-conversation">';
// Load more link
echo '<div class="paginations-wrapper">';
ae_pagination($messages_query, get_query_var('paged'), 'load', __('Load older messages', 'enginethemes'));
echo '</div>';

echo '<ul id="normalMessageList" class="list-conversation list-conversation-custom">';
//get_template_part('template/message', 'item');
if( $messages_query->have_posts() ){
    while ($messages_query->have_posts()):
    	$messages_query->the_post();
    	$convert = $post_object->convert($post);
    	$post_data[] = $convert;
    	get_template_part('template/message', 'item');
    endwhile;
wp_reset_postdata();
} else {
    echo 'No mesage.'; // debug only
}

//seen / not seen option


  $latest_reply=get_post_meta($post->ID,'latest_reply',true);    
    if($latest_reply)
    {
        $latest_reply_author=get_post_field('post_author',$latest_reply);
    }
    //echo 'last reply :'.$latest_reply_author;
    $last_reply_to_user=get_post_meta( $latest_reply,'to_user',true);
    $conversation_custom_status=get_post_meta($post->ID,$last_reply_to_user.'_conversation_status',true);
    if($latest_reply_author == get_current_user_id() && $conversation_custom_status=='read')
    {
        //echo '<i class="fa fa-check" aria-hidden="true"></i> SEEN';
        echo '<p id="seenlabel" class="text-right text-seen-custom"><i class="fa fa-check" aria-hidden="true"></i> VU</p>';
    }
    //seen / not seen option end

echo '</ul></div>';

/**
 * render post data for js
 */
//                       conversation-form

 //custom code here for realtime chat
                            echo ' <div class="col-sm-12 col-md-12" id="typing-indicator" style="padding-top:10px;display:none;color:#B0B0B0;font-style: italic;" data-normal-id-conversation="'.$current->ID.'">
          
                </div>';
                //end

echo '<script type="data/json" class="message_postdata" >' . json_encode($post_data) . '</script>';

     //custom code here for suggested message
        if(!$only_view)
        {   
          //custom code typing indicator
          echo ' <div class="col-sm-12 col-md-12" id="typing-indicator" style="padding-top:10px;display:none;color:#B0B0B0;font-style: italic;">
          
                </div>';
                //end typing
                     echo '<a href="javascript:void(0);" id="show-suggested-message" style="margin-left:20px;margin-top:20px;font-size:18px;display:inline-block;"> <i class="fa fa-lightbulb-o" aria-hidden="true"></i> Suggestion de message <i class="fa fa-chevron-down"></i></a>';
                 $args_smessage = array(
                        'numberposts' => 10,
                        'post_type' =>'smessage',
                        'post_status' => 'publish',
                        'numberposts' => 5,
                        'orderby' => 'date',
                        );
                 $smessages=get_posts($args_smessage);
              //   var_dump($smessages);
                 if(!empty($smessages))
                 {
                     echo '<div class="col-md-12 col-sm-12 col-xs-12" id="suggested-message-list" data-custom-show="show" style="cursor:pointer !imporant;padding-top:10px;display:none;cursor: pointer !important;z-index:99999;">';
                     foreach($smessages as $smessage_item)
                     {  
                          echo '<p class="sg-message" style="margin-top:10px;margin-right:10px;border:1px solid #F5F5F5;border-radius:5px;background-color:#F5F5F5;display:inline-block;padding:5px;z-index:9999;">';
                          echo $smessage_item->post_content;
                          echo '</p>';
                     }
                      echo '</div>';
                 }
        }  
    
                 //end

if( ! $only_view ){ ?>

        <div class="compose-conversation mjob-conversation-form">
            <form>
                <input type="hidden" id="from_user" value="<?php echo $user_ID; ?>">
                <input type="hidden" id="to_user" value="<?php echo $receiver_id; ?>">
                <input type="hidden" id="conversation_id" value="<?php echo $current->ID; ?>">
                <input type="hidden" class="input-item _wpnonce" name="_wpnonce" value="<?php echo de_create_nonce('ae-mjob_post-sync'); ?>" />
                <div class="form-group compose">
                    <div class="attachment-file gallery_container_single_conversation" id="message_modal_gallery_container">
                        <!-- attachments list-->

                        <!-- message input field -->
                        <div class="group-compose">
                            <div class="input-compose">
                                <ul class="gallery-image carousel-list carousel_single_conversation-image-list" id="image-list">
                                </ul>
                                <!--<input type="text" name="post_content" id="post_content" placeholder="<?php /*_e('Type here to reply', 'enginethemes'); */?>">-->
                                <textarea name="post_content" id="post_content" rows="1" placeholder="<?php _e('Type your message...', 'enginethemes');?>"></textarea>
                            </div>

                            <!-- attachment and send button-->
                            <div class="action-link">
                                <div class="attachment-image">
                                    <span class="plupload_buttons" id="carousel_single_conversation_container">
                                        <span class="img-gallery" id="carousel_single_conversation_browse_button">
                                            <a href="#" class="add-img"><i class="fa fa-paperclip"></i></a>
                                        </span>
                                    </span>
                                    <span class="et_ajaxnonce" id="<?php echo wp_create_nonce('ad_carousels_et_uploader'); ?>"></span>
                                </div>
                            </div>
                            <a class="send-message mje-send-message" href="javascript:void(0)"><i class="fa fa-paper-plane" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
    </div>
</div>

<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 current">
    <div class="current-order custom-order-box box-shadow">
        <p class="title-column"><?php _e('Custom orders', 'enginethemes');?></p>
        <div class="list-order list-custom-order-wrapper">
            <ul class="list-custom-order">
                                <?php
$args = array(
	'post_type' => 'ae_message',
	'post_status' => 'publish',
	'meta_query' => array(
		'relation' => 'AND',
		array(
			'key' => 'parent_conversation_id',
			'value' => $post->ID,
		),
		array(
			'key' => 'type',
			'value' => 'custom_order',
		),
		array(
			'relation' => 'OR',
			array(
				'key' => 'custom_order_status',
				'value' => array('decline', 'checkout', 'reject'),
				'compare' => 'NOT IN',
			),
			array(
				'key' => 'custom_order_status',
				'compare' => 'NOT EXISTS',
			),
		),
	),
	'order' => 'DESC',
);
$custom_order_query = new WP_Query($args);
$post_data_custom = array();
$order_object = $ae_post_factory->get('ae_message');
if ($custom_order_query->have_posts()):
	while ($custom_order_query->have_posts()): $custom_order_query->the_post();
		$custom = $order_object->convert($post);
		$post_data_custom[] = $custom;
		get_template_part('template/custom-order', 'item');
	endwhile;

else:
	echo __('<p class="no-custom">No custom orders</p>', 'enginethemes');
endif;
?>
                            </ul>

                            <?php echo '<script type="data/json" class="custom_order_postdata" >' . json_encode($post_data_custom) . '</script>'; ?>
                            <div class="more">
                                <?php
echo '<div class="paginations-wrapper">';
ae_pagination($custom_order_query, get_query_var('paged'), 'load', __('Load more', 'enginethemes'));
echo '</div>';
?>
                            </div>
                             <button style="margin-left:20px;margin-bottom:10px;" class="btn-send-offer" data-custom-order="none"><?php _e('Envoyer une offre à votre client', 'enginethemes') ?></button>
                             <a style="background-color:#ff5733 !important;border-color: #ff5733 !important;border:none;outline:none;padding:5px 20px;border-radius:45px;font-size:12px;color:#fff;cursor: pointer;display:inline-block;margin-left:20px;margin-bottom:10px;" id="bt-send-custom" class="bt-send-custom choose-mjob-conversation"  data-mjob-name="" data-mjob="na" data-conversation-guid="<?php echo $custom_guid; ?>" data-conversation-parent="<?php echo $current->id;?>" data-active-conversation="active" data-to-user="<?php echo $receiver_id; ?>" data-from-user="<?php echo $user_ID; ?>">Proposer une offre au vendeur</a>
                        </div>
                    </div>
	<p style="margin-top: 10px;padding-left: 30px;font-size:14px !important;">
		<strong>Comment passer commande sur Voix Off Master?</strong>
	</p>
					 <iframe style="margin-top: 5px;padding-left: 30px; width: 100%;" height="250px" src="https://www.youtube.com/embed/6IU_RO83U-Q" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>  
                </div>
            </div>
            <?php
get_template_part('template/custom-order', 'detail');
get_template_part('template/modal-send-custom', 'order');
?>
        </div>
        <div class="overlay-custom-detail"></div>
    </div>
</div>
<?php
//Load template js
get_template_part('template-js/custom-order', 'item');
echo '<script type="data/json" id="default-message-query">' . json_encode($messages_query->query) . '</script>';
get_footer();
?>


<script type="text/javascript">
(function ($) {
  $(document).ready(function () {

    let to_user_js='<?php echo $receiver_name; ?>';    
   $('#show-suggested-message').click(function(){

    let show_option=$('#suggested-message-list').attr('data-custom-show');
    if(show_option == 'show')
    {
        $('#suggested-message-list').attr('data-custom-show','notShow');         
        $('#suggested-message-list').slideDown('fast');
        $(this).html('<i class="fa fa-lightbulb-o" aria-hidden="true"></i> Suggested Message  <i class="fa fa-chevron-up"></i>');         
    }
    else
    {
        $('#suggested-message-list').attr('data-custom-show','show');
        $('#suggested-message-list').slideUp('fast');
        $(this).html('<i class="fa fa-lightbulb-o" aria-hidden="true"></i> Suggested Message  <i class="fa fa-chevron-down"></i>');
    }

      $('.sg-message').click(function(){                
        
        $("#post_content").val('@'+to_user_js+' '+$(this).text());
      });
   
   });

    var profile_id='<?php echo  $profile_id_status; ?>';

    var is_logged='<?php if(is_user_logged_in()) echo 'check'; else echo 'no'; ?>';
    
    let current_status='<?php echo $custom_online_status; ?>';

    let status_text_french='';
      if(is_logged=='check'){
                
                setInterval(function()
                { 

                        $.ajax({
                    type : "get", 
                    dataType : "json", 
                    url : 'https://voixoffmaster.com/wp-admin/admin-ajax.php',
                    data : {
                        action: "get_status_online",     
                        profile_id_send: profile_id,
                        
                    },
                    context: this,
                    beforeSend: function(){
                        
                    },
                    success: function(response) {
                        
                        if(response.success) 
                        {             
                            $('#text-status-normal').removeClass('label-default');             
                            $('#text-status-normal').removeClass('label-success');                 
                           if(response.data.status=='online')
                           {
                            
                            $('#text-status-normal').addClass('label-success');
                              status_text_french='En ligne';
                           }
                           else
                           {
                              $('#text-status-normal').addClass('label-default');   
                               status_text_french='Hors-ligne';
                           }

                           $('#text-status-normal').html(response.data.name+' est '+status_text_french);
                           
                           if(current_status != response.data.status)
                           {             
                            toastr.options.timeOut = 3000;        
                             toastr.info(response.data.name+' est '+status_text_french);
                             current_status=response.data.status;
                           }

                        }                  
                    },
                    error: function( jqXHR, textStatus, errorThrown ){                        
                        console.log( 'The following error occured: ' + textStatus, errorThrown );
                    }
                })
               
                }, 10000);

            }



  });
})(jQuery);
</script>