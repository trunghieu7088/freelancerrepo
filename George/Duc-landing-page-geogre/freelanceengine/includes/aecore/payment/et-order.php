<?php
abstract class ET_Order {

    /*
     * order id : identify an order
    */
    protected $_ID;

    const POST_TYPE = ET_ORDER;
    /*
     * total money paid for order
    */
    protected $_total;
    protected $_total_before_discount;

    /*
     * currency code use in transaction or pay
    */
    protected $_currency;


    /*
     * buyer id : identify who pay for this order
    */
    protected $_payer;

    /*
     * payment type , identify kind of payment was used for this order
    */
    protected $_payment;

    /*
     * when payer pay success, payment gateway will return their code to us.
     * this field is use to store it. May be it can be used in refund, or check some info
     * when we get some complaints
    */
    protected $_payment_code;

    /*
     * list products of this order
     * 	- id
     *  - quantity
    */
    protected $_products;

    //single product
    protected $_product_id;
    /*
     * product type
     */
    protected $_product_type;
    /*
     * shipping infomation
     *  - ship option name
     *  - ship option label
     *  - ship option address
     *  - ship option amount
    */
    protected $_shipping;

    /*
     * order created date
    */
    protected $_created_date;

    /*
     * payer pay successful date
    */
    protected $_paid_date;

    /*
     * we use wp_posts table to store order data
     * so we need register post type order
    */
    protected $_stat;

    /*
     *  when pay order by paypal, paypal return we a payer id
     *  it's used in paypal when do express checkout
    */
    protected $_payer_id;

    protected $_setup_checkout;

    protected $_coupon_code;
    protected $_discount_rate;
    protected $_discount_method;

    protected $_post_parent;

    protected $_post_content;

    protected $_version;
    /**
     * construct an object order
     * @param int | array $order :
     * @param array $product
     * @param array $ship
     */
    function __construct($order, $ship = array()) {
        //$currency = ET_Payment::get_currency();
        $ae_currency    = ae_get_currency_checkout($order); // 1.8.10
        $code           = isset($ae_currency['code']) ? $ae_currency['code'] : 'USD';
        $currency_code  = $ae_currency['code'];
        if ( !is_array($order) ) {
            // construct a order by load data from database use order_id provided
            $order_id = intval($order);

            if ($order_id > 0) {
                $order = get_post($order_id, ARRAY_A);
                $this->_ID      = $order_id;
                $this->_payer   = $order['post_author'];
                $this->_post_content    = $order['post_content'];
                //$this->_payment	    =	$order['post_title'];
                $this->_created_date    = $order['post_date'];
                $this->_stat = $order['post_status'];
                $this->_payment = get_post_meta($this->_ID, 'et_order_gateway', true);
                $this->_product_id = get_post_meta($this->_ID, 'et_order_product_id', true);
                $this->_product_type = get_post_meta($this->_ID, 'et_order_product_type', true); // bid_plan, deposit fre_credit_plan
                $this->_products = get_post_meta($this->_ID, 'et_order_products', true);
                $this->_shipping = get_post_meta($this->_ID, 'et_order_shipping', true);
                $this->_currency = get_post_meta($this->_ID, 'et_order_currency', true);
                $this->_payment_code = get_post_meta($this->_ID, 'et_order_payment_code', true);
                $this->_total = get_post_meta($this->_ID, 'et_order_total', true);
                $this->_paid_date = get_post_meta($this->_ID, 'et_order_paid_date', true);
                $this->_version = get_post_meta($this->_ID,'et_order_version', true);
                /**
                 * coupon
                 */
                $this->_coupon_code = get_post_meta($this->_ID, 'et_order_coupon_code', true);
                $this->_discount_rate = get_post_meta($this->_ID, 'et_order_discount_rate', true);
                $this->_discount_method = get_post_meta($this->_ID, 'et_order_discount_method', true);

                if ($this->_currency == '')
                    $this->_currency = $currency_code;

                $this->_setup_checkout = false;

                return $order;
            }
            return false;
        } else {
             // contruct an order by new order and insert into database
            $default_order = array(

                'payer' => '',
                'currency' => $currency_code,
                //'products'			=>	 array(),
                'total' => '',
                'status' => 'pending',
                'payment' => 'cash',
                'create_date' => current_time('mysql') ,
                'paid_date' => '',
                'token' => '',
                'coupon_code' => '',
                'discount_rate' => '',
                'discount_method' => 'percent',
            );

            $order = wp_parse_args($order, $default_order);
            extract($order);

            if(empty($currency_code))
                $currency_code = $ae_currency['code'];

            $total = $this->convert_currency($total, $currency_code);

            $this->_payer = $payer;
            $this->_currency = $currency_code;
            $this->_currency_code = $currency_code;

            $this->_total = $total;
            $this->_stat = $status;
            $this->_payment = $payment;
            $this->_created_date = $create_date;
            $this->_paid_date = $paid_date;
            $this->_payment_code = $token;
            if( isset($order_version) ){
                $this->_version = $order_version;
            }

            // $this->_products		=	$products
            $this->set_shipping($ship);

            $this->_total = $total;
            $this->_total_before_discount = $total;

            /**
             * coupon
             */
            $this->_coupon_code = $coupon_code;
            $this->_discount_rate = $discount_rate;
            $this->_discount_method = $discount_method;

            // set order post parent
            if (isset($order['post_parent'])) {
                $this->_post_parent = $post_parent;
            }
            $this->_post_content = 'product name';
            $this->update_order();

            $this->_setup_checkout = true;
            return $order;
        }
    }
    function convert_currency($total, $code){
        return apply_filters('ae_convert_currency',$total, $code);
    }

