<?php

/**
 *@since Fre 1.8.18
 **/

function fre_generate_page( $args ) {
	$page_slug 		= $args['slug'];
	$page_title 	= $args['post_title'];
	$post_content 	= isset($args['post_content']) ? $args['post_content']: 'Please fill out the form below ';
	$page_args = array(
		'post_name' => $page_slug,
		'post_title'   => $page_title,
		'post_content' => $post_content,
		'post_type'    => 'page',
		'post_status'  => 'publish'
	);

	$pages = get_pages( array(
		'meta_key'    => '_wp_page_template',
		'meta_value'  => 'page-' . $page_slug . '.php',
		'numberposts' => 1
	) );
	$opt_page_name = 'page-' . $page_slug . '.php';

	// page not existed
	if ( empty( $pages ) || ! is_array( $pages ) ) {
		$id = wp_insert_post( $page_args );
		if ( $id ) {
			update_post_meta( $id, '_wp_page_template', 'page-' . $page_slug . '.php' );
		}

	} else {
		// page exists
		$page = array_shift( $pages );
		$id   = $page->ID;
	}
	if ( $id != - 1 ) {
		$return = get_permalink( $id );
	}
	/**
	 * update transient page link
	 */
	update_option( $opt_page_name, $return );
	return $id;
}

function fre_auto_generate_et_pages(){
	$pages = fre_get_page_default();
	foreach($pages as $key=>$title){

		$args = array(
			'slug' 			=> $key,
			'post_title' 	=> $title,
			'post_content' 	=> '.'
		);
		fre_generate_page($args);
	}
}

/**
 * List all static pages on the theme here.
 * @since 1.8.18
 **/
function fre_get_page_default(){
	return $args = array(
		// 'home-new' 			=> "Home New",  Process in a specific method.
		'register' 			=> 'Sign Up',
		'reset-pass' 		=> 'Reset Password',
		'list-notification' => 'List Notification',
		'forgot-password' 	=> 'Forgot Password',
		'login' 			=> 'Login',
		'profile' 			=> 'Profile',
		'upgrade-account' 	=> 'Upgrade Account',
		'submit-project' 	=> 'Post a Project',
		'edit-project' 		=> 'Edit Project',
		'process-payment' 	=> 'Process Payment',
		'cancel-payment' 	=> 'Cancel Payment',
		'my-project'	 	=> 'My Project',
		'my-credit' 		=> 'My Credit',
		'tos' 				=> 'Terms of service',
	);
}

/**
 * Update option page_on_front when FrE upgrade to version 1.8
 * New Homepage
 * @author danng
 */
function fre_set_frontpage_theme() {
	$isSet = get_option( 'set_page_front' );
	if ( $isSet == 1 ) {
		return;
	}
	$page_on_front = get_option( 'page_on_front' );
	// List page
	$pages = get_pages(
		array(
			'post_status' => 'publish',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'page-home-new.php'
		)
	);
	if ( empty( $pages ) ) {
		$pages        = get_pages(
			array(
				'post_status' => 'publish',
				'meta_key'    => '_wp_page_template',
				'meta_value'  => 'page-home-new.php'
			)
		);
		if( $pages && is_array($pages)){
			$page         = $pages[0];
			$id = $page->ID;
		} else {
			$args = array(
				'slug' 		 => 'home-new',
				'post_title' => "Home New",
				'post_content' => "Default home page",
			);
			$id = fre_generate_page($args);
		}
		update_option( 'page_on_front', $id );
		update_option( 'set_page_front', 1 );
		update_option( 'show_on_front', 'page' );
	} else {
		$page = $pages[0];
		update_option( 'page_on_front', $page->ID );
		update_option( 'set_page_front', 1 );
    	update_option( 'show_on_front', 'page' );

	}
}
/**
 * Auto generate the deposit page.
 * @param void
 * @return void
 * @since 1.8.18
 * @package FREELANCEENGINE
 * @category FRE CREDIT
 * @author Danng
 */
function fre_create_deposit_page(){
    // prevent duplicate pages Fre_credit_deposit
    $args = array(
        'posts_per_page'   => -1,
        'offset'           => 0,
        'orderby'          => 'title',
        'order'            => 'DESC',
        'post_type'        => 'page',
        'post_status'      => 'publish',
        's'                => '[fre_credit_deposit]'
    );
    $deposite_page_id =  ae_get_option('fre_credit_deposit_page_slug', false);
    if( ! $deposite_page_id ){
    	$the_query = new WP_Query( $args );
     	if( $the_query->have_posts() ){

            while ($the_query->have_posts()) {
                $the_query->the_post();
                ae_update_option('fre_credit_deposit_page_slug', get_the_ID());
                et_log('Page deposit exits.');
                return false;
            }
        }
    } else {
    	$post = get_post($deposite_page_id);
    	if(  $post ){
    		et_log('Page deposit exits.');
    		return 0;
    	}
    }

    // Insert the post into the database
    $fre_credit_deposit = array(
      'post_title'    => 'Deposit Credit',
      'post_content'  => '[fre_credit_deposit]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
    );
    $post_id = wp_insert_post( $fre_credit_deposit, '' );
    if($post_id) {
    	et_log('create deposite page Done.');
        update_post_meta( $post_id, '_wp_page_template', 'page-full-width.php' );
        ae_update_option('fre_credit_deposit_page_slug', $post_id);
    }
}

function fre_generate_default_page( $old_theme_name, $old_theme = false ) {
	fre_auto_generate_et_pages();
	fre_create_deposit_page();
	fre_set_frontpage_theme();
}
add_action( 'after_switch_theme', 'fre_generate_default_page', 99, 2 );

function fre_reset_general_page($newname, $newtheme) {
	$pages = fre_get_page_default();
	foreach($pages as $key=>$title){
		$opt_page_name = 'page-' . $key . '.php';
		update_option($opt_page_name ,'');
	}
	update_option( 'set_page_front','' );
}
add_action("switch_theme", "fre_reset_general_page", 10 , 2);

/**
 *  hàm et_get_page_link lấy giá trị full_path_url lưu sẵn vào bảng options => return uncorrect url => 404 page.
 * Need to be reset all to empty.
 * @since 1.8.19
 **/
function fre_reset_all_default_page_link(){
	$pages = fre_get_page_default();
	foreach($pages as $slug=>$title){
		if( $slug == 'home-new' ) continue;
		$opt_link_name = 'page-' . $slug . '.php';
		update_option($opt_link_name,'');
	}
}
add_action('permalink_structure_changed','fre_reset_all_default_page_link');