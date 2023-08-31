<!--Modal send custom order-->
<?php
global $mjob_author_custom, $from_user;

 global $wp_query, $user_ID;
 if($mjob_author_custom==$user_ID)
    $mjob_author_custom=$from_user;
                        $args_custom = array(
                            'post_type'=> 'mjob_post',
                            'author'=> $mjob_author_custom,
                            'post_status'=> array(
                                'pending',
                                'publish',
                                'reject',
                                'archive',
                                'pause',
                                'unpause',
                                'draft'
                            ),
                        );
                        $query = new WP_Query( $args_custom );
                        global $ae_post_factory;
                        $post_object = $ae_post_factory->get( 'mjob_post' );                      
                        $post_data = array();
                       
                        while($query->have_posts() )
                        {
                               $query->the_post();
                              global $post;
                              $convert = $post_object->convert( $post );
                              $post_data[] = $convert;                              
                              $mjob_list_items[]=array('id'=>$convert->id,'mjob'=>$convert->post_title);
                        }   

?>
<div class="modal fade" id="customOrderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><img
                            src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt=""></span></button>
                <h4 class="modal-title" id="myModalLabel"><?php _e('Custom order', 'enginethemes'); ?></h4>
            </div>
            <div class="modal-body form-custom-order">
                <form class="et-form form-delivery-order customOrderModal custom-sendorder-form-modal">
                    <h3 class="mjob-name"></h3>   
                    <?php 
                    if( !empty($mjob_list_items))
                    {
                        echo '<div class="form-group clearfix" id="form-select-mjob-order" style="display:none">';
                        echo '<div class="input-group">';
                        echo '<label for="form-mjob_custom_order">選擇一項服務</label>' ;
                        echo '<select id="mjob_order_custom_choose" name="mjob_order_custom_choose" class="form-control" style="border:0 !important;border-bottom: 1px solid rgba(137, 138, 144, 0.2) !important;box-shadow: none;padding:0 !important;">';
                        echo '<option>選擇一項服務</option>';
                         foreach($mjob_list_items as $item)
                        {
                            echo '<option value="'.$item['id'].'">';

                            echo $item['mjob'];
                            echo '</option>';
                        }
                        echo '</select>';
                        echo '</div>';
                        echo '</div>';
                    }

                    ?>                
                       

                    <div class="form-group text-field">
                        <label for="form_post_content"><?php _e( 'Description', 'enginethemes' ); ?></label>
                        <textarea name="post_content" id="form_post_content"></textarea>
                    </div>

                    <div class="form-group clearfix">
                        <div class="input-group">
                            <label for="form-budget"><?php _e( 'Budget', 'enginethemes' ); ?> (<?php ae_currency_code(); ?>)</label>
                            <input type="number" name="budget" id="from-budget" class="form-control">
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <div class="input-group time">
                            <label for="from_deadline"><?php _e('Time of delivery (Day)', 'enginethemes'); ?></label>
                            <input type="number" name="deadline" id="from_deadline" class="form-control">
                        </div>
                    </div>
                   
                    <div class="form-group clearfix">
                        <div class="attachment-file gallery_container" id="gallery_container">
                            <div class="attachment-image">
                                <ul class="gallery-image carousel-list custom-order-image-list" id="image-list">
                                </ul>
                                <?php mje_render_attach_file_button('custom-order'); ?>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" class="input-item post-service_nonce" name="_wpnonce" value="<?php echo de_create_nonce('ae-mjob_post-sync');?>" />
                    <button class="<?php mje_button_classes( array( 'send-custom-order', 'waves-effect', 'waves-light' ) ); ?>"><?php _e('Send', 'enginethemes'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
