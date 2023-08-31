<?php

/**
 * Class AE Payment is an abstract class handle function releate to payment setup , process payment
 *
 * @since 1.0
 * @package AE Payment
 * @category payment
 *
 * @property Array $no_priv_ajax Contain all no private ajax action name
 * @property Array $priv_ajax All private ajax action name
 *
 * @author Dakachi
 */
abstract class AE_Payment extends AE_Base {

	/**
	 * no private ajax
	 */
	protected $no_priv_ajax = array();

	// private ajax
	protected $priv_ajax = array(
		'et-setup-payment'
	);

	function __construct() {

		$this->init_ajax();
	}

	/**
	 * init ajax to process payment
	 *
	 * @return null
	 *
	 * @since 1.0
	 * @author Dakachi
	 */
	function init_ajax() {
		foreach ( $this->no_priv_ajax as $key => $value ) {
			$function = str_replace( 'et-', '', $value );
			$function = str_replace( '-', '_', $function ); //setup_payment here
			$this->add_ajax( $value, $function );
		}

		foreach ( $this->priv_ajax as $key => $value ) {
			$function = str_replace( 'et-', '', $value );
			$function = str_replace( '-', '_', $function );
			$this->add_ajax( $value, $function, true, false );
		}

		// catch action ae_save_option to update payment api settings
		$this->add_action( 'ae_save_option', 'update_payment_settings', 10, 2 );

		// process payment
		$this->add_action( 'ae_process_payment_action', 'process_payment', 10, 2 );
	}

	/**
	 * callback update option for Paypal, 2checkout, cash api settings
	 *
	 * @param String $name The payment gateway name
	 * @param String $value The payment gateway api value
	 *
	 * @return  null
	 *
	 * @since 1.0
	 * @author Dakachi
	 */
	public function update_payment_settings( $name, $value ) {

		// update paypal api settings
		if ( $name == 'paypal' ) {
			ET_Paypal::set_api( $value );
		}

		// update 2checkout api settings
		if ( $name == '2checkout' ) {
			ET_2CO::set_api( $value );
		}

		// update 2checkout api settings
		if ( $name == 'cash' ) {
			ET_Cash::set_message( $value['cash_message'] );
		}
	}

	/**
	 * abstract function get payment package for submit place
	 * @since 1.0
	 * @author Dakachi <ledd@youngworld.vn>
	 */
	abstract public function get_plans();

