<?php
class MJE_OPP extends AE_Base {

	public static $instance;
	public static $opp_api;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$options       = MJE_OPP_Options::get_instance();
		self::$opp_api = $options->get_api_key();

		$this->add_filter( 'et_build_payment_visitor', 'build_payment_visitor', 10, 3 );
		$this->add_filter( 'mje_payment_list', 'add_payment_list' );
	}

	/**
	 * Check OPP enable or disable
	 *
	 * @param void
	 * @return boolean
	 * @since 1.0.0
	 * @author Tat Thien
	 */
	public static function is_active() {
		if ( ae_get_option( 'mje_opp_enable' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if admin provide publishable key and secret key or not
	 *
	 * @param void
	 * @return boolean
	 * @since 1.0.0
	 * @author Tat Thien
	 */
	public static function is_has_api_key() {
		if ( ! empty( self::$opp_api['api_key'] ) && ! empty( self::$opp_api['merchant_uid'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add payment visitor for OPP
	 *
	 * @param object   $class
	 * @param string   $payment_type
	 * @param ET_Order $order
	 * @return object $class
	 * @since 1.0.0
	 * @author Tat Thien
	 */
	public function build_payment_visitor( $class, $payment_type, $order ) {
		if ( $payment_type == 'OPP' ) {
			$class = new MJE_Opp_Visitor( $order );
		}

		return $class;
	}

	/**
	 * Add OPP to payment list
	 * this list is used for filter
	 *
	 * @param array $payment_list
	 * @return array $payment_list
	 * @since 1.1.4
	 * @author Tat Thien
	 */
	public function add_payment_list( $payment_list ) {
		$payment_list['OPP'] = __( 'OPP', 'mje_opp' );
		return $payment_list;
	}

	/**
	 * Get list of zero-decimal currencies
	 *
	 * @param void
	 * @return array
	 * @since 1.0.0
	 * @author Tat Thien
	 */
	public static function get_zero_decimal_currencies() {
		return apply_filters(
			'mje_opp_zero_decimal_currencies',
			array(
				'BIF',
				'DJF',
				'JPY',
				'KRW',
				'PYG',
				'VND',
				'XAF',
				'XPF',
				'CLP',
				'GNF',
				'KMF',
				'MGA',
				'RWF',
				'VUV',
				'XOF',
			)
		);
	}
}

$instance = MJE_OPP::get_instance();
