<?php
class MJE_Claim_User extends AE_Base
{

	public function __construct()
	{
		$this->add_action('mje_after_user_dropdown_menu', 'mje_after_user_dropdown_menu_function');
		$this->add_action('mje_after_user_sidebar_menu', 'mje_after_user_sidebar_menu_function');
		$this->add_action('mje_seller_mjob_button', 'mje_seller_mjob_button_function');
		$this->add_action('wp_head', 'create_ae_carousel_claim_template');
		$this->add_action('wp_head', 'create_ae_list_claim_template');
		$this->add_ajax('mje_claim_sync', 'mje_claim_sync');
		$this->add_ajax('mje_listclaim_fetch', 'mje_listclaim_fetch');
		$this->add_ajax('decision_claim', 'decision_claim');
		$this->add_ajax('update_price_claim', 'update_price_claim');
		$this->add_filter('ae_convert_mjob_post', 'add_filter_mjob');
		$this->add_action('mje_checkout_custom_product', 'mje_checkout_custom_product_function');
		$this->add_action('mje_action_after_update_mjob', 'mje_action_after_update_mjob');
		$this->add_action('mje_other_type_notification', 'mje_other_type_notification');
		$this->add_filter('post_type_link', 'change_permalink_claim_detail', 10, 2);
		$this->add_action('mje_mjob_item_before_rating', 'mje_mjob_item_before_rating_function');
		$this->add_action('mje_mjob_item_js_before_rating', 'mje_mjob_item_js_before_rating_function');
		$this->add_action('mje_generate_customize_css', 'mje_generate_customize_css_function', 10, 2);
		$this->add_filter('mje_checkout_product_types', 'accept_product');
		$this->add_filter('mje_checkout_response_data', 'checkout_claim');
		$this->add_action('mje_after_process_payment', 'mje_after_process_payment_function', 10, 2);
		$this->add_filter('show_text_button_process_payment', 'show_text_button_process_payment_function', 10, 2);
		$this->add_action('mje_template_search_advance_after', 'mje_template_search_advance_after_function', 99, 2);
		$this->add_filter('mje_mjob_filter_query_args', 'mje_mjob_filter_query_args_function');
		$this->add_filter('mje_mjob_search_in_url', 'mje_mjob_search_in_url_function');
	}
	function mje_mjob_search_in_url_function($query)
	{
		$claim = new MJE_Claim;
		if (isset($_GET['verified'])) {
			$query->query_vars['post__in'] =  $claim->get_verififies() ? $claim->get_verififies() : array('0');
		}
		return $query;
	}

	function mje_mjob_filter_query_args_function($query_args)
	{
		$claim = new MJE_Claim;
		$query = $_REQUEST['query'];
		$advance = array();
		if (isset($query['verified']) && $query['verified'] == "true") {
			$advance['post__in'] =  $claim->get_verififies() ? $claim->get_verififies() : array('0');
		}
		$query_args  = wp_parse_args($query_args, $advance);
		return $query_args;
	}
	function mje_template_search_advance_after_function()
	{
		ob_start();
		$checked = (isset($_GET['verified'])) ? 'checked="checked"' : '';
?>
		<div class="filter-by-verified advanced-filters-item form-group">
			<div class="checkbox">
				<label><input type="checkbox" name="verified" <?php echo $checked; ?>> <span><?php _e('Verified', 'mje_verification'); ?></span></label>
			</div>
		</div>
		<?php
		echo ob_get_clean();
	}
	function show_text_button_process_payment_function($content, $ad)
	{
		ob_start();
		if ($ad->post_type == "mje_claims") {
		?>
			<a href="<?php echo get_the_permalink($ad->ID) ?>" class="<?php mje_button_classes(array()); ?>">
				<?php _e('Visit your job verification request', 'mje_verification'); ?>
				<i class="fa fa-long-arrow-right" aria-hidden="true"></i>
			</a>
		<?php
		} else {
			echo $content;
		}
		return ob_get_clean();
	}

