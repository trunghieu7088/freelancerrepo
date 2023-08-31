<?php
global $user_ID, $ae_post_factory;
$ae_message_obj = $ae_post_factory->get('ae_message');

$role = ae_user_role($user_ID);
$order_status = $mjob_order->post_status; // disputing, disputed

//custom code here 

$js_to_user=get_the_author_meta( 'display_name', $mjob_order->to_user );
//


// if ($role == "author" ||
// 	($role == "administrator" && ($order_status == "disputing" || $order_status == "disputed")) ||
// 	($role == "administrator" && ($user_ID == $mjob_order->buyer_id || $user_ID == $mjob_order->seller_id) ) ||
//     ( in_array($order_status, array("publish","disputed")) && ($user_ID == $mjob_order->buyer_id || $user_ID == $mjob_order->seller_id) )
// ):
$admin_view_chat = $current_user_is_partner  = 0;
if( current_user_can('manage_options') &&  in_array($order_status,array('disputed','disputing') ) ){
    $admin_view_chat = 1;
}
if( $user_ID == $mjob_order->buyer_id || $user_ID == $mjob_order->seller_id ){
    $current_user_is_partner = 1;
}

if  ( $current_user_is_partner  || $admin_view_chat ) : ?>
    <div class="conversation-form microjobengine\template\mjob-order\conversation.php line15">
        <div class="inner-conversation-form"><?php
                $post_data = array();
                $args = array(
                	'post_type' => 'ae_message',
                	'post_status' => array('publish', 'future'),
                	'post_parent' => $mjob_order->ID,
                	'orderby' => 'date',
                	'order' => 'DESC',
                );

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
                // End load more link
                echo '<ul class="list-conversation">';
                //get_template_part('template/message', 'item');
                while ($messages_query->have_posts()):
                	$messages_query->the_post();
                	global $post;
                	$convert = $ae_message_obj->convert($post);
                	$post_data[] = $convert;
                	get_template_part('template/message', 'item');
                endwhile;
                wp_reset_postdata();
                echo '</ul></div>';

                 //custom code here for suggested message
                  if ( $order_status != 'finished' && $order_status != 'disputed' && $current_user_is_partner )
                  {
                         echo ' <div class="col-sm-12 col-md-12" id="typing-indicator" style="padding-top:10px;display:none;color:#B0B0B0;font-style: italic;">
                            </div>';

                             echo '<a href="javascript:void(0);" id="show-suggested-message" style="margin-left:20px;margin-top:20px;font-size:18px;display:inline-block;"> <i class="fa fa-lightbulb-o" aria-hidden="true"></i> Suggestion de message  <i class="fa fa-chevron-down"></i></a>';
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
                
                   //end custom code here

            /**
             * render post data for js
             */
            echo '<script type="data/json" class="message_postdata" >' . json_encode($post_data) . '</script>';

            if ( $order_status != 'finished' && $order_status != 'disputed' && $current_user_is_partner ): ?>
                <div class="compose-conversation mjob-conversation-form">
                    <form>
                        <input type="hidden" id="from_user" value="<?php echo $user_ID; ?>">
                        <input type="hidden" id="to_user" value="<?php echo $mjob_order->to_user; ?>">
                        <input type="hidden" id="conversation_id" value="<?php echo $mjob_order->ID; ?>">
                        <input type="hidden" id="page_type" value="mjob_order">
                        <input type="hidden" class="input-item post-service_nonce" name="_wpnonce" value="<?php echo de_create_nonce('ae-mjob_post-sync'); ?>" />
                        <div class="form-group compose">
                            <div class="attachment-file gallery_container_single_conversation" id="message_modal_gallery_container">
                                <!-- message input field -->
                                <div class="group-compose">
                                    <div class="input-compose">
                                        <!-- attachments list-->
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
                                       <!-- <button class="send-message"><?php /*_e('Send', 'enginethemes'); */?></button>-->

                                    </div>
                                    <a class="send-message mje-send-message" href="javascript:void(0)"><i class="fa fa-paper-plane" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div> <?php
            endif; ?>
        </div>
        <?php if ($order_status == "publish" || $order_status == "delivery" || $order_status == "late"): ?>
         <div class="float-right dispute-link"><a href="#" data-toggle="modal" data-target="#disputeConfirm"><?php _e('Dispute', 'enginethemes');?></a> <i class="fa fa-gavel" aria-hidden="true"></i></div>
        <?php endif;?>
    </div> <?php
endif;
if (isset($messages_query->query)) {
	echo '<script type="data/json" id="default-message-query">' . json_encode($messages_query->query) . '</script>';
}
?>

<div id="disputeConfirm" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Confirmation du litige</h4>
      </div>
      <div class="modal-body">
        <h5 class="text-justify" style="line-height: 25px;padding:10px">Êtes-vous sûr de commencer un litige sur cette commande? Vous ne pourrez plus communiquer avec la personne.
Un litige entraîne la suspension de la commande jusqu’à ce qu’un modérateur analyse la situation.
Merci de le faire qu’en cas de problème important. </h5>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger dispute-button" data-dismiss="modal">Confirmer le litige</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
      </div>
    </div>

  </div>
</div>


<script type="text/javascript">
(function ($) {
  $(document).ready(function () {

    let to_user_js='<?php echo $js_to_user; ?>';    
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


  });
})(jQuery);
</script>
