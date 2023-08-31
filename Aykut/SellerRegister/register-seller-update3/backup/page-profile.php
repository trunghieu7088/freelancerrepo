<?php
/**
 * Template Name: Page Profile
 */
global $wp_query, $ae_post_factory, $post, $current_user, $user_ID;
$user      = mJobUser::getInstance();
$user_data = $user->convert( $current_user->data );
$user_role = ae_user_role( $current_user->ID );

// Convert profile
$profile_obj = $ae_post_factory->get( 'mjob_profile' );
// Get user profile id
$profile_id = get_user_meta( $user_ID, 'user_profile_id', true );

// If user profile id is valid
if ( $profile_id ) {
	// Get profile
	$post = get_post( $profile_id );
	// If profile is valid
	if ( $post && ! is_wp_error( $post ) ) {
		// Check if user has profile or not
		if ( $post->post_author == $user_ID && $post->post_type == 'mjob_profile' ) {
			$profile = $profile_obj->convert( $post );
		} else {
			// Create a new profile, if user has not profile,
			$profile_id = wp_insert_post(
				array(
					'post_type'   => 'mjob_profile',
					'post_status' => 'publish',
					'post_title'  => $user_data->display_name,
					'post_author' => $user_data->ID,
				)
			);
			update_user_meta( $user_data->ID, 'user_profile_id', $profile_id );
			$profile = $profile_obj->convert( get_post( $profile_id ) );
		}
	}
	echo '<script type="text/json" id="mjob_profile_data" >' . json_encode( $profile ) . '</script>';
}
// Get profile infomation
$description          = ! empty( $profile->profile_description ) ? $profile->profile_description : __( 'There is no content', 'enginethemes' );
$payment_info         = ! empty( $profile->payment_info ) ? $profile->payment_info : __( 'There is no content', 'enginethemes' );
$billing_full_name    = ! empty( $profile->billing_full_name ) ? $profile->billing_full_name : __( 'There is no content', 'enginethemes' );
$billing_full_address = ! empty( $profile->billing_full_address ) ? $profile->billing_full_address : __( 'There is no content', 'enginethemes' );
$billing_country      = ! empty( $profile->billing_country ) ? $profile->billing_country : '';
$billing_vat          = ! empty( $profile->billing_vat ) ? $profile->billing_vat : __( 'There is no content', 'enginethemes' );

// custom code here :

$university = ! empty( $profile->university ) ? $profile->university : __( 'kein Inhalt', 'enginethemes' );

$major = ! empty( $profile->major ) ? $profile->major : __( 'kein Inhalt', 'enginethemes' );

$graduation_year = ! empty( $profile->graduation_year ) ? $profile->graduation_year : 'Graduation Year: None';

$academic_degree = ! empty( $profile->academic_degree ) ? get_term( $profile->academic_degree, 'degree' )->term_id : 'Degree : None';

// new line

$UBO_verification = ! empty( $profile->UBO_verification ) ? $profile->UBO_verification : 'Unverified';

$company_extract = ! empty( $profile->company_extract ) ? $profile->company_extract : 'Unverified';
// end line


// end

$registered_seller = get_post_meta( $profile_id, 'registered_seller', true );

