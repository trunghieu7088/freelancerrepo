<?php wp_reset_query();
global $user_ID, $post;
$payer_of_commission = ae_get_option( 'payer_of_commission' );
$commission_type     = ae_get_option( 'commission_type' ); // percent, currency. default is percent.
$currency            = ae_get_option( 'currency', array( 'align' => 'left', 'code' => 'USD', 'icon' => '$' ) );
$commission          = ae_get_option( 'commission', 0 );

//custom code here
$list_freelancer='';
$list_freelancer=apply_filters('fetch_profile_freelancer',$list_freelancer,$post->ID);	
//end
?>
<!-- MODAL BIG -->
<div class="modal fade" id="modal_bid_admin">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title"><?php _e( 'Bid this project', ET_DOMAIN ); ?></h4>
            </div>
            <div class="modal-body">
                <form role="form" id="bid_form_admin" class="bid-form fre-modal-form">
                    <div class="fre-input-field">
                        <label class="fre-field-title" for="freelancer_bid_admin"><?php _e( 'Employee', ET_DOMAIN ); ?></label>
                        <select data-chosen-width="100%" data-chosen-disable-search="" multiple class="fre-chosen-single" name="freelancer_bid_admin" id="freelancer_bid_admin">
                          
                           <?php 
                            foreach($list_freelancer as $freelancer)
                            {
                                echo $freelancer;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="fre-input-field">
                        <label class="fre-field-title" for="bid_budget_admin"><?php _e( 'Your Bid', ET_DOMAIN ); ?></label>
                        <div class="fre-project-budget">
                            <input type="number" name="bid_budget_admin" id="bid_budget_admin" class="form-control number numberVal" min="0"/>
                            <span><?php echo fre_currency_sign( false ); ?></span>
                            <?php do_action('multi_currencies_note');?>

                        </div>
                        <?php if ( ae_get_option( 'use_escrow' ) ) {
                            if ( $payer_of_commission == 'worker' ) {
                                if ( $commission_type == 'currency' ) {
                                    $commission_fee = $currency['icon'] . $commission;
                                } else {
                                    $commission_fee = $commission . '%'; // percent  with default option
                                }
                                printf( __( "<p class='bid-commission-fee'>Commission fee: <b>%s</b></p>", ET_DOMAIN ), $commission_fee );
                            }
                        } ?>
                    </div>
                    <div class="fre-input-field">
                        <label class="fre-field-title" for="bid_time_admin"><?php _e( 'Delivery', ET_DOMAIN ); ?></label>
                        <div class="row">
                            <div class="col-md-9 col-sm-8 col-xs-6">
                                <input type="number" name="bid_time_admin" id="bid_time_admin" class="form-control number numberVal" min="1"/>
                            </div>
                            <div class="col-md-3 col-sm-4 col-xs-6 no-padding-left">
                                <select class="fre-chosen-single" name="type_time_admin" id="type_time_admin">
                                    <option value="day"><?php _e( 'days', ET_DOMAIN ); ?></option>
                                    <option value="week"><?php _e( 'week', ET_DOMAIN ); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="fre-input-field no-margin-bottom">
                        <label class="fre-field-title"  for="post_content"><?php _e( 'Add Notes', ET_DOMAIN ); ?></label>
                        <textarea id="bid_content_admin" name="bid_content_admin"></textarea>
                    </div>
                    <input type="hidden" name="post_parent_admin" value="<?php the_ID(); ?>"/>                       
                    <input type="hidden" name="action" value="admin_add_employee_bid"/>                 
                    <input type="hidden" name="project_owner" value="<?php echo $post->post_author; ?>" />
                    <input type="hidden" name="project_title" value="<?php echo $post->post_title; ?>" />
                    <input type="hidden" name="project_guid" value="<?php echo $post->guid; ?>" />

                    <?php do_action( 'after_bid_form' ); ?>
                    <div class="fre-form-btn">
                        <button type="submit" class="fre-normal-btn btn-submit">
                            <?php _e( 'Submit', ET_DOMAIN ) ?>
                        </button>
                        <span class="fre-form-close" data-dismiss="modal"><?php _e('Cancel',ET_DOMAIN);?></span>
                    </div>

                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->