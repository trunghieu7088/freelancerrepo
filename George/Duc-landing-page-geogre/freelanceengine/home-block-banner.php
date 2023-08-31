<?php
global $user_ID;
?>
<!-- Block Banner -->
<div class="fre-background home-block-banner" id="background_banner" style="background-image: url('<?php echo get_theme_mod("background_banner") ? get_theme_mod("background_banner") : get_template_directory_uri()."/img/fre-bg.png";?>');">
	<div class="fre-bg-content">
		<div class="container">
			<h1 id="title_banner"><?php echo get_theme_mod("title_banner") ? get_theme_mod("title_banner") : __("Find perfect freelancers for your projects or Look for freelance jobs online?", ET_DOMAIN);?></h1>
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