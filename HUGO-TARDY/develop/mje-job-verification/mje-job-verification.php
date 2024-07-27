<?php
/*
Plugin Name: MjE Job Verification
Plugin URI: https://www.enginethemes.com/
Description: Allow sellers to claim their mJob and protect their reputation from counterfeit sellers.
Version: 1.1.1
Author: EngineThemes
Author URI: https://www.enginethemes.com/
License: A "Slug" license name e.g. GPL2
Text Domain: mje_verification
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('MJE_CLAIM_URL', plugin_dir_url(__FILE__));
define('MJE_CLAIM_PATH', plugin_dir_path(__FILE__));
define('MJE_CLAIM_VERSION', '1.1.1');

if (!function_exists('mje_claim_require_plugin_files')) {
    function mje_claim_require_plugin_files()
    {
        if (!defined('MJOB')) {
            return;
        }
        require_once dirname(__FILE__) . '/update.php';
        require_once dirname(__FILE__) . '/function/mje-claim-posttype.php';
        require_once dirname(__FILE__) . '/function/mje-claim-function.php';
        require_once dirname(__FILE__) . '/function/mje-claim-frontend.php';
        require_once dirname(__FILE__) . '/function/mje-claim-backend.php';
        require_once dirname(__FILE__) . '/function/mje-claim-shortcodes.php';
    }
    add_action('init', 'load_claim_languages');
    add_action('after_setup_theme', 'mje_claim_require_plugin_files', 99);
}

if (!function_exists('load_claim_languages')) {
    function load_claim_languages()
    {
        load_plugin_textdomain('mje_verification', false,  dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

if (!function_exists('mje_claim_enqueue_scripts')) {
    function mje_claim_enqueue_scripts()
    {
        wp_enqueue_script('claim-frontendjs', MJE_CLAIM_URL . 'assets/js/frontend.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_CLAIM_VERSION, true);

        wp_enqueue_script('claim-listClaim', MJE_CLAIM_URL . 'assets/js/mylistClaim.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_CLAIM_VERSION, true);

        wp_enqueue_script('claim-labrary', MJE_CLAIM_URL . 'assets/js/labrary.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_CLAIM_VERSION, true);

        wp_localize_script('claim-frontendjs', 'wnm_th', array('url' => MJE_CLAIM_URL, 'path' => MJE_CLAIM_PATH, 'ajax' => admin_url('admin-ajax.php'), 'home_url' => home_url()));
        wp_enqueue_style('css-claim-admin', MJE_CLAIM_URL . 'assets/css/frontend.css');
    }
    add_action('wp_enqueue_scripts', 'mje_claim_enqueue_scripts');
}

if (!function_exists('mje_claim_script_admin')) {
    add_action('admin_enqueue_scripts', 'mje_claim_script_admin');
    function mje_claim_script_admin()
    {
        wp_enqueue_style('css-claim-admin', MJE_CLAIM_URL . 'assets/css/backend.css');
        wp_enqueue_style('claim-font-awesome', get_template_directory_uri() . '/assets/css/font-awesome.css');
        wp_enqueue_script('claim-backendjs', MJE_CLAIM_URL . 'assets/js/backend.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_CLAIM_VERSION, true);
        wp_enqueue_script('claim-hero', MJE_CLAIM_URL . 'assets/js/labrary.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_CLAIM_VERSION, true);

        wp_enqueue_script('claim-bootrap-js', get_template_directory_uri() . '/includes/aecore/assets/js/bootstrap.min.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_CLAIM_VERSION, true);

        wp_localize_script('claim-hero', 'wnm_th', array('p_url' => get_template_directory_uri() . '/images/', 'a_url' => admin_url('admin-ajax.php')));
        wp_localize_script('claim-backendjs', 'wnm_th', array('p_url' => get_template_directory_uri() . '/images/', 'a_url' => admin_url('admin-ajax.php')));
    }
}
