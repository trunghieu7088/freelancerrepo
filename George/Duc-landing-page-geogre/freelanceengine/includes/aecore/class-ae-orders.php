<?php

class AE_Order extends ET_Order {

	protected $payment_package;
	protected $order_name;

	public function __construct( $order, $ship = array() ) {
		if ( is_array( $order ) ) {

			$this->payment_package 		= empty( $order['payment_plan'] ) ? '' : (string) $order['payment_plan'];
			$this->payment_plan    		= $this->payment_package;
			$this->order_name      		= empty( $order['order_name'] ) ? __( "Post ad", ET_DOMAIN ) : $order['order_name'];
			$this->_version   			= isset( $order['order_version'] ) ? $order['order_version'] : '';
			parent::__construct( $order, $ship );
			$this->update_order();
		} else {

			parent::__construct( $order, $ship );
			$this->_product_id  	= get_post_meta( $order, 'et_order_product_id', true );
			$this->payment_plan 	= get_post_meta( $order, 'et_order_plan_id', true );
			$this->payment_package 	= get_post_meta( $order, 'et_order_plan_id', true );
			$this->_version 		= get_post_meta($order,'et_order_version', true);
		}

	}

	function get_id(){
		return $this->_ID;
	}
	function get_total(){
		return (float) $this->_total;
	}
	public function get_order_data($type = ARRAY_A) {
		$result =  array(
			'ID'              => $this->_ID,
			'payer'           => $this->_payer,
			'product_id'      => $this->_product_id,
			'created_date'    => $this->_created_date,
			'status'          => $this->_stat,
			'payment'         => $this->_payment,
			'products'        => $this->_products,
			'currency'        => $this->_currency,
			'payment_code'    => $this->_payment_code,
			'total'           => $this->_total,
			'paid_date'       => $this->_paid_date,
			'shipping'        => $this->_shipping,
			'payment_package' => $this->payment_package,
			'payment_plan'    => $this->payment_plan,
			'version' 		  => $this->_version, // v1.8.19

		);
		if( $type == OBJECT ){
			return (object) $result;
		}
		return $result;

	}

	/**
	 * Override parent class
	 */
	function update_order() {
		parent::update_order();
		update_post_meta( $this->_ID, 'et_order_plan_id', $this->payment_package );
	}

	public function generate_data_to_pay($type = ARRAY_A) {
		$return                    = parent::generate_data_to_pay();
		$return['payment_package'] = $this->payment_package;
		$return['order_name']      = $this->order_name;
		$return['product_id']      = $this->_product_id;
		if($type == OBJECT) return (object) $return;
		return $return;
	}

	/**
	 * get orders
	 *
	 * @param array $args
	 *
	 * @return object $order_query
	 */
	public static function get_orders( $args = array() ) {
		$default_args = array(
			'payment'     => 0,
			'paged'       => 0,
			'post_status' => array(
				'pending',
				'publish',
				'failed', // paid amout less than order_price,
				// 'cancelled', // like draft but @ the status.
				// 'draft',
			),
			'post__in'    => ''
		);
		$args         = wp_parse_args( $args, $default_args );

		$args['post_type'] = self::POST_TYPE;

		if ( $args['payment'] ) {
			// $args['meta_key'] = 'et_order_gateway';
			// $args['meta_value'] = $args['payment'];
			$args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'   => 'et_order_gateway',
					'value' => $args['payment']
				)
			);
		}
		unset( $args['payment'] );
		$order_query = new WP_Query( $args );

		return $order_query;
	}

	public function set_payment_plan( $plan_id ) {
		$this->payment_plan = $plan_id;
	}
	/**
	 * $product: maybe a plan package.
	*/

	public function add_product( $product, $number = 1 ) {



        $product_price = $product['et_price'];

        $amt = $this->convert_currency($product_price,$this->_currency);

		$this->_products[ $product['ID'] ] = array(
			'ID'     => $product['ID'],
			'NAME'   => $product['post_title'],
			'AMT'    => $amt,
			'QTY'    => $number,
			'L_DESC' => $product['post_content'],
			'TYPE'   => $product['post_type']
		);
		$this->_total_before_discount = $amt;

		//$this->_total_before_discount      = (float) $this->_total_before_discount + number_format( (float) $product['et_price'] * $number, 2, '.', '' );
		$this->_total    = number_format( $this->calculate_discount( $this->_total_before_discount ), 2, '.', '' );

		$this->_product_id   = $product['post_id'];
		$this->_product_type = $product['post_type'];
		$this->update_order();
	}
}

