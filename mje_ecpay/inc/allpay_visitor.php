<?php

require_once(dirname( __FILE__ ) . '/ECPayPaymentHelper.php');

if( !class_exists('ECPay_PaymentCommon') ){

class ECPay_PaymentCommon{
    /**
     * 取得Helper
     * @return object
     */
    public static function getHelper()
    {
        $helper = new ECPayPaymentHelper();

        # 設定時區
        $helper->setTimezone(static::getTimezone());

        # 設定訂單狀態
        $helper->setOrderStatus(static::getOrderStatus());

        return $helper;
    }

    /**
     * 產生 Html
     * @return object
     */
    public static function genHtml()
    {
        $genHtml = new ECPayPaymentGenerateHtml();
        return $genHtml;
    }

    /**
     * 取得時區
     *
     * @return array
     */
    public static function getTimezone()
    {
        $timezone = (get_option('timezone_string') === '') ? date_default_timezone_get() : get_option('timezone_string');

        return $timezone;
    }

    /**
     * 訂單狀態
     *
     * @return array
     */
    public static function getOrderStatus()
    {
        $data = array(
            'Pending'    => 'pending',
            'Processing' => 'processing',
            'OnHold'     => 'on-hold',
            'Cancelled'  => 'cancelled',
            'Ecpay'      => 'ecpay',
        );

        return $data;
    }

    /**
     * 儲存訂單資訊
     * @param  integer $order_id 訂單編號
     * @return void
     */
    public static function ecpay_save_payment_order_info($data)
    {
        // 儲存測試模式訂單編號前綴
        $stage_order_prefix = isset($data['stage_order_prefix']) ? $data['stage_order_prefix'] : '' ;
        add_post_meta($data['order_id'], '_ecpay_payment_stage_order_prefix', sanitize_text_field($stage_order_prefix), true);

        // 儲存付款方式
        $notes_comment_content = isset($data['notes']) ? $data['notes']->comment_content : '' ;
        add_post_meta($data['order_id'], '_ecpay_payment_method', sanitize_text_field($notes_comment_content), true);

        // 是否做過訂單反查檢查，預設'N'(否)
        add_post_meta($data['order_id'], '_ecpay_payment_is_expire', sanitize_text_field($data['is_expire']), true);
    }
}

}
if(!class_exists('ECPay_CheckMacValue', false)){

    class ECPay_CheckMacValue{

        public static function generate($arParameters = array(),$HashKey = '' ,$HashIV = '',$encType = 0){
            $sMacValue = '' ;

            if(isset($arParameters)) {
                unset($arParameters['CheckMacValue']);
                uksort($arParameters, array('ECPay_CheckMacValue','merchantSort'));

                // 組合字串
                $sMacValue = 'HashKey=' . $HashKey ;
                foreach($arParameters as $key => $value){
                    $sMacValue .= '&' . $key . '=' . $value ;
                }

                $sMacValue .= '&HashIV=' . $HashIV ;

                // URL Encode編碼
                $sMacValue = static::ecpay_urlencode($sMacValue);

                // 編碼
                switch ($encType) {
                    case ECPay_EncryptType::ENC_SHA256:
                        // SHA256 編碼
                        $sMacValue = hash('sha256', $sMacValue);
                    break;

                    case ECPay_EncryptType::ENC_MD5:
                    default:
                    // MD5 編碼
                        $sMacValue = md5($sMacValue);
                }

                    $sMacValue = strtoupper($sMacValue);
            }

            return $sMacValue ;
        }

        /**
        * 自訂排序使用
        */
        private static function merchantSort($a,$b)
        {
            return strcasecmp($a, $b);
        }

        /**
         * URL Encode編碼，特殊字元取代
         *
         * @param  string $sParameters
         * @return string $sParameters
         */
        public static function ecpay_urlencode($sParameters) {

            // URL Encode編碼
            $sParameters = urlencode($sParameters);

            // 轉成小寫
            $sParameters = strtolower($sParameters);

            // 參數內特殊字元取代
            $sParameters = static::Replace_Symbol($sParameters);

            return $sParameters;
        }

        /**
        * 參數內特殊字元取代
        * 傳入    $sParameters    參數
        * 傳出    $sParameters    回傳取代後變數
        */
        public static function Replace_Symbol($sParameters){
            if(!empty($sParameters)){

                $sParameters = str_replace('%2D', '-', $sParameters);
                $sParameters = str_replace('%2d', '-', $sParameters);
                $sParameters = str_replace('%5F', '_', $sParameters);
                $sParameters = str_replace('%5f', '_', $sParameters);
                $sParameters = str_replace('%2E', '.', $sParameters);
                $sParameters = str_replace('%2e', '.', $sParameters);
                $sParameters = str_replace('%21', '!', $sParameters);
                $sParameters = str_replace('%2A', '*', $sParameters);
                $sParameters = str_replace('%2a', '*', $sParameters);
                $sParameters = str_replace('%28', '(', $sParameters);
                $sParameters = str_replace('%29', ')', $sParameters);
            }

            return $sParameters ;
        }

    }
}