	/**
	 * catch action ae_process_payment_action and update post data after payment success
	 *
	 * @param $payment_return
	 * @param $data
	 *
	 * @return array $payment_return
	 *
	 * @since 1.0
	 * @author dakachi <ledd@youngworld.vn>
	 */
	function process_payment( $payment_return, $data ) {
		global $user_ID, $ae_post_factory;
		et_track_payment('process_payment =>: start processing.');
		// process user order after pay
		do_action( 'ae_select_process_payment', $payment_return, $data );
		$this->member_payment_process( $payment_return, $data );
		//  if not exist post id
		if ( ! isset( $data['ad_id'] ) || ! $data['ad_id'] ) {
			et_track_payment('process_payment: exit(ad_id is empty) => This is a deposit order.');
			return $payment_return;
		}

		$options = AE_Options::get_instance();
		$ad_id   = $data['ad_id'];

		extract( $data );
		if ( ! $payment_return['ACK'] ) {
			et_track_payment('process_payment: exit(ACK FAIL). ');
			return 0;
		}
		et_track_payment('process_payment => Update package infor + project status.');
		$post = get_post( $ad_id );
		/**
		 * get object by post type and convert
		 */
		$post_obj = $ae_post_factory->get( $post->post_type );
		$ad       = $post_obj->convert( $post );
		if ( $payment_type == 'free' ) {
			AE_Package::update_used_free_plan( $ad->post_author );
		}
		et_track_payment('process_payment : '.$payment_type);
		if ( $payment_type != 'usePackage' && isset($ad->et_payment_package) ) {
			/**
			 * update seller package quantity
			 */
			et_track_payment('process_payment : update quanlity.');
			AE_Package::update_package_data( $ad->et_payment_package, $ad->post_author );

			$post_data = array(
				'ID'          => $ad_id,
				'post_status' => 'publish'
			);

			if ( ! current_user_can( 'administrator' ) ) {
				$post_data = array(
					'ID'          => $ad_id,
					'post_status' => 'pending'
				);
			}
			/*Send email for admin if pending payment*/

			do_action( 'ae_after_process_payment', $post_data );
		}

		// disable pending and auto publish post


		/** @var string $payment_type */
		if ( ! $options->use_pending && ( 'cash' != $payment_type ) ) {
			$post_data['post_status'] = 'publish';
		}

		// when buy new package will got and order
		if ( isset( $data['order_id'] ) ) {

			// update post order id
			update_post_meta( $ad_id, 'et_invoice_no', $data['order_id'] );
		}

		// Change Status Publish places that posted by Admin
		if ( current_user_can( 'administrator' )  ) {
			wp_update_post( array(
				'ID'          => $ad_id,
				'post_status' => 'publish'
			) );
		}

		switch ( $payment_type ) {
			case 'cash':
				if( !empty($post_data) )
					wp_update_post( $post_data );

				// update unpaid payment
				update_post_meta( $ad_id, 'et_paid', 0 );

				return $payment_return;
			case 'free':
				wp_update_post( $post_data );

				// update free payment
				update_post_meta( $ad_id, 'et_paid', 2 );

				return $payment_return;
			case 'usePackage':
				$mail = new Fre_Mailing();
				$mail->new_project_of_category( $ad );
				return $payment_return;
			default:

				// code...
				break;
		}

		/**
		 * payment succeed
		 */
		if ( 'PENDING' != strtoupper( $payment_return['payment_status'] ) ) {
			if( isset($post_data['ID']) )
				wp_update_post( $post_data );

			// paid
			update_post_meta( $ad_id, 'et_paid', 1 );
		} else {

			/**
			 * in some case the payment will be pending
			 */
			wp_update_post( array(
				'ID'          => $ad_id,
				'post_status' => 'pending'
			) );

			// unpaid
			update_post_meta( $ad_id, 'et_paid', 0 );
		}

		if ( current_user_can( 'administrator' ) || ! ae_get_option( 'use_pending', false ) ) {
			do_action( 'ae_after_process_payment_by_admin', $ad_id );
		}

		return $payment_return;
	}

	/**
	 *
	 * @param snippet
	 *
	 * @return snippet
	 * @since snippet
	 * @package snippet
	 * @category snippet
	 * @author Dakachi
	 */
	function setup_orderdata( $data ) {
		global $user_ID;

		// remember to check isset or empty here
		$adID        = isset( $data['ID'] ) ? $data['ID'] : '';
		$author      = isset( $data['author'] ) ? $data['author'] : $user_ID;
		$packageID   = isset( $data['packageID'] ) ? $data['packageID'] : '';
		$paymentType = isset( $data['paymentType'] ) ? $data['paymentType'] : '';
		$currency_code 	 = isset( $data['currency_code'] ) ? $data['currency_code'] : '';
		$errors      = array();

		// job id invalid
		if ( $adID ) {

			// author does not authorize job
			$job = get_post( $adID );

			if ( $job->post_type != BID && $author != $job->post_author && ! current_user_can( 'manage_options' ) ) {
				$author_error = __( "Post author information is incorrect!", ET_DOMAIN );
				$errors[]     = $author_error;
			}

		}

		// input data error
		if ( ! empty( $errors ) ) {
			$response = array(
				'success' => false,
				'errors'  => $errors
			);

			wp_send_json( $response );
		}

		////////////////////////////////////////////////
		////////////// process payment//////////////////
		////////////////////////////////////////////////

		$order_data = array(
			'payer'        => $author,
			'total'        => '',
			'status'       => 'draft',
			'payment'      => $paymentType,
			'paid_date'    => '',
			'payment_plan' => $packageID,
			'post_parent'  => $adID,
			'currency_code' => $currency_code,
		);

		return $order_data;
	}

