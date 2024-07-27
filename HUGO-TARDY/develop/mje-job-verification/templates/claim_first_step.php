<div class="modal claim_step" id="claim_first_step" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<div class="contain_claim" id="contain_claim">
							<div class="header-claim">
								<div class="title-claim"><?php _e('STEP 1 : BASIC INFORMATION','mje_verification'); ?></div>
								<div class="sologon-claim"><?php _e('All information below are very important for us to make sure this mJob was exactly created by you','mje_verification'); ?></div>
							</div>
							<form class="form_claim" onsubmit="return false">
								<div class="form-input-claim">
									<div class="row">
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix contain-item-claim">
											<div class="item-claim"><i class="fa fa-user" aria-hidden="true"></i> <input name="new_name_meta" id="new_name_meta" class="input_claim" type="text" placeholder="<?php _e('Your name','mje_verification'); ?>" /></div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix contain-item-claim">
											<div class="item-claim"><i class="fa fa-skype" aria-hidden="true"></i> <input name="skype_meta" id="skype_meta" class="input_claim" type="text" placeholder="<?php _e('Your Skype ID','mje_verification'); ?>" /></div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix contain-item-claim contain-item-primary">
											<div class="item-claim"><i class="fa fa-envelope" aria-hidden="true"></i> <input name="pri_email_meta" id="pri_email_meta" class="input_claim" type="text" placeholder="<?php _e('Your primary email','mje_verification'); ?>" /></div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix contain-item-claim contain-item-alternate ">
											<div class="item-claim"><i class="fa fa-envelope-o" aria-hidden="true"></i> <input name="alt_email_meta" id="alt_email_meta" class="input_claim" type="text" placeholder="<?php _e('Your alternate email','mje_verification'); ?>" /></div>
											<input type="hidden" name="mjob_id" id="mjob_id" value="<?php echo $post->ID ?>" />
										</div>
									</div>
								</div>
								<!-- photo area --->
								<div class="photo-claim">
									<div class="row">
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix">
											<div class="item-claim">
												<div class="title-photo"><span><?php _e('Your Government Issued Photo ID','mje_verification'); ?></span></div>
												<?php echo ($ae_option->get_option('claim_government')<>"")?$ae_option->get_option('claim_government'):$mje_claim->claim_government();?>
												<div class="claim_upload_button">
													<div>
														<span class="plupload_buttons" id="claim_photo_container">
															<span class="img-gallery" id="claim_photo_browse_button">
																 <a class="choose_image btn-submit"><label for="my_userfile"><?php _e('Choose Photo','mje_verification'); ?></label></a>
															</span>
														</span>
													</div>
													<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'ad_carousels_et_uploader' ); ?>"></span>
												</div>
											</div>
										</div>
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix show-photo">
											<div class="item-claim">
												<div class="contain-photo-claim append-claim-del">
													<img class="show_photo_claim" src="<?php echo MJE_CLAIM_URL.'assets/images/scan_id.png'; ?>" />
													<!--<span class="del_photo_claim"><i class="fa fa-times" aria-hidden="true"></i></span>-->
													<input type="hidden" name="photo_meta" id="photo_meta" value="" required/>
												</div>

											</div>
										</div>
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 clearfix check-claim">
											<span><?php _e("I understand my mJob won't be verified if I haven't upload a valid government issued ID.",'mje_verification'); ?></span>
											<div class="packge-chose">
												<div class="checkbox">
													<label class="click_checkbox_claim">
														<input type="checkbox" value="" name="mjob_extra" class="check_box_claim" >
														<label style="display:none"></label>
														<span class="span_check uncheck"><?php _e("I agree to", 'mje_verification'); ?> <a href="<?php echo get_permalink(ae_get_option('claim_term')) ?>" target="_blank"><?php _e(" Terms of Service and Privacy Policy", 'mje_verification'); ?></a></span>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!--- button step --->
								<div class="control-button">
									<a class="btn-order btn-order-aside-bar waves-effect waves-light btn-submit next-step step-button"><?php _e('NEXT TO STEP 2 ', 'mje_verification'); ?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
								</div>
						    </form>
					</div>
				</div>
			</div>
		</div>
</div>