class Allpay_setting{
    protected $_payment_type = 'allpay';
    public $mer_id;
    public $hash_key;
    public $hash_iv;
    public $payment_type ;
    public $OrderResultURL;
    public $ClientBackURL;
    public $expire_date;
    public $item_names;
    public $english;
    public $order_prefix;
    public $allpay_checkout_gateway;
    public $ecpay_checkout_gateway;
    public $allpay_inquery_url;
    public $ecpay_inquery_url;
    public $pay_use;
    public $testmode;
     // define Allpay checkout url
    const ALLPAY_CHECKOUT_PRODUCTION = 'https://payment.allpay.com.tw/Cashier/AioCheckOut';
    const ALLPAY_CHECKOUT_TEST = 'https://payment-stage.allpay.com.tw/Cashier/AioCheckOut';
    // define Allpay inquery url
    const ALLPAY_INQUERY_PRODUCTION = 'https://payment.allpay.com.tw/Cashier/QueryTradeInfo/V3';
    const ALLPAY_INQUERY_TEST = 'https://payment-stage.allpay.com.tw/Cashier/QueryTradeInfo/V3';
    // define Allpay checkout url
    // const ECPAY_CHECKOUT_PRODUCTION = 'https://payment.ecpay.com.tw/Cashier/AioCheckOut';
    // const ECPAY_CHECKOUT_TEST = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut';//4
    const ECPAY_CHECKOUT_PRODUCTION =  'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5';
    const ECPAY_CHECKOUT_TEST = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';
    // define Ecpay inquery url
    const ECPAY_INQUERY_PRODUCTION = 'https://payment.ecpay.com.tw/Cashier/QueryTradeInfo/V3';
    const ECPAY_INQUERY_TEST = 'https://payment-stage.ecpay.com.tw/Cashier/QueryTradeInfo/V3';

    function __construct() {
    	// $this->mer_id ='2000132';
    	// $this->hash_key ='5294y06JbISpM5x9';
    	// $this->hash_iv ='v77hoKGq4kWxNNIS';


    	// $this->mer_id ='3029737';
    	// $this->hash_key ='aNgmnHCxstjQQ6GK';
    	// $this->hash_iv ='3GyHWL1u8VfUNcpp';
    	$setting                = getEcpaySettings();
    	$this->mer_id          = $setting->mer_id;
    	$this->hash_key        = $setting->hash_key;
    	$this->hash_iv         =  $setting->hash_iv;
    	$this->IgnorePayment   = $setting->IgnorePayment;
    	$this->pay_use = 2; // default is 2 - ecpay
    	$pay_use =  (int) $setting->pay_use;
    	if(  $pay_use == 1 )
    	 $this->pay_use = 1;
    	//$this->pay_use = 2; // 1 is allpay, 2 - ecpay.
    	//WebATM#ATM#CVS#BARCODE#Alipay

    	$this->payment_type = 'aio';
    	$this->OrderResultURL  = et_get_page_link ('process-payment');
    	$this->ClientBackURL  = et_get_page_link ('process-payment');
    	$this->expire_date= 10;
    	/*$this->item_names = 'Iphone9';*/
    	$this->notify_url = et_get_page_link ('process-payment');
    	$this->process_payment = et_get_page_link ('process-payment');
    	$this->english = 0;
    	$this->order_prefix = 'WC';
    	$this->testmode = $this->get_test_mode();

    	$this->allpay_checkout_gateway = ( $this->testmode == 'yes' ) ? self::ALLPAY_CHECKOUT_TEST : self::ALLPAY_CHECKOUT_PRODUCTION;
        $this->ecpay_checkout_gateway = ( $this->testmode == 'yes' ) ? self::ECPAY_CHECKOUT_TEST : self::ECPAY_CHECKOUT_PRODUCTION;

        $this->allpay_inquery_url = ( $this->testmode == 'yes' ) ? self::ALLPAY_INQUERY_TEST : self::ALLPAY_INQUERY_PRODUCTION;
        $this->ecpay_inquery_url = ( $this->testmode == 'yes' ) ? self::ECPAY_INQUERY_TEST : self::ECPAY_INQUERY_PRODUCTION;

    }
    function get_logo(){
    	$img_url = MJE_PAYPALL_URL.'/img/logo1.png';
    	if($this->pay_use == 2)
    		$img_url = MJE_PAYPALL_URL.'/img/logo2.png';
    	return "<img src='".$img_url."' width='96px' />";
    }
    function get_test_mode(){

        if( in_array( $this->mer_id, array('2000132','2000214') ) ){

            return 'yes';
        }

        return 'no';

    	 if( !isset($options['test_mode']) || ( isset($options['test_mode']) && $options['test_mode'] == 1 ) )  {
            $testmode = 'yes';
        }

        return $testmode;
    }

