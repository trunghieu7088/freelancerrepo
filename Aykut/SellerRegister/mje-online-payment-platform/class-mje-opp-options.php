<?php

class MJE_OPP_Options {

	public static $instance;
	public $options;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->options = get_option( 'et_options' );
	}

	/**
	 * Get OPP API key based on test mode or live mode
	 *
	 * @param void
	 * @return array $opp_api
	 * @since 1.0.0
	 * @author Tat Thien
	 */
	public function get_api_key() {
		$opp_api = array();
		$mode    = 'live';
		if ( ! $this->options['mje_opp_production_mode'] ) {
			$mode = 'test';
		}

		$opp_api['mode']                = $mode;
		$opp_api['api_key']             = ! empty( $this->options[ 'mje_opp_' . $mode . '_api_key' ] ) ? $this->options[ 'mje_opp_' . $mode . '_api_key' ] : '';
		$opp_api['merchant_uid']        = ! empty( $this->options[ 'mje_opp_' . $mode . '_merchant_uid' ] ) ? $this->options[ 'mje_opp_' . $mode . '_merchant_uid' ] : '';
		$opp_api['notification_secret'] = ! empty( $this->options[ 'mje_opp_' . $mode . '_notification_secret' ] ) ? $this->options[ 'mje_opp_' . $mode . '_notification_secret' ] : '';

		return $opp_api;
	}
}