if ( $registered_seller ) {
	if ( 'yes' !== get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
		// Create merchan account
		$country = $profile_obj->current_post->billing_country;
		if ( empty( $country ) && $profile_obj->current_post->country ) {
			$country = reset( $profile_obj->current_post->country );
		}

		$country_term = get_term( $country, 'country' );

		$country_code = null;
		if ( $country_term && ! is_wp_error( $country_term ) ) {
			$country_code = WPS_MjE_Online_Payment_Plateform()->get_country_code( $country_term->slug );
		}

		$args = array(
			'emailaddress' => $user_data->user_email,
			'notify_url'   => add_query_arg( 'opp_webhook', 'merchant', home_url() ),
			'return_url'   => add_query_arg( 'opp_profile', 'merchant', et_get_page_link( 'profile' ) ),
			'type'         => 'enterprise' === get_post_meta( $profile_id, 'seller_type', true ) ? 'business' : 'consumer',
		);

		$name_first = update_user_meta( $user_data->ID, 'first_name', true );
		if ( ! empty( $name_first ) ) {
			$args['name_first'] = $name_first;
		}

		$name_last = update_user_meta( $user_data->ID, 'last_name', true );
		if ( ! empty( $name_last ) ) {
			$args['name_last'] = $name_last;
		}

		if ( 'enterprise' === get_post_meta( $profile_id, 'seller_type', true ) ) {
			$args['coc_nr'] = get_post_meta( $profile_id, 'billing_vat', true );
		}

		if ( ! empty( $country_code ) ) {
			$args['country'] = $country_code;
		}

		$phone_number = get_post_meta( $profile_id, 'phone_number', true );

		if ( ! empty( $phone_number ) ) {
			$args['phone'] = $phone_number;
		} elseif ( ! empty( $user_data->phone ) ) {
			$args['phone'] = $user_data->phone;
		}

		$response = WPS_MjE_Online_Payment_Plateform()->create_merchant( $args );

		if ( siar( $response, 'uid' ) ) {
			update_user_meta( $user_data->ID, 'mje_opp_account_created', 'yes' );
			update_user_meta( $user_data->ID, 'mje_opp_merchant_uid', siar( $response, 'uid' ) );
			update_user_meta( $user_data->ID, 'mje_opp_account_creation_response', json_encode( $response ) );
			update_user_meta( $user_data->ID, 'mje_opp_account_compliance_response', json_encode( $response ) );

			// Store latest response
			$merchant_response = WPS_MjE_Online_Payment_Plateform()->get_merchant( siar( $response, 'uid' ) );
			update_user_meta( $user_data->ID, 'mje_opp_account_compliance_response', json_encode( $merchant_response ) );
		} else {
			update_user_meta( $user_data->ID, 'mje_opp_account_creation_error_response', json_encode( $response ) );
		}
	}

	if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) && 'yes' !== get_user_meta( $user_data->ID, 'mje_opp_bank_account_created', true ) ) {
		// Create bank account
		$args     = array(
			'uid'        => get_user_meta( $user_data->ID, 'mje_opp_merchant_uid', true ),
			'return_url' => add_query_arg( 'opp_profile', 'bank', et_get_page_link( 'profile' ) ),
			'notify_url' => add_query_arg( 'opp_webhook', 'bank_account', home_url() ),
		);
		$response = WPS_MjE_Online_Payment_Plateform()->create_merchant_bank_account( $args );

		if ( siar( $response, 'uid' ) ) {
			update_user_meta( $user_data->ID, 'mje_opp_bank_account_created', 'yes' );
			update_user_meta( $user_data->ID, 'mje_opp_bank_account_uid', siar( $response, 'uid' ) );
			update_user_meta( $user_data->ID, 'mje_opp_bank_account_creation_response', json_encode( $response ) );
		} else {
			update_user_meta( $user_data->ID, 'mje_opp_bank_account_creation_error_response', json_encode( $response ) );
		}
	}

	/*
	if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) && 'yes' !== get_user_meta( $user_data->ID, 'mje_opp_account_contact_created', true ) ) {
		$merchant_response = WPS_MjE_Online_Payment_Plateform()->get_merchant( get_user_meta( $user_data->ID, 'mje_opp_merchant_uid', true ), true );
		if ( siar( $merchant_response, 'uid' ) === get_user_meta( $user_data->ID, 'mje_opp_merchant_uid', true ) ) {
			update_user_meta( $user_data->ID, 'mje_opp_account_contact_created', 'yes' );
			//update_user_meta( $user_data->ID, 'mje_opp_account_contact_response', json_encode( $merchant_response ) );
			update_user_meta( $user_data->ID, 'mje_opp_account_compliance_response', json_encode( $response ) );
		}
	}*/
}

get_header();
?>

<?php if ( ! mje_is_user_active( $user_ID ) ) : ?>
	<div class="active-account">
		<p><?php _e( 'Your account is not activated yet! Lost the activation link?', 'enginethemes' ); ?> <a href="" class="resend-email-confirm"><?php _e( 'Resend it.', 'enginethemes' ); ?></a></p>
	</div>
