	<div class="fre-search-wrap">
		<?php
		global $wp, $user_ID, $current_user;
		$active_profile = '';
		$active_project = '';
		$action_link    = '';
		$input_hint     = '';
		$current_url    = home_url( add_query_arg( array(), $wp->request ) );
		$current_url    = $current_url . '/';

		if ( is_user_logged_in() ) {
			$user_data = get_userdata( $current_user->ID );
			$user_role = implode( ', ', $user_data->roles );
			if ( $user_role == 'freelancer' ) {
				$active_project = 'active';
				$action_link    = get_post_type_archive_link( PROJECT );
				$input_hint     = __( 'Find Projects', ET_DOMAIN );
			} else if ( $user_role == 'employer' ) {
				$active_profile = 'active';
				$action_link    = get_post_type_archive_link( PROFILE );
				$input_hint     = __( 'Find Freelancers', ET_DOMAIN );
			} else {
				$active_profile = 'active';
				$action_link    = get_post_type_archive_link( PROFILE );
				$input_hint     = __( 'Find Freelancers', ET_DOMAIN );
			}
		} else {
			$active_profile = 'active';
			$action_link    = get_post_type_archive_link( PROFILE );
			$input_hint     = __( 'Find Freelancers', ET_DOMAIN );
		}

		if ( $current_url == get_post_type_archive_link( PROJECT ) ) {
			$active_project = 'active';
			$active_profile = '';
			$action_link    = get_post_type_archive_link( PROJECT );
			$input_hint     = __( 'Find Projects', ET_DOMAIN );
		} else if ( $current_url == get_post_type_archive_link( PROFILE ) ) {
			$active_profile = 'active';
			$active_project = '';
			$action_link    = get_post_type_archive_link( PROFILE );
			$input_hint     = __( 'Find Freelancers', ET_DOMAIN );
		}
		?>

	    <form class="fre-form-search" action="<?php echo $action_link; ?>" method="post">
	        <div class="fre-search dropdown">
	                <span class="fre-search-dropdown-btn dropdown-toggle" data-toggle="dropdown">
	                    <i class="fa fa-search" aria-hidden="true"></i>
	                    <i class="fa fa-caret-down" aria-hidden="true"></i>
	                </span>
	            <input class="fre-search-field" name="keyword"
	                   value="<?php echo isset( $_POST['keyword'] ) ? $_POST['keyword'] : "" ?>" type="text"
	                   placeholder="<?php echo $input_hint; ?>">
	            <ul class="dropdown-menu fre-search-dropdown">
	                <li><a class="<?php echo $active_profile; ?>" data-type="profile"
	                       data-action="<?php echo get_post_type_archive_link( PROFILE ); ?>"><?php _e( 'Find Freelancers', ET_DOMAIN ); ?></a>
	                </li>
	                <li><a class="<?php echo $active_project; ?>" data-type="project"
	                       data-action="<?php echo get_post_type_archive_link( PROJECT ); ?>"><?php _e( 'Find Projects', ET_DOMAIN ); ?></a>
	                </li>
	            </ul>
	        </div>
	    </form>
	</div>