    public function get_order_data() {
        return array(
            'payer' => $this->_payer,
            'created_date'          => $this->_created_date,
            'status'                => $this->_stat,
            'payment'               => $this->_payment,
            'products'              => $this->_products,
            'currency'              => $this->_currency,
            'payment_code'          => $this->_payment_code,
            'total'                 => $this->get_total(),
            'total_before_discount' => $this->_total_before_discount,
            'discount_rate'         => $this->_discount_rate,
            'discount_method'       => $this->_discount_method,
            'paid_date'             => $this->_paid_date,
            'shipping'              => $this->_shipping,
           // 'version'               => $this->_version,
        );
    }

    //convert order to an associate array
    public function generate_data_to_pay() {

        $currency_code = $this->_currency;
        $order = array(

            'payment_code' => $this->_payment_code,
            'ID' => $this->_ID,
            'payer' => $this->_payer,
            'currencyCodeType' => $this->_currency,
            'products' => $this->_products,

            'total' => $this->convert_currency($this->_total, $currency_code),
            'total_before_discount' => $this->_total_before_discount,

            'ship' => $this->_shipping,
            'payment' => $this->_payment,
            'payer_id' => $this->_payer_id,
             // use in paypal
            'coupon_code' => $this->_coupon_code,
            'discount_rate' => $this->_discount_rate,
            'discount_method' => $this->_discount_method,
            // add for multi curren cy
            'currency_code' => $currency_code,

        );

        return $order;
    }

    /*
     * update order infomation to database
    */
    function update_order() {

        if ($this->_total_before_discount == '' && $this->_total == '') return false;

        $postarr = array();
        $postarr['ID'] = $this->_ID;
        $postarr['post_author'] = $this->_payer;
        $postarr['post_title'] = '#'.$this->_ID;

        $postarr['post_date'] = $this->_created_date;
        $postarr['post_status'] = $this->_stat;
        $postarr['post_type'] = self::POST_TYPE;
        if ($this->_post_parent) $postarr['post_parent'] = $this->_post_parent;

        $postarr = apply_filters('et_save_order_data', $postarr);

        // Insert post if ID null
        if(empty($postarr['ID'])) {
            $post_content   = '<strong>Order type:</strong>:  Purchase package to post project.';
            if($this->_product_type == 'bid_plan'){
                $post_content   = '<strong>Order type:</strong>: Purchase bid plan to bid on site.';
            } else  if(in_array($this->_product_type,array('fre_credit_fix','fre_credit_plan') ) ){
                $post_content = '<strong>Order type:</strong>: Depsit Credit.';
            }
            $postarr['post_content'] = $post_content.'<br /><strong>Order Data: </strong><br />';
            $postarr['post_content'] .= serialize($postarr);
            $this->_ID = wp_insert_post($postarr);

            if($this->_version && !empty($this->_version)){
                update_post_meta($this->_ID, 'et_order_version', $this->_version);
            }
        } else {
            $this->_ID = wp_update_post($postarr);
        }

        update_post_meta($this->_ID, 'et_order_product_id', $this->_product_id);
        update_post_meta($this->_ID, 'et_order_product_type', $this->_product_type);
        update_post_meta($this->_ID, 'et_order_products', $this->_products);
        update_post_meta($this->_ID, 'et_order_shipping', $this->_shipping);
        update_post_meta($this->_ID, 'et_order_currency', $this->_currency);
        update_post_meta($this->_ID, 'et_order_payment_code', $this->_payment_code);
        update_post_meta($this->_ID, 'et_order_total', $this->_total);
        update_post_meta($this->_ID, 'et_order_total_before_discount', $this->_total_before_discount);
        update_post_meta($this->_ID, 'et_order_paid_date', $this->_paid_date);
        update_post_meta($this->_ID, 'et_order_payer_id', $this->_payer_id);
        update_post_meta($this->_ID, 'et_order_gateway', $this->_payment);

        /**
         * coupon
         */
        update_post_meta($this->_ID, 'et_order_coupon_code', $this->_coupon_code);
        update_post_meta($this->_ID, 'et_order_discount_rate', $this->_discount_rate);
        update_post_meta($this->_ID, 'et_order_discount_method', $this->_discount_method);

        // et_refresh_revenue();

        do_action('et_save_order');

        return $this->_ID;
    }

