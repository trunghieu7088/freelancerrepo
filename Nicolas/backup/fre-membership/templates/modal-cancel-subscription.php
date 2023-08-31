<?php
global $renewal_date, $pack_type, $plan_sku;?>
		<div class="modal fade" id="modalCancelMembership">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<i class="fa fa-times"></i>
						</button>
						<h4 class="modal-title"><?php _e("Disable Auto Renewal", 'enginethemes') ?></h4>
					</div>
					<div class="modal-body">
						<form role="form" id="cancel_membership" class="fre-modal-form auth-form cancel_membership">
							<div class="fre-input-field">

		                		<p><?php printf(__('You subscription will be renewed at <strong>%s</strong>. Are you want to disable auto renewal?','enginethemes'), $renewal_date);?></p>

		                	</div>
		                	<input type="hidden" name="plan_sku" value="<?php echo $plan_sku;?>">
		                	<input type="hidden" name="pack_type" value="<?php echo $pack_type;?>">
		                	<input type="hidden" name="action" value="cancelMemberShip">

		                	<div class="fre-form-btn">
		                		<button type="submit" class="fre-normal-btn fre-submit-cancelmembership">
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