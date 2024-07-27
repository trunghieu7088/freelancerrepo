<?php
add_shortcode('mje_list_claims','mje_list_claims_function');
function mje_list_claims_function(){
	$mje_claim = new MJE_Claim;
	ob_start();
	?>
	<div class="contain-claim-list">
		<div class="right-title">
				<ul>
					<li><a class="click_filter_claim" data-page="1" data-status="" data-pav="true" ><?php _e( 'All', 'mje_verification' ) ?> (<?php echo $mje_claim->get_count_claims(); ?>)</a></li>
					<?php
					$arrs=$mje_claim->get_status_claim();
					foreach($arrs as $val=>$name){
					?>
						<li><a class="click_filter_claim <?php echo $val ?>" data-page="1" data-status="<?php echo $val; ?>" data-pav="true"><?php echo $name; ?> (<?php echo $mje_claim->get_count_claims($val); ?>)</a></li>
					<?php
					}
					?>
				</ul>
		</div>
		<div class="claim-list">
			<div class="list-content-claim">
				<div class="list-item">
					<?php $obj_claim=$mje_claim->get_claims();	?>
				</div>
				<div style="clear:both"></div>
				<?php
				if($obj_claim->numpage>1){
				?>
				<div class="claim-pav clearfix">
					<ul class="claim_pav">
						<?php
						for($i=1;$i<=$obj_claim->numpage;$i++){
						?>
							<li><a class="<?php echo ($i==1)?'':'click_filter_claim'; ?> page-numbers <?php echo ($i==1)?'active':''; ?>" data-page="<?php echo $i ?>" data-status=""><?php echo $i; ?></a></li>
						<?php
						}
						?>
						<li><a class="click_filter_claim" data-page="2" data-status=""><i class="fa fa-angle-double-right"></i></a></li>
					</ul>
				</div>
				<?php
				}
				?>
			</div>
			<div class="nothing-found-claim"  style="display:<?php echo (empty($obj_claim->datas))?'block':'none' ?>"<?php ?>>
				<p class="no-items"><?php echo _e('There are no job verification found!','mje_verification') ?></p>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}


