<header class="custom-new-header-container">
	<div class="custom-new-header">
		<!-- logo -->
		<div class="custom-new-logo-area">
			<img src="<?php echo get_stylesheet_directory_uri().'/assets/img/new-logo.png'; ?>">
		</div>
		<!-- end logo -->

		<!-- search bar -->
		<form class="custom-search-form" name="search">
		<div class="custom-new-search-area">
			
				<div class="search-input-area">
					<i class="fas fa-search"></i>
					<input type="text" placeholder="Find Your Guidance" class="custom-search-input">				
				</div>
				<button type="button" class="custom-search-button">
					<span>Profile</span>
					<i class="fas fa-caret-right"></i>
					<!-- <i class="fas fa-caret-down"></i> dung sau -->
				</button>
												
		</div>
		</form>
		<!-- end search -->

		<!-- action links -->
		<div class="custom-action-links">
			<a href="#"><span>Post a hire </span><i class="fas fa-plus-circle"></i></a>
			<a  href="#"><span> Upload Course </span><i class="fas fa-plus-circle"></i></a>
			<a class="custom-contact-icons-new"  href="#"><i class="fas fa-bell"></i></a>
			<a  class="custom-contact-icons-new" href="#"><i class="fas fa-comment-dots"></i></a>
		</div>
		<!-- end action links -->

		<!-- profile menu -->
		<div class="custom-profile-menu-header">
				<div class="custom-profile-menu-dropdown">
					 <?php
					  echo mje_avatar(get_current_user_id(),35,array('class'=>'custom-profile-menu-avatar-img','title'=>'','alt'=>''));					 	
					 ?>
					 <span class="custom-profile-menu-header-authorname">Jane Cooper</span>
					 <i class="fas fa-caret-right custom-dropdown-icon-menu"></i>
				</div>
		</div>
		<!-- end profile menu -->
	</div>
</header>