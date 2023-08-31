<?php


/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage FreelanceEngine
 * @since FreelanceEngine 1.0
 */
global $current_user;
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<?php global $user_ID; ?>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1 ,user-scalable=no">
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php ae_favicon(); ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<!-- <div class="fre-wrapper"> -->
<header class="fre-header-wrapper">
    <div class="fre-header-wrap" id="main_header">
        <div class="container">
            <div class="fre-site-logo">
                <a href="<?php echo home_url(); ?>">
					<?php fre_logo( 'site_logo' ) ?>
                </a>
                <div class="fre-hamburger">
					<?php if ( is_user_logged_in() ) { ?>
                        <a class="fre-notification notification-tablet" href="">
                            <i class="fa fa-bell-o" aria-hidden="true"></i>
							<?php
							if ( function_exists( 'fre_user_have_notify' ) ) {
								$notify_number = fre_user_have_notify();
								if ( $notify_number ) {
									echo '<span class="dot-noti"></span>';
								}
							}
							?>
                        </a>
					<?php } ?>
                    <span class="hamburger-menu">
                            <div class="hamburger hamburger--elastic" tabindex="0" aria-label="Menu" role="button"
                                 aria-controls="navigation">
                                <div class="hamburger-box">
                                    <div class="hamburger-inner"></div>
                                </div>
                            </div>
                        </span>
                </div>
            </div>
			<?php if ( is_user_logged_in() ) { ?>
                <div class="fre-account-tablet">
                    <div class="fre-account-info">
						<?php echo get_avatar( $user_ID ); ?>
                        <span><?php echo $current_user->display_name; ?></span>
                    </div>
                </div>
			<?php } ?>


            <?php get_template_part( 'template/search', 'form' ); ?>

			<?php if ( is_user_logged_in() ) { ?>
                <div class="fre-account-info-tablet">
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?php echo et_get_page_link( "profile" ) ?>"><?php _e( 'MY PROFILE', ET_DOMAIN ); ?></a>
                        </li>
						<?php do_action( 'fre_header_before_notify' ); ?>
                        <li><a href="<?php echo wp_logout_url(); ?>"><?php _e( 'LOGOUT', ET_DOMAIN ); ?></a></li>
                    </ul>
                </div>
			<?php } ?>

            <?php fre_header_menu(); ?>

			<?php if ( ! is_user_logged_in() ) { ?>
                <div class="fre-account-wrap">
                    <div class="fre-login-wrap">
                        <ul class="fre-login">
                            <li>
                                <a href="<?php echo et_get_page_link( "login" ) ?>"><?php _e( 'LOGIN', ET_DOMAIN ); ?></a>
                            </li>
							<?php if ( fre_check_register() ) { ?>
                                <li>
                                    <a href="<?php echo et_get_page_link( "register" ) ?>"><?php _e( 'SIGN UP', ET_DOMAIN ); ?></a>
                                </li>
							<?php } ?>
                        </ul>
                    </div>
                </div>
			<?php } else { ?>
                <div class="fre-account-wrap dropdown">
                    <a class="fre-notification dropdown-toggle" data-toggle="dropdown" href=""> <i class="fa fa-bell-o" aria-hidden="true"></i>
						<?php
						if ( function_exists( 'fre_user_have_notify' ) ) {
							$notify_number = fre_user_have_notify();
							if ( $notify_number ) {
								echo '<span class="dot-noti"></span>';
							}
						} ?>
                    </a>
					<?php fre_user_notification( $user_ID, 1, 5 ); ?>
                    <div class="fre-account dropdown">
                        <div class="fre-account-info dropdown-toggle" data-toggle="dropdown">
							<?php echo get_avatar( $user_ID ); ?>
                            <span><?php echo $current_user->display_name; ?></span>
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </div>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?php echo et_get_page_link( "profile" ) ?>"><?php _e( 'MY PROFILE', ET_DOMAIN ); ?></a>
                            </li>
							<?php do_action( 'fre_header_before_notify' ); ?>
                            <li><a href="<?php echo wp_logout_url(); ?>"><?php _e( 'LOGOUT', ET_DOMAIN ); ?></a></li>
                        </ul>
                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
</header>
<!-- MENU DOOR / END -->

<?php
global $user_ID;
if ( $user_ID ) {
	echo '<script type="data/json"  id="user_id">' . json_encode( array(
			'id' => $user_ID,
			'ID' => $user_ID
		) ) . '</script>';
}

?>