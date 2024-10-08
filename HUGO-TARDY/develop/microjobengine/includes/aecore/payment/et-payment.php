<?php

/**
# Version: this is the API version in the request.
# It is a mandatory parameter for each API request.
# The only supported value at this time is 2.3
 */

define('VERSION', '87.0');

// Ack related constants
define('ACK_SUCCESS', 'SUCCESS');
define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');

//define ('PAYMENT_MODE', 1 );


abstract class ET_Payment
{


	protected  $_order;
	protected  $_message;
	protected  $_submit_label;

	protected  $_errors   = array();
	protected  $_settings = array();

	//abstract function get_settings ( ) ;
	/**
	 * setup settings for ET_Payment
	 */
	//abstract function set_settings ( $settings = array () ) ;
	/**
	 * retrieve supported payment gateways
	 */
	public static function get_support_payment_gateway()
	{

		$support =	array(
			'paypal'	=>	array(
				'label' => 'Paypal', 'active' => -1,
				'description' => __("Send your payment via Paypal.", 'enginethemes')
			),

			'2checkout'	=>	array(
				'label' => '2CheckOut', 'active' => -1,
				'description' => __("Send your payment via 2Checkout.", 'enginethemes')
			),

			// 'google_checkout'	=>	array ( 'label' =>'Google Checkout', 'active' => -1 ,
			// 						'description' => __("Pay using your Google Wallet.",'enginethemes')),

			'cash'		=>	array(
				'label' => __('Cash', 'enginethemes'),  'active' => -1,
				'description' => __("Send your cash payment to our bank account", 'enginethemes')
			)
		);
		return apply_filters('et_support_payment_gateway', $support);
	}
	public static function get_currency_list()
	{
		/* Paypal support currency
		 * 	- U.S. Dollar, Australian Dollar, Canadian Dollar, Hong Kong Dollar, Singapore Dollar,
		 *  - Taiwan New Dollar, New Zealand Dollar, Euro, Swiss Franc, Czech Koruna, Swedish Krona,
		 *  - Danish Krone, Norwegian Krone, Hungarian Forint, Mexican Peso, Philippine Peso,
		 *  - Malaysian Ringgit, Chinese RMB, Israeli New Shekel, Pounds Sterling, Brazilian Real,
		 *  - Polish Zloty, Thai Baht, Japanese Yen.
		 */
		$currency	=	array(
			'AUD'	=> array(
				'code'	=>	'AUD',	'alt'	=>	__("Australian Dollar", 'enginethemes'),
				'label'	=>	'AUD',	'icon'	=> 	 'AUD',		'align'	=>	 'left'
			),
			'CAD'	=> array(
				'code'	=>	'CAD',	'alt'	=>	__("Canadian Dollar", 'enginethemes'),
				'label'	=>	'CAD',	'icon'	=> 	 'CAD',		'align'	=>	 'left'
			),
			'EUR'	=> array(
				'code'	=>	'EUR',	'alt'	=>	__("Euro", 'enginethemes'),
				'label'	=>	'EURO',	'icon'	=> 	 '&euro;',	'align'	=>	 'left'
			),
			'USD'	=> array(
				'code'	=>	'USD',	'alt'	=>	__("American Dollar", 'enginethemes'),
				'label'	=>	'USD',	'icon'	=>	'$', 		'align'	=>	'left'
			),
			'JPY'	=> array(
				'code'	=>	'JPY',	'alt'	=>	__("Japanese Yen", 'enginethemes'),
				'label'	=>	'JPY',	'icon'	=>	'&yen;',	'align'	=>	'left'
			),
			'GBP'	=> array(
				'code'	=>	'GBP',	'alt'	=>	__("British Pound", 'enginethemes'),
				'label'	=>	'GBP',	'icon'	=>	'&pound;',	'align'	=>	'left'
			),
			'NZD'	=> array(
				'code'	=>	'NZD',	'alt'	=>	__("New Zealand Dollar", 'enginethemes'),
				'label'	=>	'NZD',	'icon'	=>	'NZD',	'align'	=>	'left'
			),
			'CHF'	=> array(
				'code'	=>	'CHF',	'alt'	=>	__("Swiss Franc", 'enginethemes'),
				'label'	=>	'CHF',	'icon'	=>	'CHF',	'align'	=>	'left'
			),
			'HKD'	=> array(
				'code'	=>	'HKD',	'alt'	=>	__("Hong Kong Dollar", 'enginethemes'),
				'label'	=>	'HKD',	'icon'	=>	'HKD',	'align'	=>	'left'
			),
			'SGD'	=> array(
				'code'	=>	'SGD',	'alt'	=>	__("Singapore Dollar", 'enginethemes'),
				'label'	=>	'SGD',	'icon'	=>	'SGD',	'align'	=>	'left'
			)

		);
		return apply_filters('et_payment_currency_list',  $currency);
	}
	/*
	 * update current used code
	 */
	public static function set_currency($currency_code)
	{

		$currency_list	=	self::get_currency_list();
		if (isset($currency_list[$currency_code])) {
			update_option('et_current_currency', $currency_list[$currency_code]);
			return true;
		}
		return false;
	}
	/*
	 * get current used code
	 */
	public static function get_currency()
	{
		// $cur	=	 get_option('et_current_currency', true);
		// if( $cur == '' || empty($cur) || !is_array( $cur )) {
		// 	$cur 	=	 array (	'code'	=>	'USD',	'alt'	=>	__("American Dollar",'enginethemes'),
		// 				 		'label'	=>	'USD',	'icon'	=>	'$', 		'align'	=>	'left'
		// 					  );
		// }
		$options = AE_Options::get_instance();

		$currency = apply_filters('ae_change_code_currency', $options->currency);
		if (!isset($currency) || !$currency) {
			return array(
				'code' => 'USD',
				'icon' => '$',
				'align' => 'left',
			);
		}
		return $currency;
	}
	/*
	 * enable payment gateway
	 */
	public static function enable_gateway($gateway)
	{

		$support_gateway	=	self::get_support_payment_gateway();

		// if the gateway doesnot supported return false
		if (!isset($support_gateway[$gateway])) {
			return false;
		}

		$gateways	=	self::get_gateways();
		// init array if gateways have not set
		if (!is_array($gateways)) {
			$gateways	=	array();
		}

		$gateways[$gateway]				=	$support_gateway[$gateway];
		$gateways[$gateway]['active']	=	1;
		unset($gateways[$gateway]['description']);

		update_option('et_payment_gateways', $gateways);

		return true;
	}
	/**
	 * disable a payment gateway
	 * @param string $gateway : gateway key
	 */
	public static function disable_gateway($gateway)
	{

		$gateways	=	self::get_gateways();

		if (isset($gateways[$gateway])) {
			unset($gateways[$gateway]);
			update_option('et_payment_gateways', $gateways);
		}
		return true;
	}
	/*
	 * get available payment gateways
	 */
	public static function get_gateways()
	{
		// get available payment gateway from db
		$gateways			=	get_option('et_payment_gateways', true);

		$support_gateway	=	self::get_support_payment_gateway();

		// init array if gateways have not set
		if (!is_array($gateways)) {
			$gateways	=	array();
			$gateways['cash']				=	$support_gateway['cash'];
			$gateways['cash']['active']		=	1;
		}

		foreach ($gateways as $key => $value) {
			if (!isset($support_gateway[$key])) {
				unset($gateways[$key]);
				continue;
			}
			$gateways[$key]['description'] 	=	isset($support_gateway[$key]) ? $support_gateway[$key]['description'] : '';
			$gateways[$key]['label']		=	isset($support_gateway[$key]) ? $support_gateway[$key]['label'] : '';
		}


		return apply_filters('et_get_payment_gateways', $gateways);
	}
	/**
	 * get payment mode setting
	 * return bool : true : test, false is real
	 */
	public static function get_payment_test_mode()
	{
		$options = AE_Options::get_instance();
		return $options->test_mode;
	}
	/**
	 * set payment mode setting
	 * return bool : true : test, false is real
	 */
	public static function set_payment_test_mode($value)
	{
		return update_option('et_payment_mode', $value);
	}

