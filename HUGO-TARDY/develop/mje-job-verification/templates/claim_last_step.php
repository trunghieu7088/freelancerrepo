<div class="modal claim_step" id="claim_last_step" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<div class="contain_claim" id="contain_claim">
							<div class="header-claim">
								<div class="title-claim"><?php _e('STEP 2 : REVIEW YOUR INFO','mje_verification'); ?></div>
								<div class="sologon-claim"><?php _e('To complete the request, please make sure all the information below is correct','mje_verification'); ?></div>
							</div>

							<div class="user_info_claim">
								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix">
										<div class="item-claim title-show"><?php _e('YOUR GOVERNMENT ID','mje_verification'); ?></div>
										<div class="show_photo-claim">
											<img class="show_photo_claim" src="<?php echo MJE_CLAIM_URL.'assets/images/scan_id.png'; ?>" />
										</div>
									</div>
									<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix">
										<div class="item-claim title-show">
											<div class="left_title"><?php _e('PERSONAL INFORMATION','mje_verification'); ?></div>
											<div class="right_title"><?php _e('STATUS','mje_verification'); ?></div>
										</div>
										<hr class="ct_hr" />

										<div class="info-meta-claim">
											<div class="item-meta item-meta-name">
												<div class="meta-name"><?php _e('Name','mje_verification'); ?>:</div>
												<div class="item-claim">
													<div class="left_title new_name_meta"></div>
													<div class="right_title"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
												</div>
											</div>
											<div class="item-meta item-meta-primary-email">
												<div class="meta-name"><?php _e('Primary email','mje_verification'); ?></div>
												<div class="item-claim">
													<div class="left_title pri_email_meta"></div>
													<div class="right_title"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
												</div>
											</div>
											<div class="item-meta item-meta-alternate-email">
												<div class="meta-name"><?php _e('Alternate email','mje_verification'); ?>:</div>
												<div class="item-claim">
													<div class="left_title alt_email_meta"></div>
													<div class="right_title"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
												</div>
											</div>
											<div class="item-meta item-meta-skype">
												<div class="meta-name"><?php _e('Skype ID','mje_verification'); ?>:</div>
												<div class="item-claim">
													<div class="left_title skype_meta"></div>
													<div class="right_title"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
												</div>
											</div>
										</div>

									</div>
								</div>
							</div>
							<hr />
							<div class="mjob_info_claim">
								<?php
								$currency = ae_get_option('currency', array(
									'align' => 'left',
									'code' => 'USD',
									'icon' => '$',
								));
								$icon = $currency['icon'];

								?>
								<div class="row">
									<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 clearfix">
										<div class="item-claim title-show"><?php _e('Microjob name','mje_verification'); ?></div>
										<div class="mjob-name"><?php echo $post->post_title; ?></div>
									</div>
									<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 clearfix list-claim-price">
											<div class="row">
                                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-4 clearfix bt_center item-claim-price">
                                                    <div class="item-claim item-name title-show"><?php _e('Mjob price','mje_verification'); ?></div>
                                                    <div class="item-claim item-value update_price_mjob"><sup><?php echo $icon;?></sup><?php echo number_format($price,2); ?></div>
                                                </div>
												<!-- custom code 18th Jun 2024 -->
                                                <!-- <div class="col-lg-6 col-md-6 col-sm-4 col-xs-4 clearfix bt_center item-claim-price">
                                                    <div class="item-claim item-name title-show"><?php _e('Job verification fee (%)','mje_verification'); ?></div>
                                                    <div class="item-claim item-value update_fee_claim"><?php echo $fee; ?>%</div>
                                                </div> -->
												
                                                <div class="col-lg-6 col-md-6 col-sm-4 col-xs-4 clearfix bt_center item-claim-price">
                                                    <div class="item-claim item-name title-show"><?php _e('Job verification price','mje_verification'); ?></div>
                                                    <div class="item-claim item-value total-value update_price_claim"><sup><?php echo $icon;?></sup><?php echo number_format($price_claim,2); ?></div>
                                                </div>
												<!-- end custom code 18th Jun 2024 -->
                                            </div>
									</div>
								</div>
							</div>

							<div class="control-button-show">
									<a class="btn-order btn-order-aside-bar waves-effect waves-light btn-submit submit-step submit-step-claim step-button">
										<?php $pay_text=($fee==0)? __('SUBMIT','mje_verification'):__('Pay for this request','mje_verification');	_e($pay_text, 'mje_verification'); ?>
									</a>
							</div>

							<div class="back-button-show">
									<a class="back-step-claim"><i class="fa fa-long-arrow-left" aria-hidden="true"></i>  <?php _e(' Forget something? Back to Step 1', 'mje_verification'); ?></a>
							</div>

					</div>
				</div>
			</div>
		</div>
</div>