    function get_checkout_url(){
    	if($this->pay_use == 2){
    		return $this->ecpay_checkout_gateway;
    	}
    	return $this->allpay_checkout_gateway;
    }
     function get_inquiry_url(){
    	if($this->pay_use == 2){
    		return $this->ecpay_inquery_url;
    	}
    	return $this->allpay_inquery_url;
    }

    function get_allpay_args($order_pay) {

        $order_id = $order_pay['ID'];
        $total_amount = round($order_pay['total']);

        $payment_type = $this->payment_type;

        $notify_url = $this->notify_url;
        $english = $this->english;

        //$return_url = add_query_arg( array('order_id' => $order_id,'type' => 'allpay'), $this->process_payment );
        //$return_url = add_query_arg( 'order-id', $order_id, $this->process_payment );
        $returnUrl  = add_query_arg('wc-api', 'WC_Gateway_Ecpay', home_url('/') );
        $return_url = $returnUrl;
       // $periodReturnURL   = add_query_arg('wc-api', 'WC_Gateway_Ecpay_DCA', home_url('/'));
        $process_payment    = et_get_page_link('process-payment');
        $clientBackUrl     = add_query_arg('order-id', $order_id, $process_payment );

        $allpay_args = array(
            'MerchantID' => $this->mer_id,
            'MerchantTradeNo' => $this->generate_merchant_trade_no($order_id),
            //'MerchantTradeNo' => 'MECPAY_ORDER__'.$order_id,
            'MerchantTradeDate' => current_time('Y/m/d H:i:s'),
            'PaymentType'   => $payment_type,
            'TotalAmount'   => $total_amount,
            'TradeDesc'     => get_bloginfo('name'),
            'ItemName'      => $this->item_names,
            'ChoosePayment' => 'ALL', //ALL
           // 'ChoosePayment' => 'Credit', //ALL
            //'periodReturnURL' => $periodReturnURL,
            'ReturnURL'      => $return_url, // reply url
            'PaymentInfoURL' => $return_url, // CVS, Barcode reply url
            'ClientBackURL' =>  $clientBackUrl ,
            'OrderResultURL' => '' ,
        );
        //$allpay_args['ExpireDate'] = $this->expire_date;
        if ($english) {
            $allpay_args['Language'] = 'ENG';
            $allpay_args['ChoosePayment'] = 'Credit';
        }
        $allpay_args['ExpireDate'] = 1; // might only availble when Choose Payment = 'ALl'
        return 	$allpay_args;
	}
    function get_the_check_mac_value($allpay_args) {

        $HashKey            = $this->hash_key;
        $HashIV             = $this->hash_iv;
        $EncryptType        = ECPay_EncryptType::ENC_MD5;
        $szCheckMacValue    = ECPay_CheckMacValue::generate($allpay_args,$HashKey,$HashIV, $EncryptType);

        return $szCheckMacValue;
    }