	/**
	 * @return settings array
	 * @see ET_Payment::get_settings()
	 */
	function get_settings()
	{
		return $this->_settings;
	}
}


class ET_Paypal extends ET_Payment
{

	private $_test_mod;
	private $_type;
	/**
	# Endpoint: this is the server URL which you have to connect for submitting your API request.
	 */
	private $_api_endpoint;
	/**
	# Version: this is the API version in the request.
	# It is a mandatory parameter for each API request.
	# The only supported value at this time is 2.3
	 */
	private $_version;
	/*
		PayPal URL. This is the URL that the buyer is
 	    first sent to to authorize payment with their paypal account
	    change the URL depending if you are testing on the sandbox
	    or going to the live PayPal site
	    For the sandbox, the URL is
	    https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
	    For the live site, the URL is
	    https://www.paypal.com/webscr&cmd=_express-checkout&token=
	*/
	private $_paypal_url;

	private $_api_username, $_proxy;

	//private $_proxy;
	//private $_proxy_host;
	//private $_proxy_port;

	function __construct()
	{

		$mode	=	ET_Payment::get_payment_test_mode();
		$api	=	self::get_api();
		extract($api);
		// init api setting
		$this->_api_username	=	trim($api_username);
		//$this->_api_password	=	trim($api_password);
		//$this->_api_signature	=	trim($api_signature);

		$this->_api_endpoint	=	'https://api-3t.sandbox.paypal.com/nvp';
		$this->_version			=	87.0;

		$this->_proxy			=	false;
		$this->_test_mod		=	$mode;

		if ($this->_test_mod) { // in test mode : enpoint url is sandbox

			$this->_paypal_url	=	'https://www.sandbox.paypal.com/cgi-bin/webscr';
		} else { // in realtime

			$this->_paypal_url	=	'https://www.paypal.com/cgi-bin/webscr';
		}

		/*$default_settings	=	array (
			'return'	=> 	'http://localhost',
			'cancel'	=>	'http://localhost'
		);

		$settings			=	wp_parse_args( $settings, $default_settings );
		$this->_settings	=	$settings;*/
	}

