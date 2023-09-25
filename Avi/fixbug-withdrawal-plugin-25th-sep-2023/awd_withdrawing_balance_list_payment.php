<h4 class="withdraw-request-title"><?php _e('Withdrawal Account', 'mjeawd');?></h4>
<div class="withdraw-account mje-chosen-single et-form">
	<select class="chosen-single awd_choose_payment">
		<?php
$bank_actives = $awd->get_payments_active();
$bank_args = $bank_actives;
$lastbank = get_user_meta(get_current_user_id(), 'last_payment_info', true);
foreach ($bank_actives as $bankid => $bank_active) {
	//$selected = ($lastbank == $bankid) ? 'selected="selected"' : '';
	$selected = 'selected="selected"';
	?>
				<?php if($bankid !== 'bank') : ?>
				<option value="<?php echo $bankid; ?>" <?php echo $selected; ?>><?php echo $bank_active['args']['title']; ?></option>
				<?php endif; ?>
				<?php
}
?>
	</select>
</div>

<div class="withdraw-account-info" id="awd_show_payment_info">
	<?php // echo $awd->awd_load_payment_info(($lastbank) ? $lastbank : 'bank'); 
		//custom code here
		echo $awd->awd_load_payment_info(($lastbank) ? $lastbank : 'paypal');
		//end
	?>
</div>