	function get_the_check_mac_value_Old($args) {

        $hash_key = $this->hash_key;
        $hash_iv = $this->hash_iv;
        $check_mac_value = $this->get_check_mac_value($args, $hash_key, $hash_iv);

        return $check_mac_value;
    }
    function get_check_mac_value( $args, $hash_key, $hash_iv, $hash_algo = "md5" ) {

        if (empty($args) || empty($hash_key) || empty($hash_iv)) {
            return false;
        }

        ksort($args, SORT_STRING | SORT_FLAG_CASE);
        $args_hash_key = array_merge(array('HashKey' => $hash_key), $args, array('HashIV' => $hash_iv));

        $args_string = '';
        foreach ($args_hash_key as $v => $k) {
            $args_string .= $v . '=' . $k . '&';
        }

        $args_string = rtrim($args_string, "&");
        $args_urlencode = urlencode($args_string);
        $args_urlencode = $this->inno_special_character_decode($args_urlencode);
        $args_to_lower = strtolower($args_urlencode);
        $check_mac_value = strtoupper(hash($hash_algo, $args_to_lower));

        return $check_mac_value;
    }

    function generate_merchant_trade_no($order_id) {

        $today = date("Ym");
        return 'ECPAY'.$today.''.$order_id;

        $time_stamp = (string) time();
        $unique_no = $this->order_prefix . $order_id . 'TS' . strrev($time_stamp);
        return substr($unique_no, 0, 20);
    }
    function inno_special_character_decode($string) {
        $char = array('%28', '%29', '%5b', '%5d');
        $mb_char = array('(', ')', '[', ']');

        return str_replace($char, $mb_char, $string);
    }
     function check_allpay_response() {

        @ob_clean();
        $ipn_response = !empty($_POST) ? $_POST : false;
        $is_valid_request = @$this->check_ipn_response_is_valid($ipn_response);

        if ($is_valid_request) {
            header('HTTP/1.1 200 OK');
            do_action("valid_allpay_ipn_request", $ipn_response);
        } else {
            die("0|ErrorMessage");
        }
    }
     function check_ipn_response_is_valid($ipn_response, $validate = 0) {

        $ipn_check_mac_value = isset($ipn_response['CheckMacValue']) ? $ipn_response['CheckMacValue'] : '';

        unset($ipn_response['CheckMacValue']);

        $my_check_mac_value = $this->get_the_check_mac_value($ipn_response);

        if ($ipn_check_mac_value == $my_check_mac_value) {
            return true;
        } else {
            return false;
        }
    }

}
class ET_AllpaylVisitor extends ET_PaymentVisitor {
    function setup_checkout( ET_Order $order )    {
    	$allpay = new Allpay_setting();

        $order_pay =	$order->generate_data_to_pay();

        /* webnware, start */
        $order_data = $order->get_order_data();
        $item_names = $order_data['products'];
        $product = null;

        foreach($item_names as $k => $v){
            $product = $v;
        }

        $item_names = $product['NAME'];
        $allpay->item_names = $item_names;
        /* webnware, en */

       	$allpay_args = $allpay->get_allpay_args($order_pay);
       	$order_id = $order_pay['ID'];
       	et_write_session('order_id', $order_pay['ID']);
        et_write_session('process_type', 'buy');

        $allpay_args['IgnorePayment'] = $allpay->IgnorePayment; //trim($allpay['IgnorePayment']);//'WebATM#ATM#CVS#BARCODE#Alipay';


        $HashKey    = $allpay->hash_key;
        $HashIV     = $allpay->hash_iv;
        $EncryptType = ECPay_EncryptType::ENC_MD5;
        // $check_mac_value = $allpay->get_the_check_mac_value($allpay_args); v3 or v4
        $szCheckMacValue = ECPay_CheckMacValue::generate($allpay_args,$HashKey,$HashIV, $EncryptType);

        $allpay_args['CheckMacValue'] = $szCheckMacValue; //new version

	    //$allpay_args['CheckMacValue'] = $check_mac_value; // old



	    update_post_meta( $order_id,'MerchantTradeNo', $allpay_args['MerchantTradeNo'] );
	    update_post_meta( $order_id,'mjob_order_id', $order_pay['product_id'] );

		$response = array(
                'success' => true,
                'ACK' => true,
                'api_ap'=>$allpay_args,
                'msg' => __( 'Congrats. Your payment is successful!', 'mje_stripe' ),
                'url' => home_url(),
            );

		return $response;
    }

