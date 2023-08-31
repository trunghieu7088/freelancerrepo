<?php

global $wp_query, $ae_post_factory, $post, $user_ID;

$post_object = $ae_post_factory->get('ae_message');

$current = $post_object->convert($post);

if($user_ID == $current->to_user) {

    update_post_meta($current->ID, "receiver_unread", "");

}

?>



<?php if($current->type == 'changelog'): ?>

    <li class="clearfix message-item block-changelog">

        <div class="changelog-item">

            <div class="changelog-text">

                <?php

                    echo $current->changelog;

                ?>

            </div>



            <div class="message-time">

                <?php echo $current->post_date; ?>

            </div>

        </div>

    </li>

<?php else: ?>

    <li class="clearfix message-item msg-item-<?php echo $current->ID;?>">

        <div class="<?php echo $current->message_class; ?>">

            <div class="img-avatar">

                <?php echo $current->author_avatar; ?>

            </div>



            <?php if($current->type == 'offer') { ?>

                <!-- Custom Offer Message -->

                <div class="conversation-text custom-offer">

                    <p class="offer-label"><a href="#" data-id="<?php echo $current->custom_order_id; ?>" class="color-custom-label name-customer-order"><?php _e('Custom Offer', 'enginethemes'); ?></a></p>

                    <?php echo $current->post_content_filtered; ?>

                    <?php echo $current->message_attachment; ?>

                    <div class="budget"><p><?php _e('Budget') ?><span class="mje-price-text"><?php if($current->budget) echo $current->budget ?></span></p></div>

                    <div class="deadline"><p><?php _e('Time of delivery', 'enginethemes') ?><span><?php if($current->deadline) echo $current->deadline; ?></span></p></div>

                </div>

                <!-- End Custom Offer Message -->

                <?php

            } else if($current->type == 'custom_order') { ?>

                <!-- Custom Order Message -->

                <div class="conversation-text custom-order">

                    <p class="offer-label"><?php echo $current->mjob_title; ?></p>

                    <p class="view-custom-order"><a class="link-view-custom-order name-customer-order" data-id="<?php echo $current->ID; ?>"><?php _e('View', 'enginethemes'); ?></a> <?php _e('this custom order', 'enginethemes'); ?></p>

                    <div class="budget"><p><?php _e('Budget') ?>

                            <span class="mje-price-text"><?php if ($current->budget) echo $current->budget ?></span></p></div>

                    <div class="deadline"><p><?php _e('Time of delivery', 'enginethemes') ?>

                            <span><?php if ($current->deadline) echo $current->deadline; ?></span></p></div>

                </div>

                <?php

            } else if($current->type == 'decline') {

                ?>

                <div class="conversation-text">

                    <p class="offer-label name-customer-order" data-id="<?php echo $current->custom_order_id; ?>"><?php _e('CUSTOM ORDER DECLINED', 'enginethemes'); ?></p>

                    <?php echo $current->post_content_filtered; ?>

                </div>

                <?php

            } else if($current->type == 'reject') {

                ?>

                <div class="conversation-text">

                    <p class="offer-label name-customer-order" data-id="<?php echo $current->custom_order_id; ?>"><?php _e('OFFER REJECTED', 'enginethemes'); ?></p>

                    <?php echo $current->post_content_filtered; ?>

                </div>

                <!-- End Custom Order Message -->

             <?php } else { ?>

                <div class="conversation-text">

                    <?php echo $current->post_content_filtered; ?>

                    <?php echo $current->message_attachment; ?>

                </div>

            <?php } ?>

            <div class="message-time">

                <?php

                if($current->admin_message == true) {

                    echo '<strong>' . __("by Admin", 'enginethemes') . '</strong> - ' . $current->post_date;

                } else {

                    echo $current->post_date;

                }

                ?>

            </div>

        </div>

    </li>

     <?php
        //custom code for seen

        $conversation_id=get_post_meta($current->ID,'parent_conversation_id',true);       
         ?>
        <?php if(get_post_meta($conversation_id,'type',true)=='conversation' && $current->ID == is_last_seen_message($conversation_id,get_current_user_id())): ?>
         <div class="single-seenlabel text-right">
                <i class="fa fa-check" aria-hidden="true"></i> VU
         </div>
         <?php endif; ?>

        <?php 

        if(get_post_type($conversation_id)=='mjob_order' && $current->ID == is_last_seen_message($conversation_id,get_current_user_id())): ?>
         <div class="single-seenlabel text-right">
                <i class="fa fa-check" aria-hidden="true"></i> VU
         </div>
         <?php endif; ?>
         
<?php endif; ?>

