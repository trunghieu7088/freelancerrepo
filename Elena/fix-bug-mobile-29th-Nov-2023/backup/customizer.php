<?php
function je_customizer_init(){
	// 
	$current_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	if ( isset($_REQUEST['activate']) && $_REQUEST['activate'] == 'customizer' ){
		setcookie('et-customizer', '1', time() + 3600, '/');
		wp_redirect(remove_query_arg('activate'));
		exit;
	} else if (isset($_REQUEST['deactivate']) && $_REQUEST['deactivate'] == 'customizer') {
		setcookie('et-customizer', '', time() - 3600, '/');
		wp_redirect(remove_query_arg('deactivate'));
		exit;
	}
	if ( isset($_COOKIE['et-customizer']) && $_COOKIE['et-customizer'] == true ){
		add_action('wp_print_styles', 'et_customizer_print_styles');
		add_action('wp_print_scripts', 'et_customizer_print_scripts_child');
		add_action('wp_ajax_save-customization', 'et_customizer_save_child');
		add_action('wp_footer','et_customizer_panel');
	}else {
		add_action('wp_footer','et_customizer_trigger');
	}

	// check if customization is create or not
	if(!is_multisite() || (is_multisite() && get_current_blog_id() == 1) ) {
		if ( !file_exists( STYLESHEETPATH . '/css/customization.css' ) ){
			// save customization value into database
			$general_opt	=	ET_GeneralOptions::get_instance();
			$customization  = $general_opt->get_customization();
			$customization['pattern'] = "'" . $customization['pattern'] . "'";

			et_apply_customization_child($customization);
		}
	} else {
		$site_id	=	get_current_blog_id();
		if ( !file_exists( STYLESHEETPATH . '/css/customization_{$site_id}.css' ) ){
			$general_opt	=	new ET_GeneralOptions();
			$customization  = $general_opt->get_customization();
			
			if(isset($customization['pattern'])) {
				$customization['pattern'] = "'" . $customization['pattern'] . "'";
			}

			et_apply_customization_child($customization);
		}
	}
}
function et_customizer_save_child(){

	if ( !current_user_can('manage_options') ) return;

	try {
		$customization = $_REQUEST['content']['customization'];
		
		if( empty($customization['font-text']) )
			$customization['font-text'] = 'Arial, san-serif';
		if( empty($customization['font-heading']) )
			$customization['font-heading'] = 'Arial, san-serif';
		
		// create css style from less file
		$clone = $customization;
		$clone['pattern'] = "'" . $clone['pattern'] . "'";
		et_apply_customization_child($clone);

		// set new layout
		et_set_layout( empty($customization['layout']) ? 'content-sidebar' : $customization['layout'] );

		// save customization value into database
		$general_opt	=	new ET_GeneralOptions();
		$general_opt->set_customization( $customization );

		$resp = array(
			'success' 	=> true,
			'code' 		=> 200,
			'msg' 		=> __("Changes are saved successfully.", ET_DOMAIN),
			'data' 		=> $general_opt->get_customization()
		);
	} catch (Exception $e) {
		$resp = array(
			'success' 	=> false,
			'code' 		=> true,
			'msg' 		=> sprintf(__("Something went wrong! System cause following error <br/> %s", ET_DOMAIN) , $e->getMessage() )
		);
	}

	wp_send_json($resp);
	
}
function et_apply_customization_child($options = array(), $preview = false){
	$default = array(
		'background' 	=> '#ffffff',
		'header' 		=> '#4B4B4B',
		'heading' 		=> '#4B4B4B',
		'text' 			=> '#555555',
		'footer' 		=> '#E0E0E0',
		'action' 		=> '#E87863',
		'pattern' 		=> "'" . TEMPLATEURL . "/img/pattern.png'",
		'font-text' 	=> 'Arial, san-serif',
		'font-text-size' 	=> '14px',
		'font-heading' 		=> 'Arial, san-serif',
		'font-heading-size' 	=> '12px',
	);
	
	$options 	= wp_parse_args($options, $default);
	$keys 		= array_keys($default);

	foreach ($options as $key => $value) {
		if (!in_array($key,$keys)){
			unset($options[$key]);
		}
	}

	$less 	= STYLESHEETPATH  . '/css/customization.less';
	$css 	= STYLESHEETPATH  . '/css/customization.css';

	$mobile_less	=	STYLESHEETPATH  . '/css/customization-mobile.less';
	$mobile_css		=	STYLESHEETPATH  . '/mobile/css/customization.css';

	if( is_multisite() ) {
		$site_id	=	get_current_blog_id();

		if ( !file_exists( STYLESHEETPATH . '/css/customization_{$site_id}.css' ) ){
				$general_opt	=	new ET_GeneralOptions();
				$customization  = $general_opt->get_customization();
		}else {
				$css = STYLESHEETPATH  . "/css/customization_$site_id.css";
				$mobile_css		=	STYLESHEETPATH  . "/mobile/css/customization_$site_id.css";
		}
	}
	
	et_less2css( $less, $css, $options );
	et_less2css( $mobile_less, $mobile_css, $options );
}

function et_customizer_print_scripts_child(){
	if ( current_user_can('manage_options')&& !is_admin()){
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-slider');
		wp_register_script('et_customizer', get_template_directory_uri() . '/js/customizer.js', array('jquery','underscore','backbone', 'job_engine'), false, true);
		wp_enqueue_script('et_colorpicker', false, array('jquery', 'underscore', 'backbone'));
		wp_enqueue_script('et_customizer');
		?>
		<link rel="stylesheet/less" type="txt/less" href="<?php echo get_template_directory_uri() . '/css/define.less'?>">
		<?php
		wp_enqueue_script('less-js', get_template_directory_uri() . '/js/less-1.3.1.min.js');
	}
}

function et_get_customize_css_path_child () {
	/**
	 * add multisite check for customize style
	*/
	if(is_multisite() && get_current_blog_id() != 1 ) {
		$blog_id	=	get_current_blog_id();
		$customize_css 	=	 get_stylesheet_directory_uri() . "/css/customization_$blog_id.css";
	} else {
		$customize_css 	=	 get_stylesheet_directory_uri() . "/css/customization.css";
	}
	return $customize_css;
}
?>