    /*
     * remove an order from database
    */
    function delete_order() {
        // delete order post
        wp_delete_post($this->_ID, true);

        // destroy object
        $this->_ID = '';
        $this->_total = '';
    }

    protected function calculate_discount($total) {
        if ($this->_coupon_code && $this->_discount_rate) {
            if ($this->_discount_method == 'percent') {
                $total-= ($this->_discount_rate * $total) / 100;
            } else {
                $total-= $this->_discount_rate;
            }
        }
        if ($total < 0) $total = 0;
        return number_format($total, 2, '.', '');
    }

    /**
     * set up product for order
     * @param array $product :
     * 	-	$key : product ID
     * 	-	$value : product info
     */
    public function set_product($product = array()) {

        $arr = array();
        $total = 0;
        foreach ($product as $key => $value) {

            // get all payment plan : product
            // each product data


            //$this->convert_currency($total, $currency_code);
            $p = array(
                'ID' => $key,
                'NAME' => $value['title'],
                'AMT' => $value['price'],
                'QTY' => $value['qty'],
                'L_DESC' => $value['description']
            );
            $arr[$key] = $p;
            $total+= $value * $p['AMT'];
        }

        $this->_total_before_discount = $total;
        $this->_total = $this->calculate_discount($total);

        $this->_products = $arr;
        $this->update_order();
        return number_format($total, 2, '.', '');
    }

    /**
     * set up product for order
     * @param array $product :
     * 	-	$key : product ID
     * 	-	$value : product info
     */
    public function add_product($product, $number = 1) {

        $price = $product['price'];
        //$this->convert_currency($total, $currency_code);


        $this->_products[$product['ID']] = array(
            'ID' => $product['ID'],
            'NAME' => $product['title'],
            'AMT' => $product['price'],
            'QTY' => $number,
            'L_DESC' => $product['description'],
            'SKU' => $product['sku']
        );
        $this->_total_before_discount += number_format($product['price'] * $number, 2, '.', '');
        $this->_total = number_format($this->calculate_discount($this->_total_before_discount) , 2, '.', '');

        $this->_product_id = $product['ID'];
        $this->update_order();
    }

    public function get_products() {
        return $this->_products;
    }

    public function get_total() {
        return $this->_total;
    }

    /**
     * set up shipping infomation for order
     * @param array $ship
     * 	- name
     * 	- address
     *  - city
     *  - state
     *  - amount
     *  - country
     */
    public function set_shipping($ship = array()) {
        $this->_shipping = $ship;
    }

    /**
     * set order payment code
     * @param $string $code : token
     */
    public function set_payment_code($code) {
        $this->_payment_code = $code;
        update_post_meta($this->_ID, 'et_order_payment_code', $this->_payment_code);
    }
    public function get_payment_code() {
        return $this->_payment_code;
    }

    public function set_status($status) {
        $this->_stat = $status;
    }

    public function set_payer_id($id) {
        $this->_payer_id = $id;
    }

    public function get_payer_id() {
        return $this->_payer_id;
    }

    // function accept visitor
    function accept(ET_PaymentVisitor $visitor) {
        if ($this->_setup_checkout) return $visitor->setup_checkout($this);
        else return $visitor->do_checkout($this);
    }
}
class ET_NOPAYOrder extends ET_Order
{
    public function __construct() {
    }
}


function et_add_order() {

    // action, author, order, theme - can not register this post_type
    register_post_type('et_order', $args = array( // change post_type order to et_order. v1.8.19
        'labels' => array(
            'name' => __('Order', ET_DOMAIN) ,
            'singular_name' => __('Order', ET_DOMAIN)
        ) ,
        'hierarchical' => false,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' =>  false,
        'show_in_admin_bar' => false,
        'menu_position' => 25,
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'supports'           => array( 'title', 'editor', 'author','custom-fields' ),
    ));
}
add_action('init', 'et_add_order', 15);


function upgrade_new_post_type_order(){
    if( version_compare(ET_VERSION,'1.8.19', '>=') ){
        global $wpdb;
        $sql = "UPDATE  $wpdb->posts SET  `post_type` =  'et_order'   WHERE  `post_type` = 'order' ";
        $check = $wpdb->query($sql);
    }
}
add_action('admin_init','upgrade_new_post_type_order', 999);

/**
 * Post_type order is now allwo in wordpress. change it to et_order.
 * @since v1.8.19
 **/
function et_order_change_post_status(){
    register_post_status( 'failed', array(
        'label'                     => _x( 'Fail', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Fail <span class="count">(%s)</span>', 'Fail <span class="count">(%s)</span>' ),
    ) );
    register_post_status( 'cancelled', array(
        'label'                     => _x( 'Cancelled', 'post' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>' ),
    ) );
}
add_action( 'init', 'et_order_change_post_status' );