	function update_price_claim()
	{
		$id = $_POST['id'];
		$ae_option = new AE_Options;
		$price = get_post_meta($id, 'et_budget', true);
		$fee = ($ae_option->get_option('claim_fee') <> "") ? $ae_option->get_option('claim_fee') : 10;
		$price_claim = round($price * ($fee / 100), 2);
		?>
		<script>
			//jQuery(".update_price_mjob").html('<sup>$</sup><?php echo number_format($price, 2); ?>');
			jQuery(".update_fee_claim").html('<?php echo $fee; ?>%');
			//jQuery(".update_price_claim").html('<sup>$</sup><?php echo number_format($price_claim, 2); ?>');
		</script>
		<?php
		exit;
	}

	function mje_after_process_payment_function($payment_return, $data)
	{
		$mje_claim = new MJE_Claim;
		$ae_option = new AE_Options;
		$order = $payment_return['order'];
		$order_data = $order->get_order_data();
		$id = $order_data['product_id'];
		$claim = get_post($id);


		if ($claim->post_type == "mje_claims") {

			$price = (get_post_meta($claim->post_parent, 'et_budget', true)) ? get_post_meta($claim->post_parent, 'et_budget', true) : 0;
			$fee = get_post_meta($claim->ID, 'claim_fee', true);
			$total = (float)round($price * ($fee / 100), 2);

			update_post_meta($id, 'payment_meta', $order_data['payment']);
			update_post_meta($id, 'mjob_price', $price);
			update_post_meta($id, 'invoice_meta', $order_data['ID']);
			$mje_claim->update_post_status($id, 'mje_pending');
			//add noti for admin

			$notify_code = 'type=user_send_claim&claim_id=' . $id;
			$admins = get_users(array('role' => 'administrator'));

			$query = new WP_Query(
				array(
					'post_type'              => 'mje_notification',
					'title'                  => $notify_code,
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				)
			);

			if (empty($query->post)) {
				foreach ($admins as $admin) {
					$args = array(
						'post_type' => 'mje_notification',
						'post_status' => 'unread',
						'post_author' => $admin->ID,
						'post_title' => $notify_code,
						'post_content' => $notify_code
					);
					wp_insert_post($args);
				}
			}

			//start mail here

			$fullname = get_post_meta($id, 'new_name_meta', true);
			$skype = get_post_meta($id, 'skype_meta', true);
			$pri_mail = get_post_meta($id, 'pri_email_meta', true);
			$alt_mail = get_post_meta($id, 'alt_email_meta', true);

			$total = ($order_data['products'][$id]['AMT']);

			$headers = 'From: ' . $fullname . ' <' . $pri_mail . '>' . "\r\n";
			$headers .= "Reply-To: $pri_mail " . "\r\n";
			$headers = apply_filters('ae_inbox_mail_headers', $headers);

			$subject = sprintf(__('[%s]New Message From Claim', 'mje_verification'), get_bloginfo('blogname'));

			$cash_mail = ($ae_option->get_option('claim_mail_payment_cash')) ? $ae_option->get_option('claim_mail_payment_cash') : $mje_claim->claim_mail_cash();
			$pay_mail = ($ae_option->get_option('claim_mail_payment')) ? $ae_option->get_option('claim_mail_payment') : $mje_claim->claim_mail();

			$message = ($order_data['payment'] == "cash") ? $cash_mail : $pay_mail;

			$message = str_replace('[display_name]', $fullname, $message);
			$message = str_replace('[user_skype]', $skype, $message);
			$message = str_replace('[user_email]', $pri_mail, $message);
			$message = str_replace('[user_email_alt]', $alt_mail, $message);
			$message = str_replace('[link_detail]', '<a href="' . get_permalink($id) . '">' . get_permalink($id) . '</a>', $message);
			$message = str_replace('[invoice_id]', $order_data['ID'], $message);
			$message = str_replace('[date]', date(get_option('date_format'), strtotime($order_data['created_date'])), $message);
			$message = str_replace('[payment]', $order_data['payment'], $message);
			$message = str_replace('[total]', mje_shorten_price($total), $message);
			$message = str_replace('[blogname]', get_bloginfo('blogname'), $message);

			$mail = new MJE_Mailing;
			$mail->wp_mail($pri_mail, $subject, $message, $headers);


			//end send mail
		}
		return false;
	}

	public function accept_product($types)
	{
		array_push($types, array('mje_claims'));
		return $types;
	}

