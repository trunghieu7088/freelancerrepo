<?php
/**
 * Version nãy hỗ trợ approve 1 order mà không cần đi tới page process-paymet.  Sau khi verify 1 order thành công từ server trả về, hệ thống tự động approve và thực hiện những process map với order đó.
 * Sử dụng class này cho Bitcoin pmgw only.
 * @since 1.8.19
 */
Class Fre_Order extends ET_Order {
	public $order;
	function __construct($order_id){
 		parent::__construct($order_id);
 		$this->payment_package 	= $this->payment_plan =  get_post_meta( $order_id, 'et_order_plan_id', true ); // sku
 		$this->type = $this->_product_type; //fre_credit_fix == deposit via fre credit plus plugin. deposit ,  fre_credit_plan == deposit via plan.
 		$this->ID = $this->_ID;
	}

	/** 1 order call this method 1 time only. make sure check before call.
	 * If the order was process => Skip call this method.
	 *
	 **/
	function payment_complete(){
		et_track_payment('Mark as complete payment. order_status =='.$this->_stat);
		if($this->_stat == 'publish'){
			return ;
		}
		$type 		= $this->_product_type;
		et_track_payment('product_type: '.$type);

		$args = array(
			'order_status' 	=> $this->_stat,
			'payment_type' 	=> $this->_product_type,
			'payment' 		=> $this->_payment, // payment gateway.
			'payment_type' 	=> $type,//bid_plan, bid_plan,fre_credit_plan
		);

		$order_a 	= $this->get_order(ARRAY_A);
		$order 		= (object) $order_a;
		if($type == 'fre_credit_fix' || $type == 'fre_credit_plan' ){
			et_track_payment('Call: fre_complete_deposit_payment().');
			fre_complete_deposit_payment($order); // User depsosite credit.
		} else if($type =='pack'){
			fre_purchase_pack_complete($order);// Employer purchase package & post project
		} else if($type == "bid_plan"){
			et_track_payment('Pay to bid type.');
			fre_complete_bid_plan_payment($order_a );
		}
		// cần kiểm tra lại content, trường hợp mua spedic có thể lỗi phần infor của package.
		et_track_payment('payment_complete ==> send_mail_to_admin+notification_to_admin');
		Fre_Mailing::get_instance()->new_payment_notification_mail($this->_ID); // send email to admin.
        AE_Mailing::get_instance()->send_receipt($this->_payer, $order_a); // send email to payer.
		Fre_Notification::getInstance()->new_payment_notification_onsite(array()); // insert notification.
	}
	function get_order($type = OBJECT) {
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
			'total'           => $this->_total, //et_order_total
			'paid_date'       => $this->_paid_date,
			'shipping'        => $this->_shipping,
			'payment_package' => $this->payment_package,
			'payment_plan' 	  => $this->payment_package,
			'type' 			  => $this->_product_type,
			'post_content' 	=> $this->_post_content,
		);
		if($type !== OBJECT)
			return $result;
		return (object) $result;
	}
	function get_id(){
		return $this->_ID;
	}
	function get_total(){
		return (float) $this->_total;
	}
	function update_status($status, $note = '' ){
		wp_update_post(array('ID' => $this->_ID,'post_status' => $status));
	}

	function get_status(){
		return $this->_stat;
	}
	function add_order_note($note){
		$post_content 	= $this->_post_content;
		$date  			=     date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
		$note.='<br /><br /><strong>Note : </strong>'.$note .' at '.$date;

	}
}