<?php endif; ?>
<div id="content">
	<div class="container mjob-profile-page">
		<div class="row title-top-pages">
			<div class="col-xs-12">
				<p class="block-title"><?php _e( 'MY PROFILE', 'enginethemes' ); ?></p>
				<p><a href="<?php echo et_get_page_link( 'dashboard' ); ?>" class="btn-back"><i class="fa fa-angle-left"></i><?php _e( 'Back to dashboard', 'enginethemes' ); ?></a></p>    
			</div>
		</div>
		<div class="row profile">
			<div class="col-lg-4 col-md-4 col-sm-12 col-sx-12 block-items-detail profile">
				<?php get_sidebar( 'my-profile' ); ?>
			</div>

			<div class="col-lg-8 col-md-8 col-sm-12 col-sx-12">
				<div class="box-shadow block-profile">
					<div class="status-customer float-right" style="display: none">
						<select name="user_status" id="user_status" data-edit="user" class="user-status">
							<?php if ( $user_data->user_status == 'online' ) { ?>
								<option value="online" selected><?php _e( 'Online', 'enginethemes' ); ?></option>
								<option value="offline"><?php _e( 'Offline', 'enginethemes' ); ?></option>
							<?php } else { ?>
								<option value="online"><?php _e( 'Online', 'enginethemes' ); ?></option>
								<option value="offline" selected><?php _e( 'Offline', 'enginethemes' ); ?></option>
							<?php } ?>
						</select>
					</div>
									
					<div class="block-billing" style="margin-top:0px !important;margin-bottom: 50px;">
						<p class="title"><?php _e( 'INFORMATION', 'enginethemes' ); ?></p>
						<ul>

							<li style="margin-top:10px;">
								<div class="cate-title"><?php _e( 'Höchster Abschluss', 'enginethemes' ); ?></div>
								  <select class="input-item form-control" name="academic_degree" id="academic_degree" style="width:50%"  style="outline: none !important;border:none !important;box-shadow: none !important;border-bottom: 1px solid rgba(137, 138, 144, 0.2) !important;">
									<option value="">Bitte wählen Sie Ihren Abschluss</option>
									<?php
									$degrees_list = get_all_degress_for_posting_mjob();
									foreach ( $degrees_list as $degree => $item ) {
										if ( $item->term_id == $academic_degree ) {
											echo '<option selected="selected" value="' . $item->term_id . '">';
										} else {
											echo '<option value="' . $item->term_id . '">';
										}


										echo $item->name;
										echo '</option>';
									}
									?>
								 </select>
							</li>

							<li>
								<div class="cate-title"><?php _e( 'Fach', 'enginethemes' ); ?></div>
								<div id="major" class="info-content">
									<div class="text-content" data-type="input" data-name="major" data-id="#major"><p><?php echo $major; ?></p></div>
								</div>
							</li>


							<li>
								<div class="cate-title"><?php _e( 'Universität', 'enginethemes' ); ?></div>
								<div id="university" class="info-content">
									<div class="text-content" data-type="input" data-name="university" data-id="#university"><p><?php echo $university; ?></p></div>
								</div>
							</li>

						   
							<li style="margin-top:10px;">
								<div class="cate-title"><?php _e( 'Abschlussjahr', 'enginethemes' ); ?></div>
								 <select class="input-item form-control" style="width:50%"  name="graduation_year" id="graduation_year" style="outline: none !important;border:none !important;box-shadow: none !important;border-bottom: 1px solid rgba(137, 138, 144, 0.2) !important;width:50%;">
									 <option value="">Bitte wählen Sie ihr Abschlussjahr</option>
										 <?php
											for ( $x = date( 'Y' ); $x >= 1900; $x -= 1 ) {
												if ( $x == $graduation_year ) {
													echo '<option selected="selected" value="' . $x . '">';

												} else {
													echo '<option value="' . $x . '">';
												}
												echo $x;
												echo '</option>';
											}
											?>
										 </select>
							</li>

							
						</ul>
					</div>
	   

					<div class="block-intro">
						<p class="title"><?php _e( 'DESCRIPTION', 'enginethemes' ); ?></p>
						<div class="vote">
							<div class="rate-it star" data-score="<?php echo mje_get_total_reviews_by_user( $user_ID ); ?>"></div>
						</div>
						<div id="post_content" class="text-content-wrapper text-content">
							<div>
								<textarea class="editable" name="profile_description"><?php echo strip_tags( $description ); ?></textarea>
							</div>
						</div>
					</div>
					<style type="text/css">
					.profile .block-payment ul {
						padding: 0;
					}
					.profile .block-payment ul li {
						display: -webkit-box;
						display: -ms-flexbox;
						display: flex;
						-webkit-box-orient: horizontal;
						-webkit-box-direction: normal;
						-ms-flex-direction: row;
						flex-direction: row;
						-webkit-box-pack: start;
						-ms-flex-pack: start;
						justify-content: flex-start;
						margin-bottom: 10px;
					}
					.profile .block-payment ul li .cate-title {
						-ms-flex-preferred-size: 200px;
						flex-basis: 200px;
						color: #2a394e;
						font-family: 'Open Sans', sans-serif;
						font-weight: 700;
						padding: 5px 0;
						line-height: 15px;
					}
					.profile .block-payment ul li .textcontent a {
						cursor: pointer;
					}
					.profile .block-payment ul li .textcontent .statuscontenttext {
						min-width: 115px;
						display: inline-block;
					}
					</style>

					<!-- tran bao code -->
					<div class="block-payment">
					   <!-- <p class="title"><?php // _e('Verkäuferstatus', 'enginethemes'); ?></p> -->
						<!-- tran bao code  -->
						<h3 style="margin-bottom:35px !important;">Verkäuferstatus</h3>
						<h4 class="text-uppercase">register status</h4>
						<div class="block-payment" style="margin-bottom:50px;margin-top:30px;">
							 <ul>
							<?php
								$registered_seller = get_post_meta( $profile_id, 'registered_seller', true );
							if ( ! $registered_seller ) {
								?>
							<li style="margin-bottom:25px;">
								<div class="cate-title">Registered Seller
									<span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:16px;margin-left:5px;" class="fa fa-info-circle"></i></span>
								</div>
								<div class="info-content">      
									<strong>                             
									<i class="text-warning fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="text-warning text-uppercase"> Unregistered </span>
									
									&nbsp;&nbsp;&nbsp;&nbsp;
									 <a href="<?php echo site_url( 'register-seller-page' ); ?>">Register</a>
									  </strong>
								</div>                               
							</li>
						<?php } else { ?>
							<li style="margin-bottom:25px;">
								<div class="cate-title">Registered Seller 
									 <span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:16px;margin-left:5px;" class="fa fa-info-circle"></i></span>
								</div>
								<div class="info-content">     
									<strong>      
									<i class="text-success fa fa-check-circle" aria-hidden="true"></i>                        
									<span class="text-success text-uppercase"> Registered</span>
									  
									</strong>
									&nbsp;&nbsp;&nbsp;&nbsp;                                     
								</div>        
																
							</li>
						<?php } ?>
							 <li style="margin-bottom:25px;">
								<div class="cate-title">TradingForm
									 <span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:16px;margin-left:5px;" class="fa fa-info-circle"></i></span>
								</div>
								<div class="info-content">           
								<strong>
								<?php
								$seller_type = get_post_meta( $profile_id, 'seller_type', true );
								if ( $seller_type ) {
									?>
														
								<i class="text-info fa fa-user" aria-hidden="true"></i>
								  <span class="text-info text-uppercase"> 
									<?php
									if ( $seller_type == 'privateperson' ) {
										echo 'Private';
									}
									if ( $seller_type == 'enterprise' ) {
										echo 'Enterprise';
									}
									?>
									   
								  </span>
									<?php
								} else {
									?>
								<strong>                             
									<i class="text-warning fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="text-warning text-uppercase"> No information </span>
									</strong>
									<?php
								}

								?>
								</strong>                              
								</div>                                       
							</li>
						</ul>

						</div>
						<!-- tran bao code end -->

						<h4 class="text-uppercase" style="margin-bottom:30px;">Verifizierungsstatus</h4>
						<ul>
							<?php
							// account compliance
							$account_compliance_response = false;
							if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
								$account_compliance_response = get_user_meta( $user_data->ID, 'mje_opp_account_compliance_response', true );
								$account_compliance_response = json_decode( $account_compliance_response, true );
							}

							$opp_seller_status = siar( $account_compliance_response, 'status' );

							/*
							$is_seller_verified        = get_user_meta( $user_data->ID, 'mje_opp_seller_verified', true );
							$account_creation_response = false;
							if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
								$account_creation_response = get_user_meta( $user_data->ID, 'mje_opp_account_creation_response', true );
								$account_creation_response = json_decode( $account_creation_response, true );
							}*/
							?>
																					 
							<li>
								<div class="cate-title"><?php _e( 'Verified Seller (yes/no)', 'enginethemes' ); ?>
									
									 <span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:18px;margin-left:5px;" class="fa fa-info-circle"></i></span>
								</div>
								<div id="seller-verified" style="margin-left:30px;" class="info-content">
									<div class="textcontent" data-name="seller-verified" data-id="#seller-verified"><p>
									<?php
									if ( 'live' === $opp_seller_status ) {
										echo '<span class="statuscontenttext color-green">Verifiziert</span>';
									} elseif ( 'new' === $opp_seller_status ) {
										echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} elseif ( in_array( $opp_seller_status, array( 'terminated', 'suspended', 'blocked' ) ) ) {
										echo '<span class="statuscontenttext color-red">' . ucfirst( $opp_seller_status ) . '</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} elseif ( 'pending' === $opp_seller_status ) {
										echo '<span class="statuscontenttext color-orange">Ausstehend</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} else {
										echo '<span class="statuscontenttext color-red">Nein</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if ( 'live' !== $opp_seller_status && 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
										echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . siars( $account_compliance_response, 'compliance/overview_url' ) . '\'">Jetzt verifizieren</a>';
									}
									?>
									</p></div>
								</div>
							</li>
							<?php
							$opp_identity_status = siars( $account_compliance_response, 'contacts/0/status' );

							/*
							$is_opp_identity_verified = get_user_meta( $user_data->ID, 'mje_opp_identity_verified', true );
							$account_compliance_response = false;
							if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_contact_created', true ) ) {
								//$account_contact_response = get_user_meta( $user_data->ID, 'mje_opp_account_contact_response', true );
								$account_compliance_response = get_user_meta( $user_data->ID, 'mje_opp_account_compliance_response', true );
								$account_compliance_response = json_decode( $account_compliance_response, true );
							}*/
							?>
							<li style="padding-left:30px;">
								<div class="cate-title"><?php _e( 'Identity verified (yes/no)', 'enginethemes' ); ?></div>
								<div id="opp-verified" class="info-content">
									<div class="textcontent" data-name="opp-verified" data-id="#opp-verified"><p>
									<?php
									if ( 'verified' === $opp_identity_status ) {
										echo '<span class="statuscontenttext color-green">Verifiziert</span>';
									} elseif ( 'unverified' === $opp_identity_status ) {
										echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} elseif ( 'pending' === $opp_identity_status ) {
										echo '<span class="statuscontenttext color-orange">Ausstehend</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} else {
										echo '<span class="statuscontenttext color-red">Nein</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if ( 'verified' !== $opp_identity_status && 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
										echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . siars( $account_compliance_response, 'contacts/0/verification_url' ) . '\'">Jetzt verifizieren</a>';
									}
									?>
									</p></div>
								</div>
							</li>
							<?php
							$bank_account_response = false;
							if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_bank_account_created', true ) ) {
								$bank_account_response = get_user_meta( $user_data->ID, 'mje_opp_bank_account_updated_response', true );
								if ( empty( $bank_account_response ) ) {
									$bank_account_response = get_user_meta( $user_data->ID, 'mje_opp_bank_account_creation_response', true );
								}
								$bank_account_response = json_decode( $bank_account_response, true );
							}

							$opp_bank_account_status = siar( $bank_account_response, 'status' );

							/*
							$is_opp_bank_verified           = get_user_meta( $user_data->ID, 'mje_opp_bank_account_verified', true );
							$bank_account_creation_response = false;
							if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_bank_account_created', true ) ) {
								$bank_account_creation_response = get_user_meta( $user_data->ID, 'mje_opp_bank_account_creation_response', true );
								$bank_account_creation_response = json_decode( $bank_account_creation_response, true );
							}*/
							?>
							<li style="padding-left:30px;">
								<div class="cate-title"><?php _e( 'Bankkonto verifiziert', 'enginethemes' ); ?></div>
								<div id="opp-bank-verified" class="info-content">
									<div class="textcontent" data-name="opp-bank-verified" data-id="#opp-bank-verified"><p>
									<?php
									if ( 'approved' === $opp_bank_account_status ) {
										echo '<span class="statuscontenttext color-green">Verifiziert</span>';
									} elseif ( 'new' === $opp_bank_account_status ) {
										echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} elseif ( 'disapproved' === $opp_bank_account_status ) {
										echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} elseif ( 'pending' === $opp_bank_account_status ) {
										echo '<span class="statuscontenttext color-orange">Ausstehend</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									} else {
										echo '<span class="statuscontenttext color-red">Nein</span> &nbsp;&nbsp;&nbsp;&nbsp;';
									}

									if ( 'approved' !== $opp_bank_account_status && 'yes' === get_user_meta( $user_data->ID, 'mje_opp_bank_account_created', true ) ) {
										echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . siar( $bank_account_response, 'verification_url' ) . '\'">Jetzt verifizieren</a>';
									}
									?>
									</p></div>
								</div>
							</li>
							<li style="padding-left:30px;margin-bottom:25px;">								
								<div class="cate-title">UBO Verification 
									 <span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:16px;margin-left:5px;" class="fa fa-info-circle"></i></span>
								</div>
								<?php if ( $seller_type == 'enterprise' ) : ?>
									<div id="UBO_verification" class="info-content" style="width:50%;">
										<div class="textcontent" data-name="UBO_verification" data-id="#UBO_verification"><p>
										<?php
										$account_compliance_ubo = false;
										if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
											if ( siars( $account_compliance_response, 'compliance/requirements' ) ) {
												foreach ( siars( $account_compliance_response, 'compliance/requirements' ) as $compliance ) {
													if ( 'ubo.verification.required' === siar( $compliance, 'type' ) || 'ubo.required' === siar( $compliance, 'type' ) ) {
														$account_compliance_ubo = $compliance;
														break;
													}
												}
											}
										}

										if ( 'verified' === siar( $account_compliance_ubo, 'status' ) ) {
											echo '<span class="statuscontenttext color-green">Verifiziert</span>';
										} elseif ( 'unverified' === siar( $account_compliance_ubo, 'status' ) ) {
											echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
										} elseif ( 'new' === siar( $account_compliance_ubo, 'status' ) ) {
											echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
										} elseif ( 'pending' === siar( $account_compliance_ubo, 'status' ) ) {
											echo '<span class="statuscontenttext color-orange">Ausstehend</span> &nbsp;&nbsp;&nbsp;&nbsp;';
										} elseif ( $account_compliance_ubo ) {
											echo '<span class="statuscontenttext color-red">Nein</span> &nbsp;&nbsp;&nbsp;&nbsp;';
										}

										if ( 'verified' !== siar( $account_compliance_ubo, 'status' ) && siar( $account_compliance_ubo, 'object_redirect_url' ) ) {
											echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . siar( $account_compliance_ubo, 'object_redirect_url' ) . '\'">Jetzt verifizieren</a>';
										}
										?>
										</p></div>
									</div>
									<?php else : ?>
									<div style="background-color:#ddd;width:30%;height:30px;border-radius:5px;" id="UBO_verification" class="info-content"> 
									</div>
								<?php endif; ?>
							  </li>
							  <li style="padding-left:30px;margin-bottom:25px;">								
								 <div class="cate-title">Company Extract
									 <span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:16px;margin-left:5px;" class="fa fa-info-circle"></i></span>
								 </div>
								 <?php if ( $seller_type == 'enterprise' ) : ?>
										<div id="company_extract" class="info-content" style="width:50%;">
											<div class="textcontent" data-name="company_extract" data-id="#company_extract"><p>
												<?php
												$account_compliance_coc = false;
												if ( 'yes' === get_user_meta( $user_data->ID, 'mje_opp_account_created', true ) ) {
													if ( siars( $account_compliance_response, 'compliance/requirements' ) ) {
														foreach ( siars( $account_compliance_response, 'compliance/requirements' ) as $compliance ) {
															if ( 'coc_extract.verification.required' === siar( $compliance, 'type' ) || 'coc_extract.required' === siar( $compliance, 'type' ) ) {
																$account_compliance_coc = $compliance;
																break;
															}
														}
													}
												}

												if ( 'verified' === siar( $account_compliance_coc, 'status' ) ) {
													echo '<span class="statuscontenttext color-green">Verifiziert</span>';
												} elseif ( 'unverified' === siar( $account_compliance_coc, 'status' ) ) {
													echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
												} elseif ( 'new' === siar( $account_compliance_coc, 'status' ) ) {
													echo '<span class="statuscontenttext color-orange">Nicht verifiziert</span> &nbsp;&nbsp;&nbsp;&nbsp;';
												} elseif ( 'pending' === siar( $account_compliance_coc, 'status' ) ) {
													echo '<span class="statuscontenttext color-orange">Ausstehend</span> &nbsp;&nbsp;&nbsp;&nbsp;';
												} elseif ( $account_compliance_coc ) {
													echo '<span class="statuscontenttext color-red">Nein</span> &nbsp;&nbsp;&nbsp;&nbsp;';
												}

												if ( 'verified' !== siar( $account_compliance_coc, 'status' ) && siar( $account_compliance_coc, 'object_redirect_url' ) ) {
													echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . siar( $account_compliance_coc, 'object_redirect_url' ) . '\'">Jetzt verifizieren</a>';
												}
												?>
																						  
											</p></div>
										</div>
									<?php else : ?>
										 <div style="background-color:#ddd;width:30%;height:30px;border-radius:5px;" id="company_extract" class="info-content"></div>
								<?php endif; ?>							   								
							 </li>
							 <li style="padding-left:30px;">
								<div class="cate-title">Verified Phonenumber</div>
								<div class="info-content">
									<div class="textcontent">
										 <?php
											$phone_number = get_post_meta( $profile_id, 'phone_number', true );
											if ( ! $phone_number ) {
												?>
										   <p>
											<strong>
											<i class="text-warning fa fa-exclamation-triangle" aria-hidden="true"></i>
										   <span class="text-warning text-uppercase"> No information</span>
											  &nbsp;&nbsp;&nbsp;&nbsp;
											   <a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" href="<?php echo site_url( 'register-seller-page' ); ?>">Add</a>
										   </strong>
										   </p>
											 <?php } else { ?>
										<p>
											<span class="text-info">
												<strong><?php echo $phone_number; ?> </strong>
												 &nbsp;&nbsp;&nbsp;&nbsp;
												 <a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" href="#"  data-toggle="modal" data-target="#testModal">Update</a>
											</span>
										  
										  &nbsp;&nbsp;&nbsp;&nbsp;
										  
										</p>
										<?php } ?>
									</div>
								</div>
							</li>

							<li style="padding-left:30px;">
								<div class="cate-title"><?php _e( 'IBAN', 'enginethemes' ); ?></div>
								<div id="opp-bank-iban" class="info-content">
									<div class="textcontent" data-name="opp-bank-iban" data-id="#opp-bank-iban"><p>
									<?php
									if ( in_array( $opp_bank_account_status, array( 'pending', 'approved', 'disapproved' ) ) ) {
										echo siars( $bank_account_response, 'account/account_iban' );
										echo '&nbsp;&nbsp;&nbsp;&nbsp;';
										echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . WPS_MjE_Online_Payment_Plateform()->get_dashboard_url() . '\'">Auszahlungskonto ändern</a>';
									}

									/*
									if ( 'yes' === $is_opp_bank_verified ) {
										$bank_account_verified_response = get_user_meta( $user_data->ID, 'mje_opp_bank_account_verified_response', true );
										$bank_account_verified_response = json_decode( $bank_account_verified_response, true );
										echo siars( $bank_account_verified_response, 'account/account_iban' );
										echo '&nbsp;&nbsp;&nbsp;&nbsp;';
										echo '<a style="padding: 0px 15px; font-size: 10px; box-shadow: 1px 2px 3px rgba(94,141,147,0.7);" class="btn-submit waves-effect waves-light" onClick="window.location.href=\'' . WPS_MjE_Online_Payment_Plateform()->get_dashboard_url() . '\'">Auszahlungskonto ändern</a>';
									}*/
									?>
									</p></div>
								</div>
							</li>   
																								  
						</ul>    

						   <div class="block-billing" style="margin-top:30px !important;padding-left:30px;">
							<ul>
								<li>
									<div class="cate-title" style="padding-top:10px;flex-basis: 300px;">
										<?php _e( 'Umsatzsteuernummer', 'enginethemes' ); ?>      
										<span data-toggle="tooltip" data-placement="top" title="It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters"><i style="font-size:16px;margin-left:5px;" class="fa fa-info-circle"></i></span>                                             
									</div>

									<div id="billing_vat" class="info-content" style="padding-left:30px;">
										<div class="text-content"  data-type="input" data-name="billing_vat" data-id="#billing_vat" >
											<p><?php echo $billing_vat; ?></p>
										</div>
									</div>

								</li>
							</ul>
						   </div> 

						<?php
						/*
						?><div id="payment_info" class="text-content-wrapper text-content">
							<div>
								<textarea class="editable" name="payment_info"><?php echo $payment_info; ?></textarea>
							</div>
						</div><?php */
						?>
					</div>

					<div class="block-billing">
						<p class="title"><?php _e( 'BILLING INFO (PRIVATE)', 'enginethemes' ); ?></p>
						<ul>
							<li>
								<div class="cate-title"><?php _e( 'Business full name', 'enginethemes' ); ?></div>
								<div id="billing_full_name" class="info-content">
									<div class="text-content" data-type="input" data-name="billing_full_name" data-id="#billing_full_name"><p><?php echo $billing_full_name; ?></p></div>
								</div>
							</li>
							<li>
								<div class="cate-title full-address"><?php _e( 'Full Address', 'enginethemes' ); ?></div>
								<div id="billing_full_address" class="info-content text-content text-address">
									<textarea class="editable" name="billing_full_address"><?php echo $billing_full_address; ?></textarea>
								</div>
							</li>
							<li>
								<div class="cate-title"><?php _e( 'Country', 'enginethemes' ); ?></div>
								<div id="billing_country" class="info-content">
									<?php
									ae_tax_dropdown(
										'country',
										array(
											'id'         => 'billing_country',
											'name'       => 'billing_country',
											'class'      => 'chosen-single is-chosen',
											'hide_empty' => false,
											'show_option_all' => __( 'Select your country', 'enginethemes' ),
											'selected'   => (int) $billing_country,
										)
									);
									?>
								</div>
							</li>
							<!--
							<li>
								<div class="cate-title"><?php _e( 'VAT or Tax Number', 'enginethemes' ); ?></div>
								<div id="billing_vat"  class="info-content">
									<div class="text-content" data-type="input" data-name="billing_vat" data-id="#billing_vat"><p><?php echo $billing_vat; ?></p></div>
								</div>
							</li>
						-->
						</ul>
					</div>

					<!--<div class="block-connect-social">
						<p class="title"><?php _e( 'CONNECT TO SOCIALS', 'enginethemes' ); ?></p>
						<?php
						ae_render_connect_social_button();
						?>
					</div>-->
				</div>

			</div>
		</div>
	</div>
	<input type="hidden" class="input-item" name="_wpnonce" id="profile_wpnonce" value="<?php echo de_create_nonce( 'ae-mjob_post-sync' ); ?>" />