	/**
	 * Function to perform the API call 3rd-Party Cart parameter
	 * @param string  $nvpstr
	 * @param string  $payment_type
	 */
	function set_checkout($nvpstr, $payment_type)
	{

		$payment_type	=	strtoupper($payment_type);

		if ($payment_type == 'SIMPLEPAYPAL') {

			// 2checkout url for direct purchase link using the 3rd-Party Cart parameters
			$paypal	=	$this->_paypal_url . "?cmd=_cart&no_note=1&rm=2&business=" . $this->_api_username . $nvpstr;

			return $paypal;
		} else {
			return false;
		}
	}
	/**
	 * Check if paypal request on pay success
	 * @author Duocnv
	 */
	public function check_ipn_response()
	{
		et_track_payment('Processing ::check_ipn_response() ');

		@ob_clean();
		// Insert the post into the database
		$ipn_response = !empty($_POST) ? $_POST : FALSE;
		if ($ipn_response) {
			ae_paypal_log("Check IPN response: ");

			if ($this->check_ipn_request_is_valid($ipn_response)) {
				et_track_payment('IPN_SUCCESS');
				header('HTTP/1.1 200 OK');
				et_track_payment('After call header() ');
				// Create post object
				$this->successful_request($ipn_response);
			} else {
				et_track_payment('IPN_FAIL');
				ae_paypal_log("Check IPN response invalid");
				// wp_die( "PayPal IPN Request Failure", "PayPal IPN", array ( 'response' => 200 ) );
			}
		}
	}
	/**
	 * description
	 * @param snippet
	 * @since snippet.
	 * @author Duocnv
	 */
	public function successful_request($posted)
	{

		ae_paypal_log('Processing ::successful_request()');
		// Insert the post into the database
		$posted = stripslashes_deep($posted);

		if (!empty($posted['invoice'])) {

			ae_paypal_log("Check IPN response: ");
			ae_paypal_log($posted);

			ae_paypal_log("Receive invoice: " . $posted['invoice']);
			// Since version 1.3.9.6
			$invoice 		= $posted['invoice'];
			$order_id 		= $invoice;
			$api    		= (array)get_option('et_paypal_api', true);
			$invoice_prefix = isset($api['invoice_prefix']) ? trim($api['invoice_prefix']) : 0;
			if ($invoice_prefix) {
				$ouput 		= explode($invoice_prefix, $invoice);
				$order_id 	= $ouput[1];
			}

			// end version 1.3.9.6

			$order_pay = new AE_Order($order_id);
			$order_pay->set_payment_code($_POST['txn_id']);
			$order_pay->set_payer_id($_POST['payer_id']);

			$order_data = $order_pay->generate_data_to_pay();
			$receiver_email = isset($posted['receiver_email']) ? $posted['receiver_email'] : '';
			$business       = isset($_POST['business']) ? $_POST['business'] : '';

			if (empty($receiver_email)) {
				$receiver_email = $business;
			}


			ae_paypal_log("Verify amount, currency, receiver email");
			ae_paypal_log('order_data:');

			ae_paypal_log($order_data);
			$this->verify_amount($order_data, $posted['mc_gross']);
			$this->verify_currency($order_data, $posted['mc_currency']);
			if (!empty($receiver_email))
				$this->verify_receiver_email($receiver_email);
			ae_paypal_log("Pass verify.");

			$posted['payment_status'] 	= strtolower($posted['payment_status']);
			$posted['txn_type'] 		= strtolower($posted['txn_type']);

			$sandbox_mode 	= isset($posted['test_ipn']) ? $posted['test_ipn'] : 0;

			if (1 == $sandbox_mode && 'pending' == $posted['payment_status']) {
				$posted['payment_status'] = 'completed';
			}
			switch ($posted['payment_status']) {
				case 'completed':
					$order_pay->set_status('publish');
					break;
				case 'pending':
					$order_pay->set_status('pending');
					break;
				case 'denied':
				case 'expired':
				case 'failed':
				case 'voided':
					$order_pay->set_status('draft');
					break;
			}

			$order_pay->update_order();

			$url	=	et_get_page_link('process-payment', array('order-id' => $order_id));
			ae_paypal_log("Redirect to: " . $url);

			wp_redirect($url); // version 1.3.9.6
			ae_paypal_log("Exit");
			exit;
		} else {
			ae_paypal_log("Invalid invoice");
		}
	}

