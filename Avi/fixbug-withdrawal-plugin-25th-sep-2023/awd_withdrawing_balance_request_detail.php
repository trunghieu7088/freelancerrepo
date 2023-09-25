<?php
$lastbank = get_user_meta(get_current_user_id(), 'last_payment_info', true);
//$lastbank = ($lastbank) ? $lastbank : 'bank';
//custom code here
$lastbank = ($lastbank) ? $lastbank : 'paypal';
//end
$min_amount = mje_format_price($awd->get_min_amount($lastbank));
$max_amount = ($awd->get_max_amount($lastbank)<0)?mje_format_price(0):mje_format_price($awd->get_max_amount($lastbank));
$min_day = (ae_get_option('awd_min_period') != false) ? ae_get_option('awd_min_period') : MJE_AWD_MIN_PERIOD;
$class_min_default = 'awd_min_val_' . $lastbank;
?>

<div class="withdraw-request-detail">
		<h4 class="withdraw-request-title"><?php _e('Request detail', 'mjeawd');?></h4>

		<div class="form-group">
			<div class="radio">
				<label for="withdraw-request-single">
					<input type="radio" name="withdraw-request" id="withdraw-request-single" class="awd-choose-withdraw" data-type="awd-single-withdraw" data-value="0" checked>
					<span><?php _e('Single Request', 'mjeawd');?></span>
				</label>
			</div>
			<div class="radio">
				<label for="withdraw-request-auto">
					<input type="radio" name="withdraw-request" id="withdraw-request-auto" class="awd-choose-withdraw" data-type="awd-auto-withdraw" data-value="1">
					<span><?php _e('Automated Request', 'mjeawd');?></span>
				</label>
			</div>
		</div>
		<input type="hidden" value="<?php echo $lastbank; ?>" id="awd_bank_meta" />
		<input type="hidden" value="<?php echo 0; ?>" id="awd_auto_meta" />
		<form class="et-form et-form-mobile withdraw-request-form awd-single-withdraw awd-request-widthdraw" onsubmit="return false">
			<div class="request-single-wrap ">
				<p><?php _e('Minimum withdraw:', 'mjeawd');?> <b class="min_bank_amount"><?php echo $min_amount; ?></b></p>
				<p><?php _e('Available Amount:', 'mjeawd');?> <b class="max_bank_amount"><?php echo $max_amount; ?></b></p>
				<div class="form-group-period">
					<div class="form-group ">
						<div class="input-group">
							<label for=""><?php _e('Withdraw Amount', 'mjeawd');?></label>
							<input type="text" class="form-control awd_min_val <?php echo $class_min_default; ?>" id="awd_single_amount_meta" name="awd_single_amount_meta">
							<span class="max-available max-available-click"><?php _e('Max Available', 'mjeawd');?></span>
						</div>
					</div>
				</div>
			</div>
			<button class="waves-effect waves-light btn-submit awd-save-request-preview" data-payment="awd-single-withdraw"><?php _e('Send', 'mjeawd');?></button>
		</form>

		<form class="et-form et-form-mobile withdraw-request-form awd-auto-withdraw awd-request-widthdraw " onsubmit="return false" style="display:none">
			<div class="request-auto-wrap">
				<p class=""><?php _e('The single requests will be automatically sent after the period of time you set.', 'mjeawd');?></p>
				<br/>
				<p><?php _e('Minimum Withdrawal:', 'mjeawd');?> <b class="min_bank_amount"><?php echo $min_amount; ?></b></p>
				<p><?php _e('Available Amount:', 'mjeawd');?> <b class="max_bank_amount"><?php echo $max_amount; ?></b></p>

				<p><?php _e('Minimum Expected Time:', 'mjeawd');?> <b><?php echo $min_day; ?> <?php _e('days','mjeawd');?></b></p>
				<div class="form-group-period">
					<div class="form-group form-group-period-days">
						<div class="input-group">
							<label for=""><?php _e('Period', 'mjeawd');?></label>
							<input type="text" class="form-control awd_min_period"  id="awd_period_meta" name="awd_period_meta">
							<span class="period-days"><?php _e('days','mjeawd');?></span>
						</div>
					</div>
					<div class="form-group ">
						<div class="input-group">
							<label for=""><?php _e('Withdrawal Amount', 'mjeawd');?></label>
							<input type="text" class="form-control awd_min_val <?php echo $class_min_default; ?>" id="awd_auto_amount_meta" name="awd_auto_amount_meta" >
						</div>
						<div class="checkbox">
							<label for="withdraw-available">
								<input type="hidden" name="awd_auto_all_meta" class="awd_auto_all_meta" id="awd_auto_all_meta" value="0">
								<input type="checkbox" name="awd_auto_all_check" class="awd_auto_all_check" id="withdraw-available">
								<span><?php _e('Withdraw all available balance', 'mjeawd');?></span>
							</label>
						</div>
					</div>
				</div>
			</div>
			<button class="waves-effect waves-light btn-submit awd-save-request-preview" data-payment="awd-auto-withdraw"><?php _e('Send', 'mjeawd');?></button>
	</form>
</div>
<div class="modal fade" id="awd_request_confirmation" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">
						<img src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt="">
					</span>
				</button>
                <h4 class="modal-title" id="myModalLabelForgot"><?php _e('Withdraw request confirmation', 'mjeawd');?></h4>
            </div>

            <div class="modal-body">
                <div class="withdraw-detail-modal" id="withdraw_preview">
                	<div class="loading"><div class="loading-img"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
	jQuery(window).load(function(){
		jQuery.validator.addClassRules({
			awd_min_period: {
				required: true,
				digits: true,
				min:<?php echo $min_day; ?>,
			},
		});

	<?php
$bank_actives = $awd->get_payments_active();
foreach ($bank_actives as $bankid => $bank_active) {
	?>
		jQuery.validator.addClassRules({
			awd_min_val_<?php echo $bankid; ?>: {
				required: true,
				number: true,
				isEmptyAccount: <?php echo ($awd->is_empty_payment_id(get_current_user_id(), $bankid)) ? 'true' : 'false'; ?>,
				notEnoughAmount: <?php echo ($awd->get_min_amount($bankid) > $awd->get_max_amount($bankid)) ? 'true' : 'false'; ?>,
				min:<?php echo $awd->get_min_amount($bankid); ?>,
				max:<?php echo $awd->get_max_amount($bankid) ?>,
			},
		});
		<?php
}
?>
	})
</script>