	public function checkout_claim($request)
	{
		$product = get_post($request['p_data']['post_parent']);
		$price = get_post_meta($product->post_parent, 'et_budget', true);
		$fee = get_post_meta($product->ID, 'claim_fee', true);
		$total = (float)round($price * ($fee / 100), 2);

		$resp = array();
		$claim = get_post($request['p_data']['post_parent']);
		$claim->amount = $total;
		$resp['data'] = $claim;
		return $resp;
	}

	public function mje_generate_customize_css_function($ae_customizer, $colors)
	{

		if ($colors['primary_color'] == "default_primary_color") {
		?>
			ul.claim_pav li a.active, ul.claim_pav li a:hover, .claim-meta-info ul li i, .title-claim, .item-claim i, #claim_first_step span.uncheck:after,#claim_first_step span.checked:after, .item-value, .photo-claim li:before, .character-left-contain{ color:#10a2ef;}

			#popup_history li.active.popup_status, .choose_image, span.can_claim_enable .tooltip-inner, .claim-list .tooltip-inner{ background:#10a2ef;}

			#claim_first_step span.uncheck:before, #claim_first_step span.checked:before, textarea#decline_reason_meta{ border-color:#10a2ef;}

			.form-input-claim .input_claim:focus{ border-bottom-color:#10a2ef;}

			span.can_claim_enable .tooltip.top .tooltip-arrow, .claim-list .tooltip.top .tooltip-arrow{ border-top-color:#10a2ef;}
		<?php
		}

		$ae_customizer::ae_customize_generate_css(
			'ul.claim_pav li a.active, ul.claim_pav li a:hover, .claim-meta-info ul li i, .title-claim, .item-claim i,
		         #claim_first_step span.uncheck:after,#claim_first_step span.checked:after, .item-value, .character-left-contain, .photo-claim li:before',
			'color',
			$colors['primary_color']
		);

		$ae_customizer::ae_customize_generate_css(
			'#popup_history li.active.popup_status, .choose_image, span.can_claim_enable .tooltip-inner, .claim-list .tooltip-inner',
			'background',
			$colors['primary_color']
		);
		$ae_customizer::ae_customize_generate_css(
			'#claim_first_step span.uncheck:before, #claim_first_step span.checked:before, textarea#decline_reason_meta',
			'border-color',
			$colors['primary_color']
		);
		$ae_customizer::ae_customize_generate_css(
			' .form-input-claim .input_claim:focus',
			'border-bottom-color',
			$colors['primary_color']
		);

		$ae_customizer::ae_customize_generate_css(
			'span.can_claim_enable .tooltip.top .tooltip-arrow, .claim-list .tooltip.top .tooltip-arrow',
			'border-top-color',
			$colors['primary_color']
		);
	}

	public function mje_mjob_item_before_rating_function($post)
	{
		$mje_claim = new MJE_Claim;
		ob_start();

		if ($mje_claim->is_verified($post->ID)) {
		?>
			<span class="can_is_verified">
				<i class="fa fa-check-circle" aria-hidden="true"></i>
				<span><?php _e('verified', 'mje_verification') ?></span>
			</span>
			<?php
		} else {
			if (is_page_template('page-my-listing-jobs.php') || is_page_template('page-dashboard.php')) {
				if ($mje_claim->can_claim($post->ID)) {
			?>
					<a href="<?php echo get_permalink($post->ID) ?>">
						<span class="can_claim_enable">
							<span data-toggle="tooltip" data-placement="top" title="<?php _e('Click to submit request', 'mje_verification') ?>">
								<i class="fa fa-trophy" aria-hidden="true"></i>
								<span><?php _e('Verify job', 'mje_verification') ?></span>
							</span>
						</span>
					</a>
		<?php
				}
			}
		}

		echo ob_get_clean();
	}
	public function mje_mjob_item_js_before_rating_function()
	{
		?>
		<# if(is_verified) { #>
			<span class="can_is_verified">
				<i class="fa fa-check-circle" aria-hidden="true"></i>
				<span><?php _e('verified', 'mje_verification') ?></span>
			</span>
			<# } else { #>
				<# if(is_claim) { #>
					<a href="{{= permalink }}">
						<span class="can_claim_enable">
							<span data-toggle="tooltip" data-placement="top" title="<?php _e('Click to submit request', 'mje_verification') ?>">
								<i class="fa fa-trophy" aria-hidden="true"></i>
								<span><?php _e('Verify job', 'mje_verification') ?></span>
							</span>
						</span>
					</a>
					<# } #>
						<# } #>

							<?php
						}

						public function mje_action_after_update_mjob($data)
						{
							if ($data['new'] <> $data['old'] and $data['old'] <> 0 and $data['new'] <> 0) {
								$arrs = get_posts(array(
									'post_type' => 'mje_claims',
									'posts_per_page' => -1,
									'post_parent' => $data['ID'],
									'post_status' => 'mje_pending,mje_verifying,mje_approved'
								));
								if (!empty($arrs)) {
									foreach ($arrs as $arr) {
										$logs = get_post_meta($arr->ID, 'price_history_meta', true);
										$logs = ($logs == "" or $logs == "[]") ? array() : (array)json_decode($logs);
										$logs[] = array('price' => $data['old'] . ' -> ' . $data['new'], 'date' => current_time('timestamp'));
										update_post_meta($arr->ID, 'price_history_meta', json_encode($logs));
										update_post_meta($arr->ID, 'notice_meta', 1);
									}
								}
							}
						}
						// add more var for in convert post
						public function add_filter_mjob($mjob_post)
						{
							// Check claim
							$mje_claim = new MJE_Claim;
							$mjob_post->is_claim = $mje_claim->can_claim($mjob_post->ID);
							$mjob_post->is_verified = $mje_claim->is_verified($mjob_post->ID);
							$mjob_post->mjob_class .= ($mje_claim->can_claim($mjob_post->ID)) ? ' claim_enable' : '';
							return $mjob_post;
						}

						public function mje_listclaim_fetch()
						{
							$ae_option = new AE_Options;
							$mje_claim = new MJE_Claim;
							$link_claim = ($ae_option->get_option('claim_page_detail')) ? get_page_link($ae_option->get_option('claim_page_detail')) : '';
							$obj_claim = $mje_claim->get_claims($_POST['status'], $_POST['page']);
							$arrs = $obj_claim->datas;
							$datas = [];
							foreach ($arrs as $arr) {
								$mjob = get_post($arr->post_parent);
								$stt = $mje_claim->get_status_claim_full($arr->ID);
								$author = get_user_by('id', $arr->post_author);
								$datas[] = array(
									'mjob_link' => get_permalink($mjob->ID),
									'mjob_src' => get_the_post_thumbnail_url($mjob->ID, 'thumbnail'),
									'mjob_src_medium' => get_the_post_thumbnail_url($mjob->ID, 'medium'),
									'mjob_title' => $mjob->post_title,
									'claim_link' => ($link_claim == "") ? get_permalink($arr->ID) : ($link_claim . '?id=' . $arr->ID),
									'claim_stt' => $stt->stt,
									'claim_name' => $stt->name,
									'claim_icon' => $stt->icon,
									'claim_tooltip' => htmlspecialchars($stt->tooltip, ENT_QUOTES),
									'is_admin' => ($mje_claim->is_admin()) ? true : false,
									'is_mobile' => (wp_is_mobile()) ? true : false,
									'author' => $author->display_name
								);
							}
							echo json_encode(array('data' => $datas, 'pav' => array('num' => $obj_claim->numpage, 'stt' => $_POST['status'], 'current' => $_POST['page'])));
							exit;
						}

						public function mje_claim_sync()
						{
							$err = 0;
							$msg = 'Some fields are not valid';
							$mjob = get_post($_POST['mjob_id']);
							$user = wp_get_current_user();
							$mje_claim = new MJE_Claim;
							$ae_option = new AE_Options;
							$claim_fee = ($ae_option->get_option('claim_fee') <> "") ? $ae_option->get_option('claim_fee') : 10;
							if (!$mje_claim->can_claim($mjob->ID)) {
								echo json_encode(array('success' => false, 'msg' => __('You can not send verification request for this mJob!', 'mje_verification')));
								exit;
							}
							$arrs = array('mjob_id', 'new_name_meta', 'skype_meta', 'pri_email_meta', 'alt_email_meta', 'photo_meta');
							foreach ($arrs as $arr) {
								if (trim($_POST[$arr]) == "") {
									$err = 1;
								}
							}
							if (!preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$/i", $_POST['pri_email_meta']) or !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$/i", $_POST['alt_email_meta']) or $_POST['pri_email_meta'] == $_POST['alt_email_meta']) {
								$err = 1;
								$msg = "Email field is invalid.";
							}
							if (!$err) {
								$my_post = array(
									'post_title' => 'Job Verification of ' . $mjob->post_title,
									'post_type' => 'mje_claims',
									'post_status'   => ($claim_fee == 0) ? 'mje_pending' : 'draft',
									'post_author'   => $user->ID,
									'post_parent' => $mjob->ID
								);
								$id_post = wp_insert_post($my_post);
								if ($id_post) {
									$arrs = array('new_name_meta', 'skype_meta', 'pri_email_meta', 'alt_email_meta', 'photo_meta');
									foreach ($arrs as $arr) {
										update_post_meta($id_post, $arr, $_POST[$arr]);
									}

									$mjob_price = get_post_meta($mjob->ID, 'et_budget', true);
									update_post_meta($id_post, 'mjob_price', $mjob_price);
									update_post_meta($id_post, 'claim_fee', $claim_fee);
									$link_redirect = ($claim_fee == 0) ? get_permalink($id_post) : et_get_page_link('order') . '?pid=' . $id_post;
									/* add noticaition if fee==0*/
									if ($claim_fee == 0) {
										/****** add notication *****/
										$notify_code = 'type=user_send_claim&claim_id=' . $id_post;
										$admins = get_users(array('role' => 'administrator'));

										$query = new WP_Query(
											array(
												'post_type'              => 'mje_notification',
												'title'                  => $notify_code,
												'posts_per_page'         => 1,
												'no_found_rows'          => true,
												'ignore_sticky_posts'    => true,
												'update_post_term_cache' => false,
												'update_post_meta_cache' => false,
											)
										);

										if (empty($query->post)) {
											foreach ($admins as $admin) {
												$args = array(
													'post_type' => 'mje_notification',
													'post_status' => 'unread',
													'post_author' => $admin->ID,
													'post_title' => $notify_code,
													'post_content' => $notify_code
												);
												wp_insert_post($args);
											}
										}
										/*****send mail *******/
										$fullname = get_post_meta($id_post, 'new_name_meta', true);
										$skype = get_post_meta($id_post, 'skype_meta', true);
										$pri_mail = get_post_meta($id_post, 'pri_email_meta', true);
										$alt_mail = get_post_meta($id_post, 'alt_email_meta', true);

										$headers = 'From: ' . $fullname . ' <' . $pri_mail . '>' . "\r\n";
										$headers .= "Reply-To: $pri_mail " . "\r\n";
										$headers = apply_filters('ae_inbox_mail_headers', $headers);

										$subject = sprintf(__('[%s]New Message From Claim', 'mje_verification'), get_bloginfo('blogname'));

										$cash_mail = ($ae_option->get_option('claim_mail_payment_cash')) ? $ae_option->get_option('claim_mail_payment_cash') : $mje_claim->claim_mail_cash();
										$pay_mail = ($ae_option->get_option('claim_mail_payment')) ? $ae_option->get_option('claim_mail_payment') : $mje_claim->claim_mail();

										$message = ($ae_option->get_option('claim_mail_free')) ? $ae_option->get_option('claim_mail_free') : $mje_claim->claim_mail_free();

										$message = str_replace('[display_name]', $fullname, $message);
										$message = str_replace('[user_skype]', $skype, $message);
										$message = str_replace('[user_email]', $pri_mail, $message);
										$message = str_replace('[user_email_alt]', $alt_mail, $message);
										$message = str_replace('[link_detail]', '<a href="' . get_permalink($id_post) . '">' . get_permalink($id_post) . '</a>', $message);

										$message = str_replace('[blogname]', get_bloginfo('blogname'), $message);

										$mail = new MJE_Mailing;
										$mail->wp_mail($pri_mail, $subject, $message, $headers);
									}


									echo json_encode(array('success' => true, 'msg' => __('Your request has been submitted', 'mje_verification'), 'id' => $id_post, 'link_redirect' => $link_redirect, 'fee' => $claim_fee));
								}
							} else {
								echo json_encode(array('success' => false, 'msg' => __($msg, 'mje_verification')));
							}
							exit;
						}
						public function mje_after_user_dropdown_menu_function()
						{
							ob_start();
							$mje_claim = new MJE_Claim;
							if (ae_get_option('claim_page')) {
								$str = 'Job Verification';
							?>
								<li><a href="<?php echo get_permalink(ae_get_option('claim_page')); ?>"><?php _e($str, 'mje_verification'); ?></a></li>
							<?php
							}
							echo ob_get_clean();
						}

						public function mje_after_user_sidebar_menu_function()
						{
							ob_start();
							if (ae_get_option('claim_page')) {
								global $post;
								$mje_claim = new MJE_Claim;
								$str = 'Job Verification';
							?>
								<li class="hvr-wobble-horizontal"><a href="<?php echo get_permalink(ae_get_option('claim_page')); ?>"><?php _e($str, 'mje_verification'); ?></a></a></li>
							<?php
							}
							echo ob_get_clean();
						}

						public function mje_seller_mjob_button_function()
						{
							global $post;
							$mje_claim = new MJE_Claim;
							$ae_option = new AE_Options;
							ob_start();
							if ($mje_claim->can_claim($post->ID)) {
								$price = get_post_meta($post->ID, 'et_budget', true);
								$fee = ($ae_option->get_option('claim_fee') <> "") ? $ae_option->get_option('claim_fee') : 10;
								$price_claim = round($price * ($fee / 100), 2);
							?>
								<a data-id="<?php echo $post->ID; ?>" class="btn-order btn-order-aside-bar waves-effect waves-light bgbtn-claim btn-submit click_claim_btn" data-toggle="modal" data-target="#claim_first_step"><?php _e('Verify this mJob', 'mje_verification'); ?></a>
								<div style="display:none">
									<div id="hide_me"></div>
								</div>
							<?php
								include(MJE_CLAIM_PATH . '/templates/claim_first_step.php');
								include(MJE_CLAIM_PATH . '/templates/claim_last_step.php');
							} else {
								ob_start();
								$order_finish = ($ae_option->get_option('claim_order_finished') <> "") ? $ae_option->get_option('claim_order_finished') : 2;
								$current_order = $mje_claim->get_count_finished($post->ID);
								$step1 = ($current_order >= $order_finish) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';
								$step2 = ($post->post_status == "publish" or $post->post_status == "unpause") ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';
								$step3 = (!$mje_claim->current_claim($post->ID)) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>';
							?>
								<ul class="tooltip-claim-disable">
									<li> <span><?php _e(' - The mJob is active', 'mje_verification'); ?></span> <?php echo $step2; ?></li>
									<li> <span><?php _e(" - Order finished", 'mje_verification'); ?> (<?php echo $current_order ?>/<?php echo $order_finish ?>)</span> <?php echo $step1; ?></li>
									<li> <span><?php _e(" - The mJob is not currently in the verification process", 'mje_verification'); ?></span> <?php echo $step3; ?></li>
								</ul>
								<?php
								$tooltip_content = ob_get_clean();
								?>
								<a data-toggle="tooltip" data-html="true" data-placement="top" data-original-title="<?php echo htmlspecialchars($tooltip_content, ENT_QUOTES); ?>" href="javascript:void(0)" data-id="<?php echo $post->ID; ?>" class="btn-order btn-order-aside-bar waves-effect waves-light bgbtn-claim btn-submit claim-disable">
									<?php _e('Verify this mJob', 'mje_verification'); ?>
								</a>

							<?php
							}
							echo ob_get_clean();
						}

						public function create_ae_carousel_claim_template()
						{
							ob_start();
							?>
							<script type="text/template" id="ae_carousel_claim_template"></script>
						<?php
							echo ob_get_clean();
						}

						public function create_ae_list_claim_template()
						{
							ob_start();
						?>
							<script type="text/template" id="ae_list_claim_template">
								<# if(is_mobile){ #>
                <?php include(MJE_CLAIM_PATH . '/templates/claim_mobile.php'); ?>
            <# } else { #>
                <?php include(MJE_CLAIM_PATH . '/templates/claim_desktop.php'); ?>
            <# } #>
		</script>
							<script type="data/json" id="claim_item_template_data">
								<?php
								$ae_option = new AE_Options;
								$link_claim = ($ae_option->get_option('claim_page_detail')) ? get_page_link($ae_option->get_option('claim_page_detail')) : '';
								$mje_claim = new MJE_Claim;
								$obj_claim = $mje_claim->get_claims();
								$arrs = $obj_claim->datas;
								$datas = array();
								foreach ($arrs as $arr) {
									$mjob = get_post($arr->post_parent);
									$stt = $mje_claim->get_status_claim_full($arr->ID);
									$author = get_user_by('id', $arr->post_author);
									if (isset($mjob->ID) && $mjob->ID) {
										$datas[] = array(
											'mjob_link' => get_permalink($mjob->ID),
											'mjob_src' => get_the_post_thumbnail_url($mjob->ID, 'thumbnail'),
											'mjob_src_medium' => get_the_post_thumbnail_url($mjob->ID, 'medium'),
											'mjob_title' => $mjob->post_title,
											'claim_link' => ($link_claim == "") ? get_permalink($arr->ID) : ($link_claim . '?id=' . $arr->ID),
											'claim_stt' => $stt->stt,
											'claim_name' => $stt->name,
											'claim_icon' => $stt->icon,
											'claim_tooltip' => htmlspecialchars($stt->tooltip, ENT_QUOTES),
											'is_admin' => ($mje_claim->is_admin()) ? true : false,
											'is_mobile' => (wp_is_mobile()) ? true : false,
											'author' => $author->display_name
										);
									}
								}
								echo json_encode($datas);
								?>
		</script>
							<?php
							echo ob_get_clean();
						}

						public function decision_claim()
						{
							$id = $_POST['id'];
							$claim = get_post($id);
							$current_status = $claim->post_status;
							$status = $_POST['status'];
							$reason = $_POST['reason'];
							$mje_claim = new MJE_Claim;
							if ($mje_claim->can_decision($id, $status) && $mje_claim->is_admin()) {
								if ($status == "mje_declined" && $reason == "") {
							?>
									<script>
										blockUi.unblock();
										AE.pubsub.trigger('ae:notification', {
											msg: 'Please give a reason decline',
											notice_type: 'error'
										});
									</script>
								<?php
								} else {
									$mje_claim->update_post_status($id, $status);
									if ($status == "mje_declined") {
										update_post_meta($id, 'decline_reason_meta', strip_tags($reason));
										/*
					if($current_status<>"mje_approved"){
						$ae_option=new AE_Options;
						$price=get_post_meta($claim->ID,'mjob_price',true);
						$fee=get_post_meta($claim->ID,'claim_fee',true);
						$price_claim=round($price*($fee/100),2);
						$wallet = AE_WalletAction()->getUserWallet( $claim->post_author );
						$wallet->balance+=$price_claim;
						AE_WalletAction()->setUserWallet( $claim->post_author, $wallet );
					}
					*/
									}
									$logs = get_post_meta($id, 'status_history_meta', true);
									$logs = ($logs == "" or $logs == "[]") ? array() : (array)json_decode($logs);
									$logs[] = array('status' => $status, 'date' => current_time('timestamp'));
									update_post_meta($id, 'status_history_meta', json_encode($logs));
									$notify_code = 'type=admin_decision_claim&claim_id=' . $id . '&status=' . $status;
									$args = array(
										'post_type' => 'mje_notification',
										'post_status' => 'unread',
										'post_author' => $claim->post_author,
										'post_title' => $notify_code,
										'post_content' => $notify_code
									);
									wp_insert_post($args);
									ob_start();
								?>
									<li><?php echo $mje_claim->button_decision($id, 'mje_verifying') ?></li>
									<li><?php echo $mje_claim->button_decision($id, 'mje_approved') ?></li>
									<li class="or_center"><span>OR</span></li>
									<li><?php echo $mje_claim->button_decision($id, 'mje_declined') ?></li>
									<?php
									$update_button = ob_get_clean();
									?>
									<textarea class="update_button_textarea"><?php echo htmlspecialchars($update_button, ENT_QUOTES); ?></textarea>
									<textarea class="update_notice_textarea"><?php echo htmlspecialchars($mje_claim->notice_decision($id), ENT_QUOTES); ?></textarea>
									<textarea class="update_log_status"><?php echo htmlspecialchars($mje_claim->get_claim_log_status($id), ENT_QUOTES); ?></textarea>
									<script>
										blockUi.unblock();
										jQuery('#popup_reason_decline').modal('hide');
										jQuery(".control-action-claim ul").html(jQuery(".update_button_textarea").val());
										jQuery("#notice_decison").html(jQuery(".update_notice_textarea").val());
										jQuery("#tab_status_history").html(jQuery(".update_log_status").val());
										jQuery('[data-toggle="tooltip"]').tooltip();
									</script>
								<?php
								}
							} else {
								?>
								<script>
									blockUi.unblock();
									AE.pubsub.trigger('ae:notification', {
										msg: 'Permission denied',
										notice_type: 'error'
									});
								</script>
					<?php
							}
							exit;
						}

						public function mje_checkout_custom_product_function($product)
						{
							if ($product->post_type == "mje_claims") {
								include(MJE_CLAIM_PATH . '/templates/claim_checkout.php');
							}
						}
						public function mje_other_type_notification($post)
						{
							//var_dump($type);
							$code = trim($post->post_content);
							$code = str_ireplace('&amp;', '&', $post->post_content);
							$code = strip_tags($code);
							// Convert string to variables

							parse_str($code, $output);
							$type 		= isset($output['type']) ? $output['type'] : '';
							$status 		= isset($output['status']) ? $output['status'] : '';


							$mje_claim 	= new MJE_Claim;
							$stt 		= $mje_claim->get_status_claim();

							if ($type == "admin_decision_claim") {
								$ae_option = new AE_Options;
								$link_claim = ($ae_option->get_option('claim_page_detail')) ? get_page_link($ae_option->get_option('claim_page_detail')) : '';
								$claim = get_post($claim_id);
								if ($claim) {
									$post->noti_link = ($link_claim == "") ? get_permalink($claim_id) : ($link_claim . '?id=' . $claim_id);
									$stt_text = ($status == "mje_verifying") ? 'started to review' : $stt[$status];
									$post->noti_content = sprintf(__('Admin has <span class="action-text">%s</span> your verification request for the mJob <strong>%s</strong>', 'mje_verification'), strtolower($stt_text), $claim->post_title);
								} else {
									$post->noti_link = '';
									$post->noti_content = 'This Job Verification has been deleted';
								}
							}

							if ($type == "user_send_claim") {
								$ae_option 	= new AE_Options;
								$link_claim = ($ae_option->get_option('claim_page_detail')) ? get_page_link($ae_option->get_option('claim_page_detail')) : '';
								$claim 		= get_post($claim_id);
								$user_claim = get_user_by('ID', $claim->post_author);
								$mjob 		= get_post($claim->post_parent);
								if ($mjob) {
									$post->noti_link = ($link_claim == "") ? get_permalink($claim_id) : ($link_claim . '?id=' . $claim_id);
									$post->noti_content = sprintf(__('%s  has <span class="action-text">sent</span> you a request to verify his mJob <strong>%s</strong>', 'mje_verification'), $user_claim->display_name, $mjob->post_title);
								} else {
									$post->noti_link = '';
									$post->noti_content = 'This Job Verification has been deleted';
								}
							}
							return $post;
						}

						function change_permalink_claim_detail($url, $post)
						{
							if ('mje_claims' == get_post_type($post)) {
								$ae_option = new AE_Options;
								$link_claim = ($ae_option->get_option('claim_page_detail')) ? get_page_link($ae_option->get_option('claim_page_detail')) : '';
								return ($link_claim == "") ? get_permalink($post->ID) : ($link_claim . '?id=' . $post->ID);
							}
							return $url;
						}
					}
					new MJE_Claim_User();

					?>