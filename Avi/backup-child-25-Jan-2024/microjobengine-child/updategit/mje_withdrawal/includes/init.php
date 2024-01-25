<?php
if( !function_exists( 'mje_awd_enqueue_scripts' ) ) {
    function mje_awd_enqueue_scripts() {
        global $post;
		wp_enqueue_script( 'awd-frontendjs', MJE_AWD_URL . 'assets/js/frontend.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_AWD_VERSION, true);

		wp_enqueue_script( 'mje-labrary', MJE_AWD_URL . 'assets/js/labrary.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_AWD_VERSION, true);

		if(isset($post->ID) && $post->ID == ae_get_option('awd_withdrawing_balance_page')) {
            wp_enqueue_script( 'credit-topup-front', mje_get_modules_uri( 'MJE_Topup' ) . '/assets/js/credit-topup-front.js', array(
                'jquery',
                'underscore',
                'backbone',
                'appengine',
            ), ET_VERSION, true );
        }

		wp_enqueue_style('awd-frontendcss', MJE_AWD_URL . 'assets/css/frontend.css');
		wp_localize_script('mje-labrary', 'awd', array('url' => MJE_AWD_URL,'path'=>MJE_AWD_PATH,'ajax'=>admin_url('admin-ajax.php'),'home_url'=>home_url()));
		wp_localize_script('awd-frontendjs', 'awd', array('url' => MJE_AWD_URL,'path'=>MJE_AWD_PATH,'ajax'=>admin_url('admin-ajax.php'),'home_url'=>home_url()));

    }
    add_action( 'wp_enqueue_scripts', 'mje_awd_enqueue_scripts' );
}

if( !function_exists( 'mje_awd_script_admin' ) ) {
	add_action('admin_enqueue_scripts', 'mje_awd_script_admin');
	function mje_awd_script_admin() {
		wp_enqueue_style('awd-backendcss', MJE_AWD_URL . 'assets/css/backend.css');
		wp_enqueue_style('font-awesome', get_template_directory_uri().'/assets/css/font-awesome.css');
		wp_enqueue_script( 'awd-backendjs', MJE_AWD_URL . 'assets/js/backend.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_AWD_VERSION, true);
		wp_enqueue_script( 'mje-labrary', MJE_AWD_URL . 'assets/js/labrary.js', array(
            'underscore',
            'backbone',
            'appengine'
        ), MJE_AWD_VERSION, true);

		wp_localize_script('mje-labrary', 'awd', array('url' => MJE_AWD_URL,'path'=>MJE_AWD_PATH,'ajax'=>admin_url('admin-ajax.php'),'home_url'=>home_url()));
		wp_localize_script('awd-backendjs', 'awd', array('url' => MJE_AWD_URL,'path'=>MJE_AWD_PATH,'ajax'=>admin_url('admin-ajax.php'),'home_url'=>home_url()));
	}
}

add_filter('mje_all_payment', 'default_payment_function',10,2);
if( !function_exists( 'default_payment_function' ) ) {
	function default_payment_function($payments){
		$payments['bank']= array(
				'args' => array(
					'title' => __( 'Bank', 'mjeawd' ),
					'icon'=>'',
				),
				'fields' => array(
					array(
						'id' => 'first_name',
						'title'=> __('First Name','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'middle_name',
						'title'=> __('Middle name','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'last_name',
						'title'=>__('Last Name','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'name',
						'title'=>__('Bank Name','mjeawd'),
						'class' => '',
						'type' => 'text',
					),
					array(
						'id' => 'swift_code',
						'title'=> __('SWIFT code','mjeawd'),
						'class' => '',
						'type' => 'text',
					),
					array(
						'id' => 'account_no',
						'title'=>__('Account number','mjeawd'),
						'class' => '',
						'type' => 'text',
					),

				)
		);
		$payments['paypal']= array(
				'args' => array(
					'title' => __( 'Paypal', 'mjeawd' ),
					'icon' =>'',
				),
				'fields' => array(
					//custom code here
					array(
						'id' => 'paypal_email',
						'title'=> __('Email','mjeawd'),
						'class' => '',
						'type' => 'text',
					),

					array(
						'id' => 'paypal_first_name',
						'title'=> __('First Name','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'paypal_middle_name',
						'title'=> __('Middle name','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'paypal_last_name',
						'title'=>__('Last Name','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'paypal_custom_address',
						'title'=>__('Address','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					array(
						'id' => 'paypal_custom_tel',
						'title'=>__('Tel','mjeawd'),
						'class' => 'cols3',
						'type' => 'text',
					),
					//end custom
				)
		);
		return $payments;
	}
}
?>