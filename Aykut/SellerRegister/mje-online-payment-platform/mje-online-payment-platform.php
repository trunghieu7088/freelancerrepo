<?php
/**
 * Plugin Name: MjE Online Payment Plateform
 * Plugin URI: https://wpstriker.com/plugins
 * Description: Online Payment Plateform for MjE
 * Version: 1.0.0
 * Author: wpstriker
 * Author URI: https://wpstriker.com
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'WPS_MJE_ONLINE_PAYMENT_PLATEFORM_VERSION', '1.0.0' );
define( 'WPS_MJE_ONLINE_PAYMENT_PLATEFORM_FILE', __FILE__ );
define( 'WPS_MJE_ONLINE_PAYMENT_PLATEFORM_SLUG', 'wps-mje-online-payment-plateform' );
define( 'WPS_MJE_ONLINE_PAYMENT_PLATEFORM_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPS_MJE_ONLINE_PAYMENT_PLATEFORM_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WPS_MJE_ONLINE_PAYMENT_PLATEFORM_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

require_once WPS_MJE_ONLINE_PAYMENT_PLATEFORM_DIR . '/functions.php';

class WPS_MjE_Online_Payment_Plateform {

	protected static $instance;

	public static $opp_api;

	public $options;

	public function __construct() {
		self::$opp_api = $this->get_api_key();

		add_action( 'wp_loaded', array( $this, 'maybe_debug_page' ), 20 );  // TODO: Remove
		add_action( 'after_setup_theme', array( $this, 'load_payment_gateway_files' ), 20 );
		add_filter( 'mje_render_payment_name', array( $this, 'mje_opp_render_payment_name' ) );
		add_action( 'mje_after_payment_list', array( $this, 'mje_opp_render_button' ) );
		add_action( 'parse_request', array( $this, 'maybe_opp_ipn' ) );
		add_action( 'parse_request', array( $this, 'maybe_opp_merchant' ) );
		add_action( 'parse_request', array( $this, 'maybe_opp_bank_account' ) );
	}

	public function maybe_debug_page() {
		if ( ! isset( $_GET['_opp_api'] ) ) {
			return;
		}

		die();
	}

	public function get_api_key() {
		$this->options = get_option( 'et_options' );

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

	public function get_dashboard_url() {
		$dashboard_url = 'https://partner-dashboard.sandbox.onlinepaymentplatform.com/dashboard';
		if ( 'live' === self::$opp_api['mode'] ) {
			$dashboard_url = 'https://partner-dashboard.onlinepaymentplatform.com/dashboard';
		}

		return $dashboard_url;
	}

	public function maybe_opp_bank_account() {
		if ( 'bank_account' !== siget( 'opp_webhook' ) ) {
			return;
		}

		$raw_data = file_get_contents( 'php://input' );
		$data     = json_decode( $raw_data, true );

		$this->log( $data );

		if ( siar( $data, 'verification_hash' ) !== $this->get_verification_hash( siar( $data, 'uid' ) ) ) {
			$this->log( 'Hash verification failed!' );
			die();
		}

		// Change bank account status
		if ( 'bank_account.status.changed' === siar( $data, 'type' ) ) {
			$merchant_id = null;
			if ( 'merchant' === siar( $data, 'object_type' ) ) {
				$merchant_id = siar( $data, 'object_uid' );
			}
			if ( 'merchant' === siar( $data, 'parent_type' ) ) {
				$merchant_id = siar( $data, 'parent_uid' );
			}

			$user_id = $this->find_user_id_by_merchant( $merchant_id );

			if ( ! $user_id ) {
				$this->log( 'User id not found!' );
			}

			$bank_account = $this->get_object( siar( $data, 'object_url' ) );

			if ( siar( $data, 'object_uid' ) !== siar( $bank_account, 'uid' ) ) {
				$this->log( 'Bank account not found!' );
				die();
			}

			$this->log(
				array(
					__METHOD__,
					'user_id'       => $user_id,
					'verified_with' => siar( $bank_account, 'verified_with' ),
					$bank_account,
				)
			);

			if ( ! empty( siar( $bank_account, 'verified_with' ) ) ) {
				update_user_meta( $user_id, 'mje_opp_bank_account_verified', 'yes' );
				update_user_meta( $user_id, 'mje_opp_bank_account_verified_response', json_encode( $bank_account ) );
			}
		}

		die();
	}

	public function find_user_id_by_merchant( $merchant_id ) {
		global $wpdb;
		return $wpdb->get_var( 'SELECT `user_id` FROM `' . $wpdb->usermeta . "` WHERE `meta_key` = 'mje_opp_merchant_uid' AND `meta_value` = '" . $merchant_id . "'" );
	}

	public function maybe_opp_merchant() {
		if ( 'merchant' !== siget( 'opp_webhook' ) ) {
			return;
		}

		$raw_data = file_get_contents( 'php://input' );
		$data     = json_decode( $raw_data, true );

		$this->log( $data );

		if ( siar( $data, 'verification_hash' ) !== $this->get_verification_hash( siar( $data, 'uid' ) ) ) {
			$this->log( 'Hash verification failed!' );
			die();
		}

		if ( 'contact.status.changed' === siar( $data, 'type' ) ) {
			$merchant_id = null;
			if ( 'merchant' === siar( $data, 'object_type' ) ) {
				$merchant_id = siar( $data, 'object_uid' );
			}
			if ( 'merchant' === siar( $data, 'parent_type' ) ) {
				$merchant_id = siar( $data, 'parent_uid' );
			}

			$user_id = $this->find_user_id_by_merchant( $merchant_id );

			if ( ! $user_id ) {
				$this->log( 'User id not found!' );
			}

			$contact = $this->get_object( siar( $data, 'object_url' ) );

			if ( siar( $data, 'object_uid' ) !== siar( $contact, 'uid' ) ) {
				$this->log( 'Contact not found!' );
				die();
			}

			$this->log(
				array(
					__METHOD__,
					'user_id'       => $user_id,
					'verified_with' => siar( $contact, 'verified_with' ),
					$contact,
				)
			);

			if ( ! empty( siar( $contact, 'verified_with' ) ) ) {
				update_user_meta( $user_id, 'mje_opp_identity_verified', 'yes' );
				update_user_meta( $user_id, 'mje_opp_identity_verified_response', json_encode( $contact ) );
			}
		}

		if ( 'merchant.compliance_requirement.status.changed' === siar( $data, 'type' ) ) {
			$merchant_id = null;
			if ( 'merchant' === siar( $data, 'object_type' ) ) {
				$merchant_id = siar( $data, 'object_uid' );
			}
			if ( 'merchant' === siar( $data, 'parent_type' ) ) {
				$merchant_id = siar( $data, 'parent_uid' );
			}

			$user_id = $this->find_user_id_by_merchant( $merchant_id );

			if ( ! $user_id ) {
				$this->log( 'User id not found!' );
			}

			$merchant = $this->get_object( siar( $data, 'object_url' ) );

			if ( siar( $data, 'object_uid' ) !== siar( $merchant, 'uid' ) ) {
				$this->log( 'Merchant not found!' );
				die();
			}

			$this->log(
				array(
					__METHOD__,
					'user_id'           => $user_id,
					'compliance_status' => siars( $merchant, 'compliance/status' ),
					$merchant,
				)
			);

			if ( 'verified' === siars( $merchant, 'compliance/status' ) ) {
				update_user_meta( $user_id, 'mje_opp_seller_verified', 'yes' );
				update_user_meta( $user_id, 'mje_opp_seller_verified_response', json_encode( $merchant ) );
			}
		}

		die();
	}

	public function maybe_opp_ipn() {
		if ( 'opp_ipn' !== siget( 'ae_page' ) ) {
			return;
		}

		$raw_data = file_get_contents( 'php://input' );
		$data     = json_decode( $raw_data, true );

		$this->log( $data );

		if ( siar( $data, 'verification_hash' ) !== $this->get_verification_hash( siar( $data, 'uid' ) ) ) {
			$this->log( 'Hash verification failed!' );
			die();
		}

		if ( 'transaction.status.changed' !== siar( $data, 'type' ) ) {
			$this->log( 'Not a transaction status change notification!' );
			die();
		}

		$transaction = $this->get_object( siar( $data, 'object_url' ) );

		if ( siar( $data, 'object_uid' ) !== siar( $transaction, 'uid' ) ) {
			$this->log( 'Transaction not found!' );
			die();
		}

		$order_id = null;
		if ( siar( $transaction, 'metadata' ) ) {
			foreach ( siar( $transaction, 'metadata' ) as $meta ) {
				if ( 'order_id' === siar( $meta, 'key' ) ) {
					$order_id = siar( $meta, 'value' );
					break;
				}
			}
		}

		if ( empty( $order_id ) ) {
			$order_id = siget( 'oid' );
		}

		if ( empty( $order_id ) ) {
			$this->log( 'Order id not found!' );
			die();
		}

		$this->log( 'Order id: ' . $order_id );

		$post_data = array(
			'ID'          => $order_id,
			'post_status' => 'publish',
		);
		wp_update_post( $post_data );

		update_post_meta( $order_id, 'et_paid', 1 );
		update_post_meta( $order_id, 'mje_opp_trxn_uid', siar( $transaction, 'uid' ) );
		die();
	}

	public function create_merchant_bank_account( $args ) {
		$merchant_bank_url = 'https://api-sandbox.onlinebetaalplatform.nl/v1/merchants/' . siar( $args, 'uid' ) . '/bank_accounts';
		if ( 'live' === self::$opp_api['mode'] ) {
			$merchant_bank_url = 'https://api.onlinebetaalplatform.nl/v1/merchants/' . siar( $args, 'uid' ) . '/bank_accounts';
		}

		unset( $args['uid'] );

		$response_raw = wp_remote_get(
			$merchant_bank_url,
			array(
				'method'      => 'POST',
				'timeout'     => 15,
				'redirection' => 5,
				'sslverify'   => false,
				'headers'     => array(
					'Authorization' => 'Bearer ' . self::$opp_api['api_key'],
					'Content-Type'  => 'application/json',
				),
				'body'        => wp_json_encode( $args ),
			)
		);
		$response     = wp_remote_retrieve_body( $response_raw );
		return json_decode( $response, true );
	}

	public function create_merchant( $args ) {
		$merchant_url = 'https://api-sandbox.onlinebetaalplatform.nl/v1/merchants';
		if ( 'live' === self::$opp_api['mode'] ) {
			$merchant_url = 'https://api.onlinebetaalplatform.nl/v1/merchants';
		}

		$response_raw = wp_remote_get(
			$merchant_url,
			array(
				'method'      => 'POST',
				'timeout'     => 15,
				'redirection' => 5,
				'sslverify'   => false,
				'headers'     => array(
					'Authorization' => 'Bearer ' . self::$opp_api['api_key'],
					'Content-Type'  => 'application/json',
				),
				'body'        => wp_json_encode( $args ),
			)
		);
		$response     = wp_remote_retrieve_body( $response_raw );
		return json_decode( $response, true );
	}

	public function get_merchant( $merchant_id ) {
		$merchant_url = 'https://api-sandbox.onlinebetaalplatform.nl/v1/merchants/' . $merchant_id . '?expand[]=contacts';
		if ( 'live' === self::$opp_api['mode'] ) {
			$merchant_url = 'https://api.onlinebetaalplatform.nl/v1/merchants/' . $merchant_id . '?expand[]=contacts';
		}

		$response_raw = wp_remote_get(
			$merchant_url,
			array(
				'timeout'     => 15,
				'redirection' => 5,
				'sslverify'   => false,
				'headers'     => array(
					'Authorization' => 'Bearer ' . self::$opp_api['api_key'],
				),
			)
		);
		$response     = wp_remote_retrieve_body( $response_raw );
		return json_decode( $response, true );
	}

	public function get_object( $object_url ) {
		$response_raw = wp_remote_get(
			$object_url,
			array(
				'timeout'     => 15,
				'redirection' => 5,
				'sslverify'   => false,
				'headers'     => array(
					'Authorization' => 'Bearer ' . self::$opp_api['api_key'],
				),
			)
		);
		$response     = wp_remote_retrieve_body( $response_raw );
		return json_decode( $response, true );
	}

	public function get_verification_hash( $payload ) {
		return hash_hmac( 'sha256', $payload, self::$opp_api['notification_secret'] );
	}

	public function load_payment_gateway_files() {
		require_once WPS_MJE_ONLINE_PAYMENT_PLATEFORM_DIR . '/class-mje-opp-admin.php';
		require_once WPS_MJE_ONLINE_PAYMENT_PLATEFORM_DIR . '/class-mje-opp-options.php';
		require_once WPS_MJE_ONLINE_PAYMENT_PLATEFORM_DIR . '/gateway/class-mje-opp.php';
		require_once WPS_MJE_ONLINE_PAYMENT_PLATEFORM_DIR . '/gateway/class-mje-opp-visitor.php';
	}

	public function mje_opp_render_button() {

		if ( MJE_OPP::is_active() ) :
			$disable_class = '';
			$tooltip       = '';
			$checkout_type = 'checkout_order';
			if ( is_page_template( 'page-post-service.php' ) ) {
				$checkout_type = 'checkout_package'; // add in version 1.2
			}

			if ( ! MJE_OPP::is_has_api_key() ) {
				$disable_class = 'disable-gateway';
				$tooltip       = 'data-toggle="tooltip" data-placement="top" data-original-title="' . __( 'You can not use this checkout method because of missing API key.', 'mje_opp' ) . '"';
			}
			?>
			<li>
				<div class="outer-payment-items hvr-underline-from-left <?php echo $disable_class; ?>" <?php echo $tooltip; ?>>
					<a href="#" id="opp-gateway" class="btn-submit-price-plan select-payment" data-checkout-type="<?php echo $checkout_type; ?>" data-type="OPP" >
						<img src="<?php echo WPS_MJE_ONLINE_PAYMENT_PLATEFORM_URL . '/OPP_Yellow.svg'; ?>" alt="OPP logo">
						<p class="text-bank"><?php _e( 'Online Payment Plateform', 'mje_opp' ); ?></p>
					</a>
				</div>
			</li>
			<?php
		endif;
	}

	public function mje_opp_render_payment_name( $payment_name ) {
		$value        = '<p class="payment-name opp" title="' . __( 'Online Payment Plateform', 'mje_opp' ) . '"><img src="' . WPS_MJE_ONLINE_PAYMENT_PLATEFORM_URL . '/OPP_Yellow.svg" /><span>' . __( 'Online Payment Plateform', 'mje_opp' ) . '</span></p>';
		$payment_name = wp_parse_args( $payment_name, array( 'OPP' => $value ) );
		return $payment_name;
	}

	public function get_country_code( $country_slug ) {
		$countries = array(
			'australien'  => 'AUS',
			'belgien'     => 'BEL',
			'niederlande' => 'NLD',
			'indien'      => 'IND',
			'italien'     => 'ITA',
			'daenemark'   => 'DNK',
			'deutschland' => 'DEU',
			'schweiz'     => 'CHE',
			'spanien'     => 'ESP',
			'uk'          => 'GBR',
			'us'          => 'USA',
			'vietnam'     => 'VNM',
		);

		return isset( $countries[ $country_slug ] ) ? $countries[ $country_slug ] : $country_slug;
	}

	public function get_log_dir( string $handle ) {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/' . $handle . '-logs';
		wp_mkdir_p( $log_dir );
		return $log_dir;
	}

	public function get_log_file_name( string $handle ) {
		if ( function_exists( 'wp_hash' ) ) {
			$date_suffix = date( 'Y-m-d', time() );
			$hash_suffix = wp_hash( $handle );
			return $this->get_log_dir( $handle ) . '/' . sanitize_file_name( implode( '-', array( $handle, $date_suffix, $hash_suffix ) ) . '.log' );
		}

		return $this->get_log_dir( $handle ) . '/' . $handle . '-' . date( 'Y-m-d', time() ) . '.log';
	}

	public function log( $message ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->debug( print_r( $message, true ), array( 'source' => WPS_MJE_ONLINE_PAYMENT_PLATEFORM_SLUG ) );
		} else {
			error_log( date( '[Y-m-d H:i:s e] ' ) . print_r( $message, true ) . PHP_EOL, 3, $this->get_log_file_name( WPS_MJE_ONLINE_PAYMENT_PLATEFORM_SLUG ) );
		}
	}

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

function WPS_MjE_Online_Payment_Plateform() {
	return WPS_MjE_Online_Payment_Plateform::get_instance();
}

$GLOBALS[ WPS_MJE_ONLINE_PAYMENT_PLATEFORM_SLUG ] = WPS_MjE_Online_Payment_Plateform();