</div>

<!-- Modal -->
<div id="testModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Update PhoneNumber</h4>
	  </div>
	  <div class="modal-body">
		<form class="form-authentication et-form" id="updatephoneform">

				<input type="hidden" name="verifyID" id="verifyID" value="<?php echo get_current_user_id(); ?>">
			  <div class="inner-form signin-form" style="margin-top:0px;">
				<div class="form-group clearfix">
						<div class="input-group">
							<label for="user_login">New PhoneNumber</label>
							<input type="text" name="phonenumber" id="phonenumber" class="form-control">
						</div>


				</div>

				<div class="form-group clearfix">
					 <div class="sign-in-button float-left">
							<button class="text-uppercase btn-continue waves-effect waves-light btn-submit">Update</button>
						</div>
						
				</div>

			  </div>
		</form>
	  </div>

	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	  
	</div>

  </div>
</div>

<script type="text/javascript">
	(function ($) {
  $(document).ready(function () {

			 $('[data-toggle="tooltip"]').tooltip(); 

			  $("#updatephoneform").validate({
				  rules: 
				  {
					  phonenumber: {
								required: true,
								number: true,
							}
				  },
				   highlight:function(element, errorClass, validClass)
					{
										var $target = $(element );
										var $parent = $(element ).parent();
										$parent.addClass('has-error');
										$target.addClass('has-visited');
					},
									
					unhighlight:function(element, errorClass, validClass)
					{
										// position error label after generated textarea
										var $target = $(element );
										var $parent = $(element ).parent();
										$parent.removeClass('has-error');
										$target.removeClass('has-visited');
					},


					submitHandler: function(form,e) 
					{       
						e.preventDefault();  
						var verifyID=$("#verifyID").val();
						var phonenumber=$("#phonenumber").val();

						  $.ajax({

							type: "POST",
							url: ae_globals.ajaxURL,
							dataType: 'json',
							data: {
								action:'update_phonenumber_seller',                              
								verifyID: verifyID,                              
								phonenumber: phonenumber,
							},

							success: function (response) 
							{       
							console.log(response);
								if(response.data.confirm=='success')
								{
									toastr.success(response.data.message);
									window.location.href = response.data.redirect_url;
								}
								else
								{
									toastr.error(response.data.message);
								}
								
							
							}

							}); 
					   
				   }
							   
				});
		});
})(jQuery);
</script>

<?php
get_footer();
?>
