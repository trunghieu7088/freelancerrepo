<?php
/*
Plugin Name: FrE Credit
Plugin URI: http://enginethemes.com/
Description: Integrates the credit system with your FreelanceEngine site
Version: 1.2.6
Author: enginethemes
Author URI: http://enginethemes.com/
License: GPLv2
Text Domain: enginetheme
*/


global $pagenow;
define( 'FRE_CREDIT_CORE', '1');
if( ! defined('FRE_CREDIT_VERSION'))
	define( 'FRE_CREDIT_VERSION', '1.2.6');

if ( $pagenow == 'post.php' && isset( $_GET['post'] ) ){
    $post_id = $post_ID = (int) $_GET['post'];
    $post = get_post( $post_id );
    $post_type = $post->post_type;
    // if current is Page
    if($post_type == 'page') return;
}

/**
* Run this plugin after setup theme
* @param void
* @return void
* @since 1.0
* @package AE_ESCROW
* @category FRE CREDIT
* @author Jack Bui
*/
function fre_credit_require_theme_file(){


    add_action( 'wp_enqueue_scripts', 'fre_credit_enqueue_scripts_new' );
    require_once dirname(__FILE__) . '/class-credit-withdraw.php';
    require_once dirname(__FILE__) . '/container-withdraws.php';
    require_once dirname(__FILE__) . '/settings.php';
    $fre_withdraw = FRE_Credit_Withdraw::getInstance();
    $fre_withdraw->init();
    if( !ae_get_option('user_credit_system', false) ){
        return;
    }
    require_once dirname(__FILE__) . '/template.php';
    require_once dirname(__FILE__) . '/functions.php';
    require_once dirname(__FILE__) . '/class-credit-plans.php';
    require_once dirname(__FILE__) . '/class-credit-history.php';
    require_once dirname(__FILE__) . '/class-credit-currency-exchange.php';
    require_once dirname(__FILE__) . '/class-credit-currency.php';
    require_once dirname(__FILE__) . '/class-credit-wallet.php';
    require_once dirname(__FILE__) . '/class-credit-users.php';
    require_once dirname(__FILE__) . '/class-credit-employer.php';
    require_once dirname(__FILE__) . '/class-credit-escrow.php';
    require_once dirname(__FILE__) . '/update.php';

    $fre_credit_employer = FRE_Credit_Employer::getInstance();
    $fre_credit_employer->init();
    FRE_Credit_Plan_Posttype()->init();
    FRE_Credit_History()->init();
    FRE_Credit_Users()->init();
    FRE_Credit_Escrow()->init();
}
add_action('after_setup_theme', 'fre_credit_require_theme_file');
/**
* Enqueue script for FRE CREDIT
* @param void
* @return void
* @since 1.0
* @package FREELANCEENGINE
* @category FRE CREDIT
* @author Jack Bui
*/
function fre_credit_enqueue_scripts_new(){
    global $user_ID;
    if( !ae_get_option('user_credit_system', false) ){
        return;
    }

  $available = FRE_Credit_Users()->getUserWallet($user_ID);
    $page = ae_get_option('fre_credit_deposit_page_slug', false);
    if( $page && is_page($page) ){
        do_action('ae_payment_script');
    }
    wp_enqueue_style('fre_credit_css',get_template_directory_uri() . '/includes/fre_credit/assets/fre_credit_plugincss.css', array(), ET_VERSION );
    wp_enqueue_script('fre_credit_js', get_template_directory_uri(). '/includes/fre_credit/assets/fre_credit_pluginjs.js', array(
        'underscore',
        'backbone',
        'appengine',
        'front'
    ), ET_VERSION, true);
    $credit_api = ae_get_option( 'escrow_credit_settings' );

    wp_localize_script('fre_credit_js', 'fre_credit_globals', array(
        'currency' => ae_get_option('currency'),
        'number_mgs' => sprintf(__('Value must be greater than or equal to %s!', ET_DOMAIN), ae_get_option('fre_credit_minimum_withdraw', 50)),
        'minimum_withdraw' => ae_get_option('fre_credit_minimum_withdraw', 50),
        'not_enought_mgs' => __('Your available credit is not enough to request for withdrawal!', ET_DOMAIN),
        'unable_withdraw_text' => sprintf(__('Unable to withdraw money. Your available credit must be greater or equal to %s!', ET_DOMAIN), ae_get_option('fre_credit_minimum_withdraw', 50)),
        'available_of_user' => $available->balance,
        'balance_format' => fre_price_format($available->balance),
        'balance_number_format' => round((double)$available->balance, 2),
        'no_transaction_msg' => __("<div class='no-transaction'><span>There isn't any transaction!</span></div>", ET_DOMAIN),
        'text_acceptance_bid' => array(
                'success' => __("Credits in your account will be deducted to make the payment", ET_DOMAIN),
                'fail' => __("<i class='fa fa-warning'></i>Your available credit isn't enough to proceed the payment. Please top up the credit", ET_DOMAIN)
            ),
        'url_deposit' => fre_credit_deposit_page_link(),
        // 'is_credit_escrow' => isset($credit_api['use_credit_escrow']) ? $credit_api['use_credit_escrow'] : false ,
        'is_credit_escrow' => is_use_credit_escrow() ,

    ));
}
/**
  * enqueue script for admin page
  *
  * @param void
  * @return void
  * @since 1.0
  * @package FREELANCEENGINE
  * @category FRE CREDIT
  * @author Jack Bui
  */
