<?php
global $user_ID;
?>
<div class="fre-menu-top">
    <ul class="fre-menu-main">
        <?php do_action('before_header_menu')?>
        <!-- Menu freelancer -->
		<?php if ( ! is_user_logged_in() ) { ?>
            <li class="fre-menu-freelancer dropdown">
                <a><?php _e( 'FREELANCERS', ET_DOMAIN ); ?><i class="fa fa-caret-down"
                                                              aria-hidden="true"></i></a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="<?php echo get_post_type_archive_link( PROJECT ); ?>"><?php _e( 'Find Projects', ET_DOMAIN ); ?></a>
                    </li>
					<?php if ( fre_check_register() ) { ?>
                        <li>
                            <a href="<?php echo et_get_page_link( 'register' ) . '?role=freelancer'; ?>"><?php _e( 'Create Profile', ET_DOMAIN ); ?></a>
                        </li>
					<?php } ?>
                </ul>
            </li>
            <li class="fre-menu-employer dropdown">
                <a><?php _e( 'EMPLOYERS', ET_DOMAIN ); ?><i class="fa fa-caret-down" aria-hidden="true"></i></a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="<?php echo et_get_page_link( 'login' ) . '?ae_redirect_url=' . urlencode( et_get_page_link( 'submit-project' ) ); ?>"><?php _e( 'Post a Project', ET_DOMAIN ); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo get_post_type_archive_link( PROFILE ); ?>"><?php _e( 'Find Freelancers', ET_DOMAIN ); ?></a>
                    </li>
                </ul>
            </li>
		<?php } else { ?>

			<?php if ( ae_user_role( $user_ID ) == FREELANCER ) { ?>
                <li class="fre-menu-freelancer dropdown ">
                    <a href="<?php echo et_get_page_link( "my-project" ); ?>"><?php _e( 'MY PROJECT', ET_DOMAIN ); ?></a>
                    <ul class="dropdown-menu">
                        <li class="fre-menu-employer dropdown-empty">


                            <a href="<?php echo et_get_page_link( "my-project" ); ?>"><?php _e( 'PROJECTS', ET_DOMAIN ); ?></a>
                        </li>
                    </ul>
                </li>
                <li class="fre-menu-employer dropdown-empty">
                    <a href="<?php echo get_post_type_archive_link( PROJECT ); ?>"><?php _e( 'PROJECTS', ET_DOMAIN ); ?></a>
                </li>

			<?php } else { ?>
                <li class="fre-menu-employer dropdown">
                    <a><?php _e( 'PROJECTS', ET_DOMAIN ); ?><i class="fa fa-caret-down"
                                                                 aria-hidden="true"></i></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?php echo et_get_page_link( "my-project" ); ?>"><?php _e( 'All Projects Posted', ET_DOMAIN ); ?></a>
                        </li>
                        <li>
                            <a href="<?php echo et_get_page_link( 'submit-project' ); ?>"><?php _e( 'Post a Project', ET_DOMAIN ); ?></a>
                        </li>
                    </ul>
                </li>
                <li class="fre-menu-employer dropdown-empty">
                    <a href="<?php echo get_post_type_archive_link( PROFILE ); ?>"><?php _e( 'FREELANCERS', ET_DOMAIN ); ?></a>
                </li>
			<?php } ?>
		<?php } ?>
        <!-- Main Menu -->
		<?php if ( has_nav_menu( 'et_header_standard' ) ) { ?>
            <li class="fre-menu-page dropdown">
                <a><?php _e( 'PAGES', ET_DOMAIN ); ?><i class="fa fa-caret-down" aria-hidden="true"></i></a>
				<?php
				$args = array(
					'theme_location'  => 'et_header_standard',
					'menu'            => '',
					'container'       => '',
					'container_class' => '',
					'container_id'    => '',
					'menu_class'      => 'dropdown-menu',
					'menu_id'         => '',
					'echo'            => true,
					'before'          => '',
					'after'           => '',
					'link_before'     => '',
					'link_after'      => ''
				);
				wp_nav_menu( $args );
				?>
            </li>
		<?php } ?>
        <!-- Main Menu -->
        <?php do_action('after_header_menu')?>
    </ul>
</div>