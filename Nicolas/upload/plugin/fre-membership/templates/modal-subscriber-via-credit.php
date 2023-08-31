<?php
global $pack;
$plan_title = '';
if( $pack){

$price = get_subscribe_price($pack->et_price);

 ?>
<div class="modal fade" id="modalSubscriberViaCredit">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<i class="fa fa-times"></i>
				</button>
				<h4 class="modal-title"><?php _e("Checkout using Credit Wallet", 'enginethemes') ?></h4>
			</div>
			<div class="modal-body">
				<form role="form" id="subcriber_credit" class="fre-modal-form auth-form subscriber_credit">
				<div class="fre-input-field">

	        		<?php
	        		if($pack->et_price > 0){ ?>
						<label class="fre-field-title"><?php printf( __('Once you click Subscribe, your credit balance will be deducted <strong id="custom_price3">%s</strong>.','enginethemes'), fre_price_format($price) );?></label>
						<?php
					}
					$available 	= is_subscriber_available();
	        		if ( $available ) {
	        			$plan       = membership_get_pack($available->plan_sku, $available->pack_type);
	        			if( $plan ){
	        				$plan_title = "<strong>{$plan->post_title}</strong>";
	        			}
	        			echo '<p class="waring-lost-posts"><i class="fa fa-exclamation-triangle"></i>';
	        			printf(__('Warning: You currently have %d posts(bids) in your current plan. They will be lost when you change to another subscription plan.','enginethemes'),$available->remain_posts);
	            		echo '</p>';
	        		} ?>
            	</div>
            	<?php if( ae_get_option('fre_credit_secure_code', true) ): ?>
					<div class="fre-input-field fre-input-secure-code">
						<div class="row">
							<div class="col-md-3 col-sm-4">
								<label class="fre-field-title"><?php _e('Your secure code:', 'enginethemes');?></label>
							</div>
							<div class="col-md-8 col-sm-8 ">
								<input tabindex="20" id="secureCode" type="password" size="20" name="secureCode"  required />
							</div>
						</div>
					</div>
				<?php endif; ?>

            	<input type="hidden" name="action" value="subscriberViaCredit">
            	<input type="hidden" name="sku" value="<?php echo $pack->sku;?>">
            	<?php //custom code here ?>
            	<input type="hidden" name="discount_code_id_credit" value="" id="discount_code_id_credit">
            	<?php //end ?>
            	<div class="fre-form-btn">
            		<button type="submit" class="fre-normal-btn ">
						<?php _e('Subscribe', 'enginethemes') ?>
					</button>
					<span class="fre-form-close" data-dismiss="modal"><?php _e('Cancel', 'enginethemes') ?></span>
            	</div>
			</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php } ?>