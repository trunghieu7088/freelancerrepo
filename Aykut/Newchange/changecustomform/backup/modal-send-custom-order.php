<!--Modal send custom order-->
<div class="modal fade" id="customOrderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><img
                            src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt=""></span></button>
                <h4 class="modal-title" id="myModalLabel"><?php _e('Custom order', 'enginethemes'); ?></h4>
            </div>
            <div class="modal-body form-custom-order">
                <form class="et-form form-delivery-order customOrderModal ">
                    <h3 class="mjob-name"></h3>

                    <div class="form-group clearfix">
                        <div class="input-group time">
                            <label for="kindwork"><?php _e('Art der Arbeit', 'enginethemes'); ?></label>
                            <select class="input-item form-control" name="kindwork" id="kindwork" style="outline: none !important;border:none !important;box-shadow: none !important;border-bottom: 1px solid rgba(137, 138, 144, 0.2) !important;margin-top:10px !important;" >
                                <option value="">Wähle hier die Art der Arbeit aus</option>
                                <?php 
               $degrees_list=get_all_kindwork_for_customOrderForm();

                foreach($degrees_list as $degree => $item)
                {
                    echo '<option value="'.$item->term_id.'">';
                    echo $item->name;
                    echo '</option>';
                }   
               ?>
                    </select>
                        </div>
                    </div>

                    <div class="form-group clearfix">
                        <div class="input-group">
                            <label for="topic"><?php _e( 'Thema', 'enginethemes' ); ?></label>
                            <input type="text" name="topic" id="topic" class="form-control">
                        </div>
                    </div>

                      <div class="form-group clearfix">
                        <div class="input-group time">
                            <label for="amountpage"><?php _e('Seitenanzahl', 'enginethemes'); ?></label>
                            <input type="number" name="amountpage" id="amountpage" class="form-control">
                        </div>
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

                    <div class="form-group text-field">
                        <label for="form_post_content"><?php _e( 'Description', 'enginethemes' ); ?></label>
                        <textarea name="post_content" id="form_post_content"></textarea>
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
