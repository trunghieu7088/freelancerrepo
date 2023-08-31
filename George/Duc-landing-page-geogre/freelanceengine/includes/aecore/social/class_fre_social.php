<?php
Class Fre_Social_Front{
	function __construct(){
		add_action('wp_ajax_nopriv_fre_set_role', array($this,'fre_set_role') );

		//add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
		add_action('wp_footer',array($this,'show_popup_set_role'), 999);
	}
	function fre_set_role(){

	}
	function enqueue_scripts(){
		wp_enqueue_script(
			'fre-social-login',
			ae_get_url() . '/social/js/fre_social.js',
			array('jquery','underscore','backbone','appengine'), ET_VERSION, true
		);
	}
	function show_popup_set_role(){

		if( is_user_logged_in() ){
			global $user_ID;
			$show_popup = true;

			$role = get_social_role();
			$require_set_role = get_user_meta($user_ID, 'et_require_set_role', true);

			if( $require_set_role && ($role == FREELANCER || $role == EMPLOYER )  ){
				global $user_ID;
				$show_popup = false;
				wp_update_post( array('ID' => $user_ID,'role' => $role) );
			}
			if( !$require_set_role ){
				$show_popup = false;
			}
			// $show_popup  = true;
			if( $show_popup ){
				fre_social_set_role_popup();
			}
		}

	}
}
new Fre_Social_Front();
