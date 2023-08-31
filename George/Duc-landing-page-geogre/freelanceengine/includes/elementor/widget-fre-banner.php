<?php

class Fre_Banner extends WP_Widget {
	public $title;
	function __construct() {
		parent::__construct(

			// Base ID of your widget
			'Fre_Banner', __('Fre Banner', ET_DOMAIN),
			// Widget description
			array( 'description' => 'Fre Banner In Home Page' )
		);
		$this->title = __("Find perfect freelancers for your projects or Look for freelance jobs online?", ET_DOMAIN);
	}


	public function widget( $args, $instance ) {

		global $user_ID;
		$title_banner = isset($instance[ 'title_banner' ]) ? $instance[ 'title_banner' ] : $this->title;
		?>
		<!-- Block Banner -->
		<div class="fre-background home-block-banner" id="background_banner" style="background-image: url('<?php echo get_theme_mod("background_banner") ? get_theme_mod("background_banner") : get_template_directory_uri()."/img/fre-bg.png";?>');">
			<div class="fre-bg-content">
				<div class="container">
					<h1 id="title_banner"><?php echo $title_banner; ?></h1>
					<?php if(!is_user_logged_in()){ ?>
						<?php if(!fre_check_register()){ ?>
							<a class="fre-btn primary-bg-color" href="<?php echo get_post_type_archive_link( PROFILE ); ?>"><?php _e('Find Freelancers', ET_DOMAIN);?></a>
							<a class="fre-btn primary-bg-color" href="<?php echo get_post_type_archive_link( PROJECT ); ?>"><?php _e('Find Projects', ET_DOMAIN);?></a>
						<?php }else{ ?>
							<a class="fre-btn primary-bg-color" href="<?php echo et_get_page_link('register', array("role"=>'employer')); ?>"><?php _e('Hire Freelancer', ET_DOMAIN);?></a>
							<a class="fre-btn primary-bg-color" href="<?php echo et_get_page_link('register', array("role"=>'freelancer')); ?>"><?php _e('Apply as Freelancer', ET_DOMAIN);?></a>
						<?php } ?>

					<?php }else{ ?>
						<?php if(ae_user_role($user_ID) == FREELANCER){ ?>
							<a class="fre-btn primary-bg-color" href="<?php echo get_post_type_archive_link( PROJECT ); ?>"><?php _e('Find Projects', ET_DOMAIN);?></a>
							<a class="fre-btn primary-bg-color" href="<?php echo et_get_page_link('profile'); ?>"><?php _e('Update Profile', ET_DOMAIN);?></a>
						<?php }else{ ?>
							<a class="fre-btn primary-bg-color" href="<?php echo et_get_page_link('submit-project'); ?>"><?php _e('Post a Project', ET_DOMAIN);?></a>
							<a class="fre-btn primary-bg-color" href="<?php echo get_post_type_archive_link( PROFILE ); ?>"><?php _e('Find Freelancers', ET_DOMAIN);?></a>
						<?php } ?>
					<?php } ?>
				</div>
			</div>
		</div>
		<!-- Block Banner -->
		<?php
	}


	public function form( $instance ) {

		$title_banner = isset($instance[ 'title_banner' ]) ? $instance['title_banner'] : $this->title; ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title_banner' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title_banner' ); ?>" name="<?php echo $this->get_field_name( 'title_banner' ); ?>" type="text" value="<?php echo esc_attr( $title_banner ); ?>" />
		</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title_banner'] = ( ! empty( $new_instance['title_banner'] ) ) ? strip_tags( $new_instance['title_banner'] ) : $this->title;
		return $instance;
	}

}?>