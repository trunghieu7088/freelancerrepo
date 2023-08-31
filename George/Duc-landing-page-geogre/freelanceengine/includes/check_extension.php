<?php
/**
 * indicator the fre_membership is activated or not.
 * @since: 1.8.9
*/
if( ! function_exists('is_acti_fre_membership')){
	function  is_acti_fre_membership(){
		if( defined('FRE_MEMBERSHIP_VER') ){
			return 1;
		}
		return 0;
	}
}
if( ! function_exists('fre_membership_package_info') ){
	function fre_membership_package_info(){

	}
}
if( ! function_exists('is_acti_fre_credit_plus') ){
	function is_acti_fre_credit_plus(){
		return false;
	}
}
if( ! function_exists('is_active_fre_credit') ){
	function is_active_fre_credit(){
		if( defined('FRE_CREDIT_VERSION'))
			return true;
		return false;
	}
}


/**
 * check user can or can't bid a project
 *
 * @param int $user_ID the user's ID
 *
 * @return bool true if user can bid / false if user can't bid
 * @since version 1.5.4
 * @author Tambh
 *
 */
if( ! function_exists('can_user_bid') ){
	function can_user_bid( $user_id =0 ) {
		if( ! $user_id ){
			global $user_ID;
			$user_id = $user_ID;
		}

		if ( ae_get_option( 'pay_to_bid', false ) ) {
			// add from 1.8.9
			return fre_get_total_bid($user_id);
		}
		return true;
	}
}
/**
*/
if( ! function_exists('can_post_project_free')){
	function can_post_project_free($sku){

		if( ! is_acti_fre_membership() )
			return AE_Package::can_post_free($sku);

		return apply_filters('can_post_project_free', true , $sku);
	}
}