    function do_checkout( ET_Order $order )   {

        return 0;
        $allpay = new Allpay_setting();

        $payment_return = array();

        @ob_clean();
        $ipn_response = !empty($_POST) ? $_POST : false;
        $is_valid_request = $allpay->check_ipn_response_is_valid($ipn_response);
        ecpay_log('Ecpay do_checkout');
        header('HTTP/1.1 200 OK');

        if ($is_valid_request) {

            //do_action("valid_allpay_ipn_request", $ipn_response);
            $order->set_status( 'publish' );
            $order->update_order();


            if( isset($ipn_response['RtnCode']) && ('1' == $ipn_response['RtnCode']) ){
                $payment_return = array(
                    'ACK' => true,
                    'payment' => 'allpay',
                    'payment_status' => 'Completed'
                );
            } else {
                $payment_return = array(
                'ACK' => false,
                    'payment' => 'allpay',
                    'payment_status' => 'Pending',
                );
            }

        } else {
        	ecpay_log('Ecpay do checkout => fail');
        	$order->set_status( 'pending' );
            $order->update_order();

            $payment_return = array(
                'ACK' => false,
                'payment' => 'stripe',
                'payment_status' => 'Pending',
            );

            die("0|ErrorMessage");
        }



        return $payment_return;
    }
}


/**
 *
 *  Clone function receive_response of class WC_Gateway_Ecpay
 * Path: plugins/ecpay-payment-for-woocommerce/includes/class-wc-gateway-ecpay.php
 *
 **/

class ecpaymentVerify{
    public $helper;
    public $ecpay_merchant_id;
    public $ecpay_hash_key;
    public $ecpay_hash_iv;
    public $description;
    public $title;