	function setup_payment() {

		$packageID 		= isset( $_POST['packageID'] ) ? $_POST['packageID'] : '';
		$paymentType 	= isset( $_POST['paymentType'] ) ? $_POST['paymentType'] : ''; // cash,
		$packageType 	= isset( $_POST['packageType'] ) ? $_POST['packageType'] : 'pack'; // pack, fre_credit_plan ,bid_plan

		$order_data 	= $this->setup_orderdata( $_POST );
		$plans 			= 0;
		if($packageID !== 'no_pack' ){
			$plans      = $this->get_plans();
		}

		$order 			= fre_create_draft_order($order_data, $plans); // insert order into database

		$arg = apply_filters( 'ae_payment_links', array(
			'return' => et_get_page_link( 'process-payment' ),
			'cancel' => et_get_page_link( 'process-payment' )
		) );
		/**
		 * process payment
		 */
		$paymentType_raw = $paymentType;
		$paymentType     = strtoupper( $paymentType );
		/**
		 * factory create payment visitor
		 */
		$visitor = AE_Payment_Factory::createPaymentVisitor( $paymentType, $order, $paymentType_raw );
		// setup visitor setting
		$visitor->set_settings( $arg );
		// accept visitor process payment
		$nvp = $order->accept( $visitor );
		// call to ObjectVisitor->setup_checkout(); and set et_order_version if this is new order version.
		if ( $nvp['ACK'] ) {
			$response = array(
				'success'     => $nvp['ACK'],
				'data'        => $nvp,
				'paymentType' => $paymentType
			);
		} else {
			$response = array(
				'success'     => false,
				'paymentType' => $paymentType,
				'msg'         => __( "Invalid payment gateway", ET_DOMAIN )
			);
		}
		/**
		 * filter $response send to client after process payment
		 *
		 * @param Array $response
		 * @param String $paymentType The payment gateway user select
		 * @param Array $order The order data
		 *
		 * @package  AE Payment
		 * @category payment
		 *
		 * @since  1.0
		 * @author  Dakachi
		 */
		$response = apply_filters( 'ae_setup_payment', $response, $paymentType, $order );
		wp_send_json( $response );
	}
	/**
	 * action process payment update seller order data
	 *
	 * @param Array $payment_return The payment return data
	 * @param Array $data Order data and payment type
	 *
	 * @return bool true/false
	 * @since  1.0
	 * @author  Dakachi
	 *
	 * @package AE Payment
	 */
	public function member_payment_process( $payment_return, $data ) {
		extract( $data );
		if ( !isset($payment_return['ACK']) || ! $payment_return['ACK'] ) {
			return false;
		}
		if ( $payment_type == 'free' ) {
			return false;
		}

		if ( $payment_type == 'usePackage' ) {
			return false;
		}

		$order_pay = $data['order']->get_order_data();

		// update user current order data associate with package
		self::update_current_order( $order_pay['payer'], $order_pay['payment_package'], $data['order_id'] );
		AE_Package::add_package_data( $order_pay['payment_package'], $order_pay['payer'] );

		/**
		 * do action after process user order
		 *
		 * @param $order_pay ['payer'] the user id
		 * @param $data The order data
		 */
		do_action( 'ae_member_process_order', $order_pay['payer'], $order_pay );

		return true;
	}

	/**
	 * return the order id user paid for the package
	 *
	 * @param integer $user_id The user ID
	 * @param integer $package_id The package id want to get order
	 *
	 * @return array $oder
	 *
	 * @since  1.0
	 * @author  Dakachi
	 */
	public static function get_current_order( $user_id, $package_id = '' ) {
		$order = get_user_meta( $user_id, 'ae_member_current_order', true );
		if ( $package_id == '' ) {
			if ( $order == '' ) {
				return unserialize( $order );
			} else {
				return $order;
			}
		} else {
			return ( isset( $order[ $package_id ] ) ? $order[ $package_id ] : '' );
		}
	}

	/**
	 * update user current order
	 *
	 * @param $user_id the user pay id
	 * @param $group array of order and package 'sku' => 'order_id'
	 *
	 * @return  null
	 *
	 * @since 1.0
	 * @author Dakachi
	 */
	public static function set_current_order( $user_id, $group ) {
		update_user_meta( $user_id, 'ae_member_current_order', $group );
	}

	/**
	 *  update order id user paid for package
	 *
	 * @param Integer $user_id The user ID
	 * @param Integer $package The package ID
	 * @param Integer $order_id The order ID want to update
	 *
	 * @return bool
	 */
	public static function update_current_order( $user_id, $package, $order_id ) {
		$group             = self::get_current_order( $user_id );
		$group[ $package ] = $order_id;


		/****** BUG RẤT LỚN Ở ĐÂY ******/


		return self::set_current_order( $user_id, $group );
	}
}