add_shortcode('mje_claim_detail','mje_claim_detail_function');
function mje_claim_detail_function(){
	ob_start();
	global $ae_post_factory, $current_user, $user_ID;
	$ae_option=new AE_Options;
	$claim=get_post($_GET['id']);
    $mje_claim=new MJE_Claim;

	if($claim->post_type<>"mje_claims" || (!$mje_claim->is_admin() && $claim->post_author<>$user_ID)){
		?>
		<script>
			window.location="<?php echo home_url(); ?>";
		</script>
		<?php
		exit;
	}
	$mjob=get_post($claim->post_parent);
	$mjob_object = $ae_post_factory->get( 'mjob_post' );
	$current = $mjob_object->convert( $mjob );
	$scanid_url=wp_get_attachment_url( get_post_meta($claim->ID,'photo_meta',true) );

	$stt=$mje_claim->get_status_claim();

	?>
	<div class="contain-claim-detail row">
		<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12 clearfix contain-info-claim">
				<a href="<?php echo get_permalink($ae_option->get_option('claim_page')); ?>" class="btn-back claim-back"><i class="fa fa-angle-left"></i><?php _e('Back to job verification list','mje_verification'); ?></a>
				<div class="item-claim title-show"><?php _e('Microjob name & authors','mje_verification'); ?></div>
				<div class="row">
					<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 mjob-item clearfix mjob-info-claim">
						<div class="inner">
							<?php mje_get_template( 'template/mjob-item.php', array( 'current' => $current) ); ?>
						</div>
					</div>
				<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12 clearfix mjob-item claim-info-claim">
					<div class="inner row">
						<div id="notice_decison"><?php echo $mje_claim->notice_decision($claim->ID); ?></div>
						<div class="col-lg-7 col-md-7 col-sm-7 col-xs-12 clearfix">
							<div class="claim-personal-profile">
								<div class="claim-avata"><?php echo mje_avatar($claim->post_author, 70);	?></div>
								<div class="claim-user-info">
									<?php
									$name_claim =  get_post_meta($claim->ID,'new_name_meta',true);
									$cut_name=$mje_claim->cutstr($name_claim,70,' ...');
									if(strlen($name_claim )>strlen($cut_name)){
										?>
										<div class="claim-name" data-toggle="tooltip" data-placement="top" title="<?php echo $name_claim; ?>"><?php echo $cut_name ?></div>
										<?php
									}else{
									?>
									<div class="claim-name"><?php echo $name_claim ?></div>
									<?php
									}
									?>
									<div class="claim-desc"><a class="link_to_profile" href="<?php echo get_author_posts_url($claim->post_author); ?>" target="_blank"><?php echo _e('View profile','mje_verification'); ?></a></div>
								</div>
							</div>
							<div class="clearfix"></div>
							<hr />
							<div class="claim-meta-info">
								<ul>
									<li><i class="fa fa-envelope" aria-hidden="true"></i> <?php echo get_post_meta($claim->ID,'pri_email_meta',true) ?></li>
									<li><i class="fa fa-envelope-o" aria-hidden="true"></i> <?php echo get_post_meta($claim->ID,'alt_email_meta',true) ?></li>
									<li><i class="fa fa-skype" aria-hidden="true"></i> <?php echo get_post_meta($claim->ID,'skype_meta',true) ?></li>
								</ul>
							</div>
							<hr />

							<div class="modal fade" id="popup_history" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
											<div class="modal-body">
												  <ul class="nav nav-tabs" role="tablist">
													<li role="presentation" class="active popup_status">
														<a href="#tab_status_history" aria-controls="tab_history" role="tab" data-toggle="tab"><?php _e('Status history','mje_verification'); ?></a>
													</li>
													<li role="presentation" class="popup_status">
														<a href="#tab_price_history" aria-controls="profile" role="tab" data-toggle="tab"><?php _e('Price history','mje_verification'); ?></a>
													</li>
												  </ul>
												  <div class="tab-content">
													<div role="tabpanel" class="tab-pane active" id="tab_status_history">
														<?php echo $mje_claim->get_claim_log_status($claim->ID); ?>
													</div>
													<div role="tabpanel" class="tab-pane" id="tab_price_history">
														<?php echo $mje_claim->get_claim_log_price($claim->ID);	?>
													</div>
												  </div>
											</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-5 col-md-5 col-sm-5 col-xs-12 clearfix">
							<div class="contains_canid_profile">
								<div class="scanid_profile">
									<a class="click_modal_photo_claim" data-toggle="modal" data-target="#popup_scanid" >
										<img class="show_photo_claim" src="<?php echo $scanid_url; ?>">
									</a>
								</div>
							</div>
							<div class="modal fade" id="popup_scanid" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-body">
											<img src="<?php echo $scanid_url; ?>">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 clearfix">
							<div class="claim-full">
									<a class="click_modal_photo_claim" data-toggle="modal" data-target="#popup_history"><?php _e('View changes','mje_verification') ?></a>
							</div>
						</div>
					</div>
				</div>
				</div>
		</div>

		<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 clearfix action-info-claim">
				<div class="item-claim title-show"><?php echo _e(' ','mje_verification'); ?></div>
				<div class="contain-action-claim">
					<div class="control-action-claim">
						<?php
							if($mje_claim->is_admin()){
						?>
							<ul>
								<li><?php echo $mje_claim->button_decision($claim->ID,'mje_verifying') ?></li>
								<li><?php echo $mje_claim->button_decision($claim->ID,'mje_approved') ?></li>
								<li class="or_center"><span><?php _e('OR','mje_verification');?></span></li>
								<li><?php echo $mje_claim->button_decision($claim->ID,'mje_declined') ?></li>
							</ul>
						<?php
						}
						?>
					</div>
				</div>
				<?php
				if($mje_claim->is_admin()){
				?>
				<div class="modal fade" id="popup_reason_decline" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil" aria-hidden="true"></i>  <?php echo _e('Write to ','mje_verification').get_post_meta($claim->ID,'new_name_meta',true); ?></h4>
							</div>
							<div class="modal-body">
								<div class="title-reason-popup">
									<label><?php echo _e('Please give a reason decline','mje_verification'); ?></label>
								</div>
								<form class="form_claim_descision">
									<textarea id="decline_reason_meta" name="decline_reason_meta" maxlength="500" class="popop_reason_textarea" required></textarea>
								</form>
								<div class="character-left-contain">
									<span><?php echo _e('Character left','mje_verification'); ?>: <span class="left_char">500</span></span>
								</div>
								<div class="button-confirm-reason">
									<button type="button" class="<?php mje_button_classes(  array( 'submit-payment', 'waves-effect', 'waves-light','action-button-claim' ) ); ?> claim_decision" data-id="<?php echo $claim->ID; ?>" data-status="mje_declined"><?php _e('Confirm', 'mje_verification' );  ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				}
				?>

		</div>
	</div>
	<div class="displaynone"><div id="hide_me"></div></div>
	<?php
	return ob_get_clean();
}
?>