	/**
	 * Verify order total & postback total (mc_gross)
	 * @param array $order
	 * @param float $amount
	 */
	public function verify_amount($order, $amount)
	{
		if (number_format($order['total'], 2, '.', '') != number_format($amount, 2, '.', '')) {
			ae_paypal_log("Your order total: " . $order['total'] . "- post back total " . $amount);
			ae_paypal_log("Fail. Exit verify amount");
			exit;
		}
	}

	/**
	 * Verify order currency & postback currency
	 * @param array $order
	 * @param string $currency
	 */
	public function verify_currency($order, $currency)
	{
		if ($order['currencyCodeType'] != $currency) {
			ae_paypal_log("Your order currency: " . $order['currencyCodeType'] . "- post back currency " . $currency);
			ae_paypal_log("Exit verify currency");
			exit;
		}
	}

	/**
	 * Verify receiver_email
	 * @param string $receiver_email
	 */
	public function verify_receiver_email($receiver_email)
	{
		if ($this->_api_username != $receiver_email) {
			ae_paypal_log("Your receiver email: " . $this->_api_username . "- post back receiver email " . $receiver_email);
			ae_paypal_log("Exit verify receiver email");
			exit;
		}
	}

	/**
	 * Check valid ipn
	 * @param array $ipn_response
	 * @return boolean
	 * @since snippet.
	 * @author Duocnv
	 */
	function check_ipn_request_is_valid($ipn_response)
	{
		$validate_ipn = array('cmd' => '_notify-validate');
		$validate_ipn += stripslashes_deep($ipn_response);
		$params = array(
			'body' 			=> $validate_ipn,
			'sslverify' 	=> false,
			'timeout' 		=> 60,
			'httpversion'   => '1.1',
			'compress'      => false,
			'decompress'    => false,
			'user-agent'	=> 'AppEngine'
		);
		// Post back to get a response

		$response = wp_remote_post($this->_paypal_url, $params);
		if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {
			return true;
		}
		return false;
	}
	/**
	 * @return settings array
	 * @see ET_Payment::get_settings()
	 */
	function get_settings()
	{
		return $this->_settings;
	}
	/**
	 * get paypal checkout url
	 * return string : url
	 */
	function get_paypal_url()
	{
		return $this->_paypal_url;
	}

