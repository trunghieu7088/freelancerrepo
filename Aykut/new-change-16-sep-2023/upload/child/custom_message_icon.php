<?php
function mje_show_user_header()
	{
		global $current_user;
		$conversation_unread = mje_get_unread_conversation_count();
		// Check empty current user
		if (!empty($current_user->ID)) {
?>
			<div class="notification-icon list-message et-dropdown">
				<span id="show-notifications" class="link-message">
					<?php echo mje_is_has_unread_notification() ? '<span class="alert-sign">' . mje_get_unread_notification_count() . '</span>' : ''; ?>
					<i class="fa fa-bell"></i>
				</span>
			</div>

			<div class="message-icon list-message dropdown et-dropdown">
				<div class="dropdown-toggle hidden-sm hidden-xs" type="button" id="dropdownMenu1" data-toggle="dropdown">
					<span class="link-message">
						<?php
						if ($conversation_unread > 0) {
							echo '<span class="alert-sign">' . $conversation_unread . '</span>';
						}
						?>
						<i class="fa fa-comment"></i>
					</span>
				</div>
                <div class="dropdown-toggle hidden-md hidden-lg" type="button" id="dropdownMenu1">
                    <a href="<?php echo site_url('my-list-messages'); ?>">
					<span class="link-message">
						<?php
						if ($conversation_unread > 0) {
							echo '<span class="alert-sign">' . $conversation_unread . '</span>';
						}
						?>
						<i class="fa fa-comment"></i>
					</span>
                    </a>
				</div>
				<div class="list-message-box dropdown-menu" aria-labelledby="dLabel">
					<div class="list-message-box-header">
						<span>
							<?php
							printf(__('<span class="unread-message-count">%s</span> New', 'enginethemes'), $conversation_unread);
							?>
						</span>
						<a href="#" class="mark-as-read"><?php _e('Mark all as read', 'enginethemes'); ?></a>
					</div>

					<ul class="list-message-box-body">
						<?php
						mje_get_user_dropdown_conversation();
						?>
					</ul>

					<div class="list-message-box-footer">
						<a href="<?php echo et_get_page_link('my-list-messages'); ?>"><?php _e('View all', 'enginethemes'); ?></a>
					</div>
				</div>
			</div>

			<!--<div class="list-notification">
                <span class="link-notification"><i class="fa fa-bell"></i></span>
            </div>-->
			<?php
			$absolute_url = mje_get_full_url($_SERVER);
			if (is_mje_submit_page()) {
				$post_link = '#';
			} else {
				$post_link = et_get_page_link('post-service') . '?return_url=' . $absolute_url;
			}
			?>
			<div class="link-post-services">
				<a href="<?php echo $post_link; ?>"><?php _e('Post a mJob', 'enginethemes'); ?>
					<div class="plus-circle"><i class="fa fa-plus"></i></div>
				</a>
			</div>
			<div class="user-account">
				<div class="dropdown user-account-dropdown et-dropdown">
					<div class="dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
						<span class="avatar">
							<span class="display-avatar"><?php echo mje_avatar($current_user->ID, 35); ?></span>
							<span class="display-name"><?php echo $current_user->display_name; ?></span>
						</span>
						<span><i class="fa fa-angle-right"></i></span>
					</div>
					<ul class="dropdown-menu et-dropdown-login" aria-labelledby="dLabel">
						<li><a href="<?php echo et_get_page_link('dashboard'); ?>"><?php _e('Dashboard', 'enginethemes'); ?></a></li>
						<?php
						/**
						 * Add new item menu after Dashboard
						 *
						 * @since 1.3.1
						 * @author Tan Hoai
						 */
						do_action('mje_before_user_dropdown_menu');
						?>
						<li><a href="<?php echo et_get_page_link("profile"); ?>"><?php _e('My profile', 'enginethemes'); ?></a></li>
						<li><a href="<?php echo et_get_page_link("my-list-order"); ?>"><?php _e('My orders', 'enginethemes'); ?></a></li>
						<li><a href="<?php echo et_get_page_link("my-listing-jobs"); ?>"><?php _e('My jobs', 'enginethemes'); ?></a></li>
						<li><a href="<?php echo et_get_page_link("my-invoices"); ?>"><?php _e('My invoices', 'enginethemes'); ?></a></li>
						<li class="post-service-link"><a href="<?php echo et_get_page_link('post-service'); ?>"><?php _e('Post a mJob', 'enginethemes'); ?>
								<div class="plus-circle"><i class="fa fa-plus"></i></div>
							</a></li>
						<li class="get-message-link">
							<a href="<?php echo et_get_page_link('my-list-messages'); ?>"><?php _e('Message', 'enginethemes'); ?></a>
						</li>
						<?php
						/**
						 * Add new item menu before Sign out
						 *
						 * @since 1.3.1
						 * @author Tan Hoai
						 */
						do_action('mje_after_user_dropdown_menu');
						?>
						<li><a href="<?php echo wp_logout_url(home_url()); ?>"><?php _e('Sign out', 'enginethemes'); ?> </a></li>
					</ul>
					<div class="overlay-user"></div>
				</div>
			</div>
		<?php
		}
	}