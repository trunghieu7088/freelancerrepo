<?php

/**
 * Generate social connect page template;
 */
function ae_page_social_connect()
{
	global $wp_query, $wp_rewrite, $post, $et_data;

	if (!isset($_SESSION['et_auth']) && !isset($_REQUEST['credential']) && !isset($_REQUEST['type'])) {
		// shouldn't access this page without things to do --> redirect to home
?>
		<script type="text/javascript">
			window.location.href = "<?php echo home_url() ?>";
		</script>
	<?php
	}

	if (!isset($_SESSION)) {
		ob_start();
		@session_start();
	}

	$error = isset($et_data['error']) ? $et_data['error'] : "";

	if ($error) { ?>
		<div class="social-error">
			<?php _e("There has been an error during your authentication. You will be redirect to the sign in page to try again in 3s.", 'enginethemes'); ?>
		</div>
		<script type="text/javascript">
			setTimeout(function() {
				window.location.href = "<?php echo et_get_page_link("sign-in") ?>";
			}, 3000);
		</script>
	<?php
	} else {

		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
		if ('google' == $type && isset($_REQUEST['credential'])) {
			// this action handle the credential in the POST request that Google return to us
			// it set the SESSION that will be used in below forms
			do_action('handle_google_cred_after_login', $_REQUEST['credential']);
		}

		$et_session = et_read_session();

		if (isset($et_session['et_auth']) && $et_session['et_auth'] != '') {
			$auth = unserialize($et_session['et_auth']);
		} elseif (isset($_SESSION['et_auth']) && $_SESSION['et_auth'] != '') {
			$auth = unserialize($_SESSION['et_auth']);
		} else {
		}

	?>
		<div class="twitter-auth social-auth social-auth-step1">
			<div class="social-welcome">
				<?php printf(__("Welcome! This seems to be your first time signing in using your %s account.", 'enginethemes'), ucwords(strtolower($type))); ?>
			</div>
			<div class="social-instruction">
				<p><?php printf(__("If you already have an account with %s, use the form below to link it.", 'enginethemes'), get_bloginfo("name")); ?></p>
				<p><?php printf(__("New user? Enter your email, password, and choose a username on the next page to create your account (one-time setup!). Next time, you'll be logged in with %s in a flash!", 'enginethemes'), ucwords(strtolower($type))); ?></p>
			</div>

			<form id="form_auth" method="post" action="">
				<div class="social-form">
					<input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce('authentication') ?>">
					<input type="text" name="user_email" value="<?php if (isset($auth['user_email'])) echo $auth['user_email']; ?>" placeholder="<?php _e('Email', 'enginethemes') ?>">
					<input type="password" name="user_pass" placeholder="<?php _e('Password', 'enginethemes') ?>">
					<input type="submit" value="<?php _e('Submit', 'enginethemes'); ?>">
				</div>
			</form>
		</div>
		<div class="social-auth social-auth-step2">
			<div class="social-welcome"><?php _e('Please provide a username to continue', 'enginethemes'); ?></div>
			<form id="form_username" method="post" action="">
				<div class="social-form">
					<input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce('authentication') ?>">
					<input type="text" name="user_login" value="<?php echo isset($auth['user_login']) ? $auth['user_login'] : "" ?>" placeholder="<?php _e('Username', 'enginethemes') ?>">
					<?php $social_user_roles = ae_get_option('social_user_role', false);
					if (!$social_user_roles) {
						$social_user_roles = ae_get_social_login_user_roles_default();
					}
					if ($social_user_roles && count($social_user_roles) >= 1) { ?>
						<select name="user_role" class="sc_user_role">
							<?php foreach ($social_user_roles as $key => $value) { ?>
								<option value="<?php echo $value ?>"><?php echo $value; ?></option>
							<?php } ?>
						</select>
					<?php } ?>
					<input type="submit" value="<?php _e('Submit', 'enginethemes'); ?>">
				</div>
			</form>
		</div>
		<?php
	}
}
/**
 *Generate short code phot social connect page
 */
function ae_social_connect_page_shortcode()
{
	return ae_page_social_connect();
}
add_shortcode('social_connect_page', 'ae_social_connect_page_shortcode');

function ae_is_social_enabled()
{
	return ae_get_option('facebook_login', false) || ae_get_option('gplus_login', false);
}

/**
 *get user for social login
 */
function ae_social_auth_support_role()
{
	$default = array('author' => 'author', 'subscriber' => 'subscriber', 'editor' => 'editor', 'contributor' => 'contributor');
	return apply_filters('ae_social_auth_support_role', $default);
}
/**
 *Render the social login button
 *
 *@param array $icon_classes are css classes for displaying social buttons
 *@param array $button_classes are css classes for displaying social buttons
 *@param string $before_text are text display before social login buttons
 *@param string $after_text are text display after social login buttons
 *@since version 1.8.4 of DE
 *
 */
function ae_render_social_button($icon_classes = array(), $button_classes = array(), $before_text = '', $after_text = '')
{
	/* check enable option*/
	$use_facebook = ae_get_option('facebook_login');
	$gplus_login = ae_get_option('gplus_login');
	if ($icon_classes == '') {
		$icon_classes = 'fa-brands fa-square-facebook';
	}
	$defaults_icon = array(
		'fb' => 'fa-brands fa-square-facebook',
		'gplus' => 'fa-brands fa-google',
	);
	$icon_classes = wp_parse_args($icon_classes, $defaults_icon);
	$icon_classes = apply_filters('ae_social_icon_classes', $icon_classes);
	$defaults_btn = array(
		'fb' => '',
		'gplus' => '',
	);
	$button_classes = wp_parse_args($button_classes, $defaults_btn);
	$button_classes = apply_filters('ae_social_button_classes', $button_classes);
	if ($use_facebook || $gplus_login) {
		if ($before_text != '') { ?>
			<div class="socials-head"><?php echo $before_text ?></div>
		<?php } ?>
		<ul class="list-social-login">
			<?php if ($use_facebook) { ?>
				<li>
					<a href="#" class="fb facebook_auth_btn <?php echo $button_classes['fb']; ?>">
						<i class="<?php echo $icon_classes['fb']; ?>"></i>
						<span class="social-text"><?php _e("Facebook", 'enginethemes') ?></span>
					</a>
				</li>
			<?php } ?>
			<?php if ($gplus_login) {
				do_action('google_login_btn', "icon");
			} ?>
		</ul>
		<?php
		if ($after_text != '') { ?>
			<div class="socials-footer"><?php echo $after_text ?></div>
<?php }
	}
}