	static function set_api($api = array())
	{
		update_option('et_paypal_api', $api);
		if (!self::is_enable()) {
			$gateways	=	self::get_gateways();
			if (isset($gateways['paypal']['active'])  && $gateways['paypal']['active'] != -1) {
				ET_Payment::disable_gateway('paypal');
				return __('Paypal option is disabled because of invalid setting.', 'enginethemes');
			}
		}
		return true;
	}

	static function get_api()
	{

		$api	= (array)get_option('et_paypal_api', true);
		if (!isset($api['api_username'])) $api['api_username']	=	'';
		if (!isset($api['api_password'])) $api['api_password']	=	'';
		if (!isset($api['api_signature'])) $api['api_signature']	=	'';
		return $api;
	}

	// function accept visitor
	function accept(ET_PaymentVisitor $visitor)
	{
		$visitor->visitSimplePaypal($this);
	}
	/**
	 * check paypal api setting available or not
	 */
	public static function is_enable()
	{
		$api	=	self::get_api();
		if (isset($api['api_username']) && $api['api_username'] != '')
			return true;
		return false;
	}
}

/** =====================================
 * 	Some function relate to payments
 * 	===================================== */

/**
 *
 */
function et_get_default_currency($object = OBJECT)
{
	switch ($object) {
		case OBJECT:
			return (object)ET_Payment::get_currency();
			break;

		case ARRAY_A:
			return ET_Payment::get_currency();
			break;

		default:
			return ET_Payment::get_currency();
			break;
	}
}

/**
 *
 */
function et_get_price_format($amount, $style = '')
{
	$currency = et_get_default_currency();
	$format = '%1$s';

	switch ($style) {
		case 'sup':
			$format = '<sup>%s</sup>';
			break;

		case 'sub':
			$format = '<sub>%s</sub>';
			break;

		default:
			$format = '%s';
			break;
	}

	$decimal		=	get_theme_mod('et_decimal', 2);
	$number_format = ae_get_option('number_format');

	$decimal_point	=	(isset($number_format['dec_point']) && $number_format['dec_point']) ? $number_format['dec_point'] : get_theme_mod('et_decimal_point', '.');
	$thousand_sep	=	(isset($number_format['thousand_sep']) && $number_format['thousand_sep']) ? $number_format['thousand_sep'] : get_theme_mod('et_thousand_sep', ',');

	if ($currency->align == "left") {
		$format = $format . '%s';
		return sprintf($format, $currency->icon, number_format((float)$amount, $decimal, $decimal_point, $thousand_sep));
	} else {
		$format = '%s' . $format;
		return sprintf($format, number_format((float)$amount, $decimal, $decimal_point, $thousand_sep),  $currency->icon);
	}
}

/**
 *
 */
function et_set_default_currency($code)
{
	return ET_Payment::set_currency($code);
}

/**
 * get supported payment gateways
 */
function et_get_support_gateways()
{
	$support	=	ET_Payment::get_support_payment_gateway();
	return $support;
}
function et_get_enable_gateways()
{
	$enable		=	ET_Payment::get_gateways();
	return $enable;
}
/**
 * get support currency list
 */
function et_get_currency_list()
{
	$list	=	ET_Payment::get_currency_list();
	return $list;
}

add_filter('et_payment_currency_list', 'et_payment_add_current');
function et_payment_add_current($list)
{
	$additional	=	get_option('et_currency_list', array());
	foreach ($additional as $key => $value) {
		$list[$key]	=	$value;
	}
	return $list;
}
/**
 * set currency
 * @param string $currency : currency code
 */
function et_set_currency($currency)
{
	return ET_Payment::set_currency($currency);
}

/**
 * enable a gateway
 * @param string $gateway
 * @param string $label
 */
