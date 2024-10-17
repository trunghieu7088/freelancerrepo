<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action('carbon_fields_register_fields', 'moving_platform_settings', 999, 0);

function moving_platform_settings()
{
    Container::make('theme_options', __('Moving Settings', 'crb'))
    ->set_icon('dashicons-admin-generic')
    ->set_page_menu_title('Moving Settings')
    ->set_page_menu_position(6)
    ->add_tab('Stripe API Key', array(
        Field::make('text', 'moving_stripe_pk', __('Stripe public key'))->set_default_value('none'),
        Field::make('text', 'moving_stripe_sk', __('Stripe secret key'))->set_default_value('none'),        
    ))
    ->add_tab('URL Settings', array(
        Field::make('text', 'after_post_request_redirect', __('Post Request Redirect'))->set_default_value('http://moving.et/all-requests/')->set_attribute( 'placeholder', 'https://part-dem.com/all-requests/' ),
        Field::make('text', 'author_url_profile', __('User Profile URL'))->set_default_value('profil')->set_attribute( 'placeholder', 'https://part-dem.com/profil/' ),              
    ))
    ->add_tab('General Settings', array(
        Field::make('select', 'moving_request_status', __('Default Status of Request'))->set_options(array('publish' => 'Publish','pending' => 'Pending')),
        Field::make('text', 'moving_request_price', __('Request Price (€)'))->set_default_value(20)->set_attribute( 'placeholder', '20' ),
        Field::make('text', 'moving_request_per_page', __('Request Per Page'))->set_default_value(20)->set_attribute( 'placeholder', '20' ),
        Field::make('text', 'max_upload_image', __('Allowed number of image uploads for posting request'))->set_default_value(5)->set_attribute( 'placeholder', '5' ),
        Field::make('text', 'budget_filter_list', __('Budget Filter'))->set_default_value('<1000,1000-3000,>=3000')->set_attribute( 'placeholder', '<1000,1000-3000,>=3000' ),    
        Field::make('text', 'tos_link', __('Term of service URL'))->set_default_value('https://part-dem.com/tos/')->set_attribute( 'placeholder', 'https://part-dem.com/tos/' ),      
    ))
    ->add_tab('Title of Pages', array(
        Field::make('text', 'post_request_page_title', __('Post Request Page'))->set_default_value('Post Moving Request'),
        Field::make('text', 'all_request_page_title', __('All Request Page'))->set_default_value('All Requests'),      
        Field::make('text', 'checkout_page_title', __('Check Out Page'))->set_default_value('Check out'),                 
    ))
    ->add_tab('Email Settings', array(
        Field::make( 'separator', 'crb_separator_1', __( 'Email sent to admin when new request submitted
' ) ),
        Field::make('text', 'notify_admin_new_request_subject', __('Title'))->set_default_value('A new request has been submitted'),
        Field::make( 'textarea', 'notify_admin_new_request_content', __( 'Content' ) )->set_rows( 20 )->set_default_value('A new request has been submitted [request_content]'),
        
        Field::make( 'separator', 'crb_separator_4', __( 'Email sent to customer to confirm their submitted request
        ' ) ),
        Field::make('text', 'notify_customer_confirm_request_subject', __('Title'))->set_default_value('Your request has been submitted'),
        Field::make( 'textarea', 'notify_customer_confirm_request_content', __( 'Content' ) )->set_rows( 20 )->set_default_value('Your request has been submitted [request_content]'),                

        Field::make( 'separator', 'crb_separator_2', __( 'Email sent to the movers when they purchased contact method successfully' ) ),
        Field::make('text', 'confirm_new_order_subject', __('Title'))->set_default_value('Your payment has been completed [payment_id]'),
        Field::make( 'textarea', 'confirm_new_order_content', __( 'Content' ) )->set_rows( 20 )->set_default_value('Your payment has ben completed. You can visit the paid list [paid_list]'),

        Field::make( 'separator', 'crb_separator_3', __( 'Email sent to the admin when there is a new payment.' ) ),
        Field::make('text', 'notify_admin_new_order_subject', __('Title'))->set_default_value('Notify about new payment [payment_id]'),                 
        Field::make('textarea', 'notify_admin_new_order_content', __('Content'))->set_rows( 20 )->set_default_value('A new payment has been sent to you [Payment_info].'),                 
    ));
}

add_action('init','create_admin_settings_value_instance',999);

function create_admin_settings_value_instance()
{
    class AdminData {
        private static $instance = null;
        private $data_value = [];
    
        private function __construct() {
            $this->load_data_value();
        }
    
        public static function get_instance() {
            if (self::$instance === null) {
                self::$instance = new AdminData();
            }
            return self::$instance;
        }

        private function load_data_value()
        {
            $this->data_value=[
                //stripe info
                'moving_stripe_pk'=> carbon_get_theme_option('moving_stripe_pk') ?: false,
                'moving_stripe_sk'=> carbon_get_theme_option('moving_stripe_sk') ?: false,

                //urls redirect
                'after_post_request_redirect'=> carbon_get_theme_option('after_post_request_redirect') ?: site_url('/all-requests/'),
                'author_url_profile'=> carbon_get_theme_option('author_url_profile') ?: site_url('/profil/'),

                //general settings
                
                'moving_request_status'=> carbon_get_theme_option('moving_request_status') ?: 'publish',                
                'moving_request_price'=> carbon_get_theme_option('moving_request_price') ?: 20,                
                'moving_request_per_page'=> carbon_get_theme_option('moving_request_per_page') ?: 20,
                'max_upload_image'=> carbon_get_theme_option('max_upload_image') ?: 5,                 
                'budget_filter_list'=> carbon_get_theme_option('budget_filter_list') ?: '<1000,1000-3000,>=3000',
                'tos_link'=> carbon_get_theme_option('tos_link') ?: 'https://part-dem.com/tos/',
                

                //titles of page
                'post_request_page_title'=> carbon_get_theme_option('post_request_page_title') ?: 'Post Moving Request',
                'all_request_page_title'=> carbon_get_theme_option('all_request_page_title') ?: 'All Requests',                 
                'checkout_page_title'=> carbon_get_theme_option('checkout_page_title') ?: 'Check out',                

                //email settings                
                'notify_admin_new_request_subject'=> carbon_get_theme_option('notify_admin_new_request_subject') ?: 'A new request has been submitted',
                'notify_admin_new_request_content'=> carbon_get_theme_option('notify_admin_new_request_content') ?: 'A new request has been submitted [request_content]',

                'notify_customer_confirm_request_subject'=> carbon_get_theme_option('notify_customer_confirm_request_subject') ?: 'Your Request has been submitted',
                'notify_customer_confirm_request_content'=> carbon_get_theme_option('notify_customer_confirm_request_content') ?: 'Your requrest has been submitted [request_content]',


                'confirm_new_order_subject'=> carbon_get_theme_option('confirm_new_order_subject') ?: 'Your payment has been completed [payment_id]',
                'confirm_new_order_content'=> carbon_get_theme_option('confirm_new_order_content') ?: 'Your payment has ben completed. You can visit the paid list [paid_list]',

                'notify_admin_new_order_subject'=> carbon_get_theme_option('notify_admin_new_order_subject') ?: 'Notify about new payment [payment_id]',
                'notify_admin_new_order_content'=> carbon_get_theme_option('notify_admin_new_order_content') ?: 'A new payment has been sent to you. [payment_price]',


            ];
        }

        public function getValue($key) {
            return isset($this->data_value[$key]) ? $this->data_value[$key] : null;
        }
    }    
}