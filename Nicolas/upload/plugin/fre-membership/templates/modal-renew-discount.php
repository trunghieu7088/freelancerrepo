<style>
	.discountcodecustom
	{
		font-size:14px;
		padding:10px 15px;
		border-radius:5px;
		width:100%;
		border:1px solid #ddd !important;


	}
</style>
<?php
global $renewal_date;
$current_subscription=get_current_subscription_by_id_discount(get_current_user_id());
$plan_sku=$current_subscription->plan_sku;
$pack_type=$current_subscription->pack_type;
?>
		<div class="modal fade" id="modalDiscountRenew">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<i class="fa fa-times"></i>
						</button>
						<h4 class="modal-title"><?php _e("Apply Discount Code", 'enginethemes') ?></h4>
					</div>
					<div class="modal-body">
						<form role="form" id="renewdiscount_membership" class="fre-modal-form auth-form">
							<div class="fre-input-field">

		                		<p>Please enter the discount code to renew the current package</p>

		                	</div>
		                	<input type="hidden" name="plan_sku" value="<?php echo $plan_sku;?>">
		                	
		                	<input type="hidden" id="pack_type" name="pack_type" value="<?php echo $pack_type;?>">		                			               	
		                	<input type="hidden" id="user_id_discount" name="user_id_discount" value="<?php echo get_current_user_id(); ?>">
		                	<input type="hidden" name="customPlansku" id="customPlansku" value="<?php echo $plan_sku;?>">

		                	<input required="required" placeholder="100% Discount Code" type="text" id="discount_code" name="discount_code" class="discountcodecustom">

		                	<div class="fre-form-btn">
		                		<button type="submit" class="fre-normal-btn  discountsubmitbtn">
									<?php _e('Confirm', 'enginethemes') ?>
								</button>
								<span class="fre-form-close" data-dismiss="modal"><?php _e('Cancel', 'enginethemes') ?></span>
		                	</div>
						</form>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<style type="text/css">
	    	.subscription-info{
	    		font-size: 16px;
	    	}

	    	.subscription-info>p.pack-info{
			    font-size: 16px;
			    font-weight: 500;
			    color: #2c3e50;
			    line-height: 1.4em;


	    	}
	    	.subscription-info>p.pack-info span{
	    		display: inline-block;
			    min-width: 140px;
			    font-weight: 400;
			    font-style: italic;
			    color: #9da4a9;
			}



			.profile-freelance-wrap .fre-profile-box{
				margin-bottom: 22px;
			}
			.fre-membership-block .freelance-portfolio-add{
				min-height: 44px;
			}
			.fre-submit-cancelmembership{
				background-color: red !important;
				border-color:red !important;
			}
			.subscription-info i{
				font-weight: bold;
			}
			.subscription-info .pack-name-line {
			    border-bottom: 1px solid #ededed;
			    margin-left: -40px;
			    margin-right: -40px;
			    padding: 10px 40px 18px 40px;
			}
			.subscription-info .pack-name-line .member-value{
				font-weight: bold;
			}
			.subscription-info p.df-text-show{
				padding: 15px 0;
			}		
	    </style>