function fre_credit_admin_enqueue_script_new($hook) {
    if( current_user_can( 'manage_options' ) ){
        wp_enqueue_script('fre_credit_admin_js', get_template_directory_uri().'/includes/fre_credit/assets/fre_credit_admin_pluginjs.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), ET_VERSION, true);
    }
    // plugin_dir_url(__FILE__)
}
add_action( 'admin_enqueue_scripts', 'fre_credit_admin_enqueue_script_new' );

if( ! function_exists('is_active_fcp')):

	/**
	 * @since: version 1.2.3
	 * @return boolean
	  * check to indicate the fre_credit_plus is actived or not.
	*/
	function is_active_fcp(){

			return true;

	}
endif;
if( ! function_exists('fre_credit_filter_plan')):
/**
 * filter array package of credit plans
 *
 * @param Array $request
 * @return Array $request
 * @since 1.0
 * @author ThanhTu
 */
function fre_credit_filter_plan($request){

    if( ! is_active_fcp() ){
    	$request['et_number_posts'] = $request['et_price'];
    }
    return $request;
}
endif;

add_filter( 'ae_filter_pack_fre_credit_plan', 'fre_credit_filter_plan' );

function fre_credit_check_rediect(){
    if(is_404() || is_single()) return false;
    global $post;
    $deposit_page = ae_get_option('fre_credit_deposit_page_slug', false);
    if(empty($post)) return;
    if($post->ID == $deposit_page){
        if(!is_user_logged_in()){
            $re_url = et_get_page_link('login').'?ae_redirect_url='.urlencode(fre_credit_deposit_page_link());
            wp_redirect($re_url);
            exit();
        }else if (!has_shortcode($post->post_content, 'fre_credit_deposit')){
            wp_redirect( home_url( '404' ), 302  );
            exit;
        }
    }
}
add_action('template_redirect', 'fre_credit_check_rediect');

if( !function_exists('credit_update_db_for_182') ):

function credit_update_db_for_182(){
	global $wpdb;

	$update_check = get_option( 'credit_update_db_for_182' );
	if ( ! ( $update_check ) && ae_get_option( 'update_db_for_182' )) {

		//update all transaction has status recieved to completed
		$wpdb->update($wpdb->postmeta,array('meta_value' => 'completed'),array('meta_key' => 'history_status', 'meta_value' => 'recieved'));

		// update status for project payment of employer
		$list_charge = get_posts(array(
			'post_status' =>'publish',
			'post_type' =>'fre_credit_history',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'history_status',
					'value' => 'completed',
				),
				array(
					'key' => 'history_type',
					'value' => 'charge',
				),
			)
		));

		if($list_charge){
			foreach ($list_charge as $v){
				if(!get_post_meta($v->ID,'is_commission',true)){
					$charge = FRE_Credit_History()->retrieveHistory($v->ID);
					$project_accept_id = get_post_meta($charge->ID,'project_accept',true);
					if(!empty($project_accept_id)){
						$project = get_post($project_accept_id);
						if(!empty($project)){
							switch ($project->post_status){
								case 'close' :
									update_post_meta($charge->ID,'history_status','pending');
									$bid_id = get_post_meta($project_accept_id,'accepted',true);
									$bid = get_post($bid_id);
									if(!empty($bid)){
										// check receive of freelancer
										$list_receive =  get_posts(array(
											'post_status' =>'publish',
											'post_type' =>'fre_credit_history',
											'posts_per_page' => 1,
											'author' => $bid->post_author,
											'meta_query' => array(
												array(
													'key' => 'history_type',
													'value' => 'transfer',
												),
												array(
													'key' => 'payment',
													'value' => $project->ID,
												),
											)
										));
										if(empty($list_receive)){
											// create credit transaction received pending for freelancer
											$bid_budget = get_post_meta($bid_id, 'bid_budget', true);
											$args_received_pending = array(
												'post_title' => 'Received',
												'post_author' => $bid->post_author,
												'history_type' => 'transfer',
												'status' => 'pending',
												'amount' => $bid_budget,
												'commission_fee' => 0,
												'payment' => $bid->post_parent,
												'destination' => $bid->post_author,
												'currency' => !empty($charge->currency->signal) ? $charge->currency->signal : '',
											);
											$payer_commission = get_post_meta($bid_id,'payer_of_commission',true);
											if($payer_commission == 'worker'){
												$args_received_pending['commission_fee'] = get_post_meta($bid_id,'commission_fee',true) ;
											}
											$history_id = FRE_Credit_History()->saveHistory($args_received_pending);

											if($history_id){
												$wpdb->update($wpdb->posts,array('post_date' => $charge->post_date,'post_date_gmt' => $charge->post_date),array('ID' => $history_id));
											}
										}
									}
									break;
								case 'disputing' :
									update_post_meta($charge->ID,'history_status','pending');
									$bid_id = get_post_meta($project_accept_id,'accepted',true);
									$bid = get_post($bid_id);
									if(!empty($bid)){
										// check receive of freelancer
										$list_receive =  get_posts(array(
											'post_status' =>'publish',
											'post_type' =>'fre_credit_history',
											'posts_per_page' => 1,
											'author' => $bid->post_author,
											'meta_query' => array(
												array(
													'key' => 'history_type',
													'value' => 'transfer',
												),
												array(
													'key' => 'payment',
													'value' => $project->ID,
												),
											)
										));
										if(empty($list_receive)){
											// create credit transaction received pending for freelancer
											$bid_budget = get_post_meta($bid_id, 'bid_budget', true);
											$args_received_pending = array(
												'post_title' => 'Received',
												'post_author' => $bid->post_author,
												'history_type' => 'transfer',
												'status' => 'pending',
												'amount' => $bid_budget,
												'commission_fee' => 0,
												'payment' => $bid->post_parent,
												'destination' => $bid->post_author,
												'currency' => !empty($charge->currency->signal) ? $charge->currency->signal : '',
											);
											$payer_commission = get_post_meta($bid_id,'payer_of_commission',true);
											if($payer_commission == 'worker'){
												$args_received_pending['commission_fee'] = get_post_meta($bid_id,'commission_fee',true) ;
											}
											$history_id = FRE_Credit_History()->saveHistory($args_received_pending);

											if($history_id){
												$wpdb->update($wpdb->posts,array('post_date' => $charge->post_date,'post_date_gmt' => $charge->post_date),array('ID' => $history_id));
											}
										}
									}
									break;
								case 'disputed' :
									$winner = get_post_meta($project->ID,'winner_of_arbitrate',true);
									if($winner and $winner == 'employer'){
										update_post_meta($charge->ID,'history_status','cancelled');

										// check bug not refund when employer win in old version
										$list_refund = get_posts(array(
											'post_status' =>'publish',
											'post_type' =>'fre_credit_history',
											'posts_per_page' => 1,
											'meta_query' => array(
												array(
													'key' => 'history_type',
													'value' => 'refund',
												),
												array(
													'key' => 'payment',
													'value' => $project->ID,
												),
											)
										));
										if(empty($list_refund)){
											// create transaction history
											$refund_obj = array(
												'post_title'  =>  'Refunded',
												'amount'      => $charge->amount,
												'currency'    => $charge->currency,
												'history_type'=> 'refund',
												'status'      => 'completed',
												'post_author' => $charge->post_author,
												'payment'     => $project_accept_id,
											);
											$history_id = FRE_Credit_History()->saveHistory($refund_obj);
											if($history_id){
												$wpdb->update($wpdb->posts,array('post_date' => $charge->post_date,'post_date_gmt' => $charge->post_date),array('ID' => $history_id));
											}

											//update  available credit for employer
											$fre_available = FRE_Credit_Users()->getUserWallet($charge->post_author);
											$new_balance = ($fre_available->balance) + intval($charge->amount);
											FRE_Credit_Users()->updateUserBalance($charge->post_author,$new_balance);
										}
									}

									break;
							}
						}
					}
				}
			}
		}

		add_option( 'credit_update_db_for_182', 'true' );
	}
}
endif;
add_action( 'wp_loaded', 'credit_update_db_for_182' );