    function __construct(){
        $setting        = new Allpay_setting();
        $HashKey        = $setting->hash_key;
        $HashIV         = $setting->hash_iv;
        $EncryptType    = ECPay_EncryptType::ENC_MD5;


        $this->title                = 'title';
        $this->description           = 'des';
        $this->ecpay_merchant_id     = $setting->mer_id;
        $this->ecpay_hash_key        = $HashKey;
        $this->ecpay_hash_iv         = $HashIV;

       // add_action( 'init', array($this, 'ecpay_receive_response') );
        add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
        add_action( 'mje_api_wc_gateway_ecpay', array( $this, 'ecpay_receive_response' ), 0 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
        add_action( 'init', array( $this, 'add_endpoint' ), 0 );


    }
    public static function add_endpoint() {
        // REST API, deprecated since 2.6.0.
        add_rewrite_rule( '^wc-api/v([1-3]{1})/?$', 'index.php?wc-api-version=$matches[1]&wc-api-route=/', 'top' );
        add_rewrite_rule( '^wc-api/v([1-3]{1})(.*)?', 'index.php?wc-api-version=$matches[1]&wc-api-route=$matches[2]', 'top' );
    }
    public function isTestMode($merchantId = ''){
        $stageMerchantIds = array('2000132', 'ECPay_PaymentCommon');
        return in_array($merchantId, $stageMerchantIds) ? 'yes' : 'no';
    }

    /**
     *   clon public function receive_response()
     *
     **/
    function handle_api_requests() {

        global $wp;

        if ( ! empty( $_GET['wc-api'] ) ) { // WPCS: input var okay, CSRF ok.
            $wp->query_vars['wc-api'] = sanitize_key( wp_unslash( $_GET['wc-api'] ) ); // WPCS: input var okay, CSRF ok.
        }



        if ( ! empty( $wp->query_vars['wc-api'] ) ) {

            ecpay_log('wc-api ok.');
            // Buffer, we won't want any output here.
            //ob_start();
            ecpay_log($_POST);
            ecpay_log('add hook: mje_api_wc_gateway_ecpay');
            do_action( 'mje_api_wc_gateway_ecpay' );

            // No cache headers.
            wc_nocache_headers();

            // Clean the API request.
            $api_request = strtolower( wc_clean( $wp->query_vars['wc-api'] ) );

            // Make sure gateways are available for request.
           // WC()->payment_gateways();

            // Trigger generic action before request hook.
           // do_action( 'woocommerce_api_request', $api_request );

            // Is there actually something hooked into this API request? If not trigger 400 - Bad request.
            status_header( has_action( 'woocommerce_api_' . $api_request ) ? 200 : 400 );

            // Trigger an action which plugins can hook into to fulfill the request.
            //do_action( 'woocommerce_api_' . $api_request );



            // Done, clear buffer and exit.
            //ob_end_clean();
            die( '-1' );
        }

    }
    public function add_query_vars( $vars ) {
        $vars[] = 'wc-api-version'; // Deprecated since 2.6.0.
        $vars[] = 'wc-api-route'; // Deprecated since 2.6.0.
        $vars[] = 'wc-api';
        return $vars;
    }
    function ecpay_receive_response() {
        ecpay_log('call: ecpay_receive_response');
        $result_msg = '1|OK';
        $order = null;

        $CheckMacValue  = isset($_POST['CheckMacValue']) ? $_POST['CheckMacValue'] :'';
        $RtnCode        = isset($_POST['RtnCode']) ? $_POST['RtnCode'] : '';
        if( empty($CheckMacValue) || empty($RtnCode) ){
            ecpay_log('empty CheckMacValue or RtnCode.');
            return ;
        }

        $this->helper = ECPay_PaymentCommon::getHelper();
        $this->helper->setMerchantId($this->ecpay_merchant_id);
        $this->ecpay_test_mode = ($this->helper->isTestMode($this->ecpay_merchant_id)) ? 'yes' : 'no';

        // ecpay_log('IPN Response : ');
        // ecpay_log($_POST);
        $comments = '';
        try {
            # Retrieve the check out result
            $data = array(
                'hashKey'   => $this->ecpay_hash_key,
                'hashIv'    => $this->ecpay_hash_iv,
            );

            $ecpay_feedback = $this->helper->getValidFeedback($data);

            ecpay_log('ecpay_feedback:');
            ecpay_log($ecpay_feedback);
            if (count($ecpay_feedback) < 1) {
                ecpay_log('exit. feedback failed');
                ecpay_log('exit. feedback failed');
                throw new Exception('Get ECPay feedback failed.');
            } else {
                # Get the cart order id
                $mjob_order_id = $mje_order_id =  $ecpay_feedback['MerchantTradeNo'];
                //if ($this->ecpay_test_mode == 'yes') {
                    $mjob_order_id = substr($ecpay_feedback['MerchantTradeNo'], 12);
                     //WC2332TS7288 043461 ==> 04361.
                    //WC23 32TS 7288
                    $mjob_order_id = substr($ecpay_feedback['MerchantTradeNo'], 11); //ECPAY202202 194  generate_merchant_trade_no
                    //"ECPA Y202 201 {$order_id}

               // }
                ecpay_log('ecpay_feedback:');
                ecpay_log($ecpay_feedback);
                $cart_order_id = $mjob_order_id;
                ecpay_log('mjob_order_id: '.$mjob_order_id);
                // ecpay_log('ecpay_feedback: ');
                // ecpay_log($ecpay_feedback);
                // ecpay_log('order: ');
                // ecpay_log($cart_order_id);


                # Get the cart order amount
               //$order = new MJE_Order($cart_order_id);
                //ecpay_log('Order Object: ');

                //$cart_amount = $order->get_total();
               // $products = $order['products'];

                $order = new MJE_Order($cart_order_id);
                $product = $order->get_products();
                $order_data = $order->get_order_data();
                $ad = get_post($order_data['product_id']);
                $mjob_order_id = $ad->ID;

                $pay    = array_shift($product);

                $order_amount = round($pay['AMT']);


                // global $wp_query, $ae_post_factory, $user_ID;
                // $mjob_order_obj = $ae_post_factory->get( 'mjob_order' );
                // $p_order    = get_post($cart_order_id);


                // $order = $current = $mjob_order_obj->convert($p_order);
                // ecpay_log($order);
                // $order_amount =  $current->amount;


                # Check the amounts
                $ecpay_amount = $ecpay_feedback['TradeAmt'];

                ecpay_log('verify order_id : '.$cart_order_id);
                ecpay_log('ecpay_amount: '.$ecpay_amount);
                ecpay_log('order_amount: '.$order_amount);

                //ecpay_amount: 5
                //cart_amount:

                if ($order_amount != $ecpay_amount) {
                    ecpay_log(' Gia khong khop.');
                    throw new Exception('Order ' . $cart_order_id . ' amount are not identical.');
                } else {
                    # Set the common comments
                    $comments = sprintf(
                        $this->tran('Payment Method : %s<br />Trade Time : %s<br />'),
                        esc_html($ecpay_feedback['PaymentType']),
                        esc_html($ecpay_feedback['TradeDate'])
                    );

                    # Set the getting code comments
                    $return_code        = esc_html($ecpay_feedback['RtnCode']);
                    $return_message     = esc_html($ecpay_feedback['RtnMsg']);
                    $get_code_result_comments = sprintf(
                        $this->tran('Getting Code Result : (%s)%s'),
                        $return_code,
                        $return_message
                    );

                    # Set the payment result comments
                    $payment_result_comments = sprintf(
                        $this->tran(' Payment Result : (%s)%s'),
                        $return_code,
                        $return_message
                    );

                    # Set the fail message
                    $fail_msg = sprintf('Order %s Exception.(%s: %s) 111', $cart_order_id, $return_code, $return_message);

                    # Get ECPay payment method
                    $ecpay_payment_method = $this->helper->getPaymentMethod($ecpay_feedback['PaymentType']);

                    # Set the order comments

                    // 20170920
                    ecpay_log('ecpay_payment_method: '. $ecpay_payment_method);

                    switch($ecpay_payment_method) {
                        case ECPay_PaymentMethod::Credit:
                            if ($return_code != 1 and $return_code != 800) {
                                ecpay_log('return_code: '.$return_code);
                                throw new Exception($fail_msg);
                            } else {

                                $order_id       = $cart_order_id;


                                $mje_order      = new MJE_Order($cart_order_id);

                                $payment_type   = 'ecpay';

                                $payment_return = array(
                                    'ACK'               => true,
                                    'payment'           => $payment_type,
                                    'payment_status'    => 'Completed',
                                    'order'             => $mje_order,
                                );
                                $data                   = array();
                                $data['order']          = $mje_order;
                                $data['payment_type']   = $payment_type;

                                do_action( 'mje_after_process_payment', $payment_return, $data );
                                $order = get_post($cart_order_id);
                                if( $order && !is_wp_error($order) && $order->post_status !== 'publish'){
                                    wp_update_post(
                                        array(
                                            'ID'            => $cart_order_id,
                                            'post_status'   => 'publish',
                                        )
                                    );
                                    ecpay_log('update order_status to publish.');
                                }

                                // ecpay_log('status update order status: '.$order->post_status);
                                // if ($order->post_status !== 'publish') {
                                //     ecpay_log('Update status order here: ');
                                //     //$this->confirm_order($order, $payment_result_comments, $ecpay_feedback);
                                //     $order->set_status( 'publish' );
                                //     $order->update_order();

                                //     // 增加ECPAY付款狀態
                                //     add_post_meta( $order->id, 'ecpay_payment_tag', 1, true);

                                //      wp_update_post(
                                //         array(
                                //             'ID' => $mjob_order_id,
                                //             'post_status' => 'publish'
                                //         )
                                //     );

                                // } else {
                                //     # The order already paid or not in the standard procedure, do nothing
                                //     //throw new Exception('The order already paid or not in the standard procedure ' . $cart_order_id . '.');
                                //     $nEcpay_Payment_Tag = get_post_meta($order->id, 'ecpay_payment_tag', true);
                                //     if($nEcpay_Payment_Tag == 0) {
                                //         //$order->add_order_note($payment_result_comments, ECPay_OrderNoteEmail::PAYMENT_RESULT_CREDIT);
                                //         add_post_meta( $order->id, 'ecpay_payment_tag', 1, true);
                                //     }
                                // }
                            }
                            break;

                        default:
                            throw new Exception('Invalid payment method of the order ' . $cart_order_id . '.');
                            break;
                    }
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (!empty($order)) {
                //$comment .= sprintf($this->tran('Failed To Pay<br />Error : %s<br />'), $error);
                // ecpay_log('Errole line 689');
                // var_dump($error);
                //ecpay_log($comments);
              //  $order->add_order_note($comments);
            }

            # Set the failure result
            $result_msg = '0|' . $error;
        }
        echo $result_msg;
        exit;
    }
    private function tran($content, $domain = 'ecpay') {
        if ($domain == 'ecpay') {
            return __($content, 'ecpay');
        } else {
            return __($content, 'woocommerce');
        }
    }
}
new ecpaymentVerify();
