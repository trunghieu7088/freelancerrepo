<style>
	#discount_code_form label
	{
		color:#f44336;
		display: block;
	}
</style>
<?php
	global $pack,$user_ID;
	$sku  		= isset($_GET['sku']) ? $_GET['sku'] : '';
	$pack_type 	= get_pack_type_of_user($user_ID);
	$pack 		= membership_get_pack($sku, $pack_type);
	// var_dump($sku);
	// var_dump($pack_type);
	// var_dump($pack);
	if($pack){
		$has_pmgw 		= $is_subscribering  = $paypal_email = false;
		$enable_mebership_paypal 	= ae_get_option('enable_mebership_paypal', false);
		$enable_mebership_stripe 	= ae_get_option('enable_mebership_stripe', false);
		$stripe_api 				= fre_get_stripe_membership_api();
		$box_css 		= '';

		$subscribed 	= is_subscriber_available();
		if( $subscribed && ( ( $subscribed->plan_sku == $pack->sku) || ( $subscribed->price == 0 && $pack->et_price == 0 ) ) ) {
			$is_subscribering = 1;
			$box_css 	=  'box-can-not-resubscriber';
		}
		$enable_credit =  ae_get_option('user_credit_system', false) ;

		//custom code here
		$current_user_custom=wp_get_current_user();
		if(in_array('employer', $current_user_custom->roles))
		{
			$is_employer=true;
		}
		$is_employer=true;
		//end
		?>
		<div class="fre-verify-box col-left-verify  col-xs-12 ">
			<div class="titlle-heading">
				<h2><?php _e('Membership Plan Info','enginethemes');?></h2>
				<ul>
					<li> <?php printf(__('<span>Name:</span> <strong>%s</strong>','enginethemes'), $pack->post_title);?></strong>
					<li> <?php printf( __('<span>Price: </span><strong id="custom_price">%s</strong>','enginethemes'), fre_price_format($pack->et_price)) ;?></strong>
					<?php do_action('after_price_checkout_form', $pack);?>
					<li><span> <?php echo $pack->post_content;?></span></li>
				</ul>
				<?php do_action('after_checkout_form', $pack);?>
				
				<?php //custom code here
				 if ( $is_employer ) :

				?>
				<form id="discount_code_form" name="discount_code_form" class="fre-input-field">
					<input type="hidden" id="user_id_discount" name="user_id_discount" value="<?php echo get_current_user_id() ?>">
					<input type="hidden" id="discount_sku" name="discount_sku" value="<?php echo $pack->sku; ?>">
					<input type="text" name="discount_code" id="discount_code" placeholder="Discount code (optional)" style="border:1px solid #dbdbdb;border-radius:5px;height:48px;">
					<input type="submit" class="fre-btn" style="margin-top:5px;" name="apply_code" id="apply_code" value="Apply">
					<input type="button" class="fre-btn" style="display:none;margin-top:5px;" name="remove_discount" id="remove_discount" value="Remove">
				</form>
				<p id="text-discount-alert" name="text-discount-alert" style="display: none;color:#f44336;">
					the discount code is not valid
				</p>

				<p class="text-success text-bold" name="text-discount-info" id="text-discount-info" style="display: none;"></p>

				<p id="text-discount-success" class="text-success text-uppercase text-bold" name="text-discount-success" style="display: none;font-weight: 700;">					
				</p>

				<?php 
				endif; 
				//end
				?>

			</div>
		</div> <!-- end left !-->
		<div class="fre-verify-box fre-deposit-packages   col-right-verify col-xs-12 <?php echo $box_css;?>">
			<div class="titlle-heading">
				<h2><?php _e('Payment Method','enginethemes');?><br><span><?php _e('Select your preferred payment method','enginethemes');?></span></h2>
			</div>
			<ul id="fre-payment-accordion" class="fre-payment-list subscription-payments">
				<?php
				if($is_subscribering){
					echo '<li><h5>';_e('You are subscribed this plan so can not subscribe again.','enginethemes'); echo '</h5></li>';
				} else {
					do_action('membership_payment_gateway_checkout', $pack, $subscribed); ?>
				<?php }?>
			</ul>
		</div> <!-- end right!-->

	<?php } else {
		$page_id 	= ae_get_option('fre_membership_plans');
		$url 		= get_permalink($page_id);?>
		<div class="fre-verify-box fre-deposit-packages  col-xs-12 fre-pack-not-available">
			<div class="titlle-heading">
				<h2><?php _e('There are something wrong','enginethemes');?></h2>
				<h3><?php printf(__('This plan is not avaible. Select another plan and <a href="%s">get started</a>.','enginethemes'), $url);?></span></h3>
			</div>
		</div>
<?php } ?>