function et_enable_gateway($gateway)
{
	$a	=	strtoupper($gateway);
	$available	=	true;
	switch ($a) {
		case 'PAYPAL':
			if (!ET_Paypal::is_enable())
				$available	=	 false;
			break;

		case '2CHECKOUT':
			if (!ET_2CO::is_enable())
				$available	= false;
			break;
		case 'GOOGLE_CHECKOUT':
			if (!ET_GoogleCheckout::is_enable())
				$available	= false;
			break;
		default:
			if (!ET_Cash::is_enable())
				$available	= false;
			break;
	}
	$available		=	apply_filters('et_enable_gateway', $available, $gateway);

	if ($available) {
		return ET_Payment::enable_gateway($gateway);
	} else return false;
}

/**
 * disable a gateway
 * @param string $gateway
 */
function et_disable_gateway($gateway)
{
	return ET_Payment::disable_gateway($gateway);
}
/**
 * update payment mode
 * @param bool
 */
function et_set_payment_test_mode($value)
{
	ET_Payment::set_payment_test_mode($value);
}
/**
 * get payment test mode
 * @return bool
 */
function et_get_payment_test_mode()
{
	return ET_Payment::get_payment_test_mode();
}
/**
 * update payment setting
 * @param name : string api key
 * @param value : string api value
 */
function et_update_payment_setting($name, $value)
{

	$paypal_api	=	ET_Paypal::get_api();
	$_2co_api	=	ET_2CO::get_api();
	$google		=	ET_GoogleCheckout::get_api();
	$value		=	trim($value);
	$msg		=	'';
	switch ($name) {
		case 'PAYPAL-APIUSERNAME':
			$validator	=	new ET_Validator();
			if ($value != '' && !$validator->validate('email', $value)) {
				$msg	=	__('Please fill in a valid email!', 'enginethemes');
				break;
			}
			$paypal_api['api_username']	=	$value;
			$msg	=	ET_Paypal::set_api($paypal_api);

			break;

		case '2CHECKOUT-SID':
			$_2co_api['sid']	=	$value;
			$msg	=	ET_2CO::set_api($_2co_api);
			break;

		case '2CHECKOUT-SECRETKEY':
			$_2co_api['secret_key']	=	$value;
			$msg	=	ET_2CO::set_api($_2co_api);
			break;
		case '2CO_USE_DIRECT':
			$_2co_api['use_direct']	=	$value;
			$msg	=	ET_2CO::set_api($_2co_api);
			break;
			break;
		case 'GOOGLE-MERCHANT-ID':
			$google['merchant_id']	=	$value;
			$msg					=	ET_GoogleCheckout::set_api($google);
			break;

		case 'GOOGLE-MERCHANT-KEY':
			$google['merchant_key']	=	$value;
			$msg					=	ET_GoogleCheckout::set_api($google);
			break;

		case 'CASH-MESSAGE':
			$msg	=	ET_Cash::set_message($value);
			break;

		default:
			$response	=	false;
			break;
	}

	$msg	=	apply_filters('et_update_payment_setting', $msg, $name, $value);
	if (is_string($msg)) {
		$response	=	array('success' => false, 'msg' => $msg);
	} else {
		$response	=	array('success' => true, 'msg' => $msg);
	}

	return $response;
}


/**
 * display currency code with the additional label
 * @param string $label : the string will be followed by an currency icon
 * @param string  $currency : currency code type
 * @param string $before_icon : could be html tag before currency icon
 * @param string $after_icon : could be html tag after currency icon
 */
function et_display_currency($label, $currency = array(), $before_icon = '', $after_icon = '', $echo = true)
{

	if (!is_array($currency) || !isset($currency['icon'])) {
		echo $label;
		return false;
	}
	$string 	=	'';
	if (isset($currency['icon'])) {

		$icon 	=	$currency['icon'];
		if ($icon != '') { // currency has an icon
			if (isset($currency['align']) &&  $currency['align'] == 'left')
				$string	=	 $before_icon . $icon . $after_icon . $label;
			else
				$string	= $label . $before_icon . $icon . $after_icon;
		} else { //currency does not have icon or icon = ''
			if ($label != $currency['label']) { //
				$string	= $label . $before_icon . $currency['label'] . $after_icon;
			} else {
				$string	= $label;
			}
		}
	}
	if ($echo) {
		echo $string;
	} else
		return $string;
}
