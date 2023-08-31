<?php
/*
Plugin Name:MJE Ecpay V1.3
Plugin URI: http://www.enginethemes.com/
Description:
Version: 1.3
Author: danng
Author URI: http://danng.com/
Text Domain: memberpress
Copyright: 2004-2017, Caseproof, LLC
*/
define( 'MJE_PAYPALL_URL', plugin_dir_url( __FILE__ ) );
define('MJE_ECPAY_LOG', false );

function ecpay_log($input, $file_store = ''){

	if( ! MJE_ECPAY_LOG ){
		return ;
	}

    if(empty($file_store)){
        $file_store = WP_CONTENT_DIR.'/ecpay_log.css';
    }

    if( is_array( $input ) || is_object( $input ) ){
        error_log( print_r($input, TRUE), 3, $file_store );
    } else {
        error_log( $input . "\n" , 3, $file_store);
    }
}

add_action( 'after_setup_theme', 'allpay_require_plugin_files' );
function allpay_require_plugin_files() {

	// require_once dirname( __FILE__ ) . '/inc/ECPayPaymentHelper.php';
	// require_once dirname( __FILE__ ) . '/inc/ECPay.Payment.Integration.php';
	require_once dirname( __FILE__ ) . '/inc/functions.php';
	require_once dirname( __FILE__ ) . '/inc/helper.php';
   	require_once dirname( __FILE__ ) . '/inc/allpay_visitor.php';
}


add_filter( 'mje_payment_gateway_setting_sections', 'add_allpay_setting');

function add_allpay_setting($sections){
	$fields = allpay_setting_fields();
	$sections['general']['groups'][1]['fields'][] = $fields;
	return $sections;
}


function allpay_setting_fields(){

	return array(
	    'id' => 'allpay',
	    'name' => 'allpay',
	    'type' => 'combine',
	    'title' => __('Ecpay', 'enginethemes'),
	    'desc' => __(""),
	    'class' => 'field-payment',
	    'children' => array(
	        array(
	            'id' => 'enabled',
	            'type' => 'switch',
	            'title' => __("Enable Ecpay API", 'enginethemes') ,
	            'desc' => __('Enabling this will allow your users to pay via Ecpay.', 'enginethemes'),
	            'name' => 'enable',
	            'class' => 'option-item bg-grey-input '
	        ) ,

	        array(
	            'id' => 'pay_use',
	            'type' => 'text',
	            'title' => __("pay_use", 'enginethemes') ,
	            'name' => 'pay_use',
	            'class' => 'option-item bg-grey-input ',
	            'desc' => __('Enter your pay_use type : 1 - Allpay, 2- Ecpay. Default is 2 - Ecpay.', 'enginethemes')
	        ) ,
	        array(
	            'id' => 'MerchantID',
	            'type' => 'text',
	            'title' => __("MerchantID", 'enginethemes') ,
	            'name' => 'MerchantID',
	            'class' => 'option-item bg-grey-input ',
	            'desc' => __('Enter your MerchantID  <code>2000132</code>', 'enginethemes')
	        ),
	        array(
	            'id' => 'hash_key',
	            'type' => 'text',
	            'title' => __("Has key(hash_key)", 'enginethemes') ,
	            'name' => 'hash_key',
	            'class' => 'option-item bg-grey-input ',
	            'desc' => __('Enter your hash_key  <code>5294y06JbISpM5x9</code>', 'enginethemes')
	        ),
	        array(
	            'id' => 'hash_iv',
	            'type' => 'text',
	            'title' => __("hash_iv", 'enginethemes') ,
	            'name' => 'hash_iv',
	            'class' => 'option-item bg-grey-input ',
	            'desc' => __('Enter your hash_iv  <code>v77hoKGq4kWxNNIS</code>', 'enginethemes')
	        ),
	         array(
	            'id' => 'IgnorePayment',
	            'type' => 'text',
	            'title' => __("IgnorePayment", 'enginethemes') ,
	            'name' => 'IgnorePayment',
	            'class' => 'option-item bg-grey-input ',
	            'desc' => __('Default disable all  <code>WebATM#ATM#CVS#BARCODE#Alipay</code>', 'enginethemes')
	        ),
	    )
	);
}




add_filter( 'ae_support_gateway', 'ae_molpay_add' );
function ae_molpay_add($gateways){
	$gateways['molpay'] = 'All pay';
	return $gateways;
}
add_action('mje_after_payment_list', 'ae_allpay_render_button');
function ae_allpay_render_button() {
	$allpay = ae_get_option('allpay');
	$enable = 0;
	if( isset( $allpay['enable'] ) ) {
		$enable = (int) $allpay['enable'];
	}
	$ap_setting = new Allpay_setting();
	if ( $enable == 1 ) { ?>
		<li>
			<div class="outer-payment-items hvr-underline-from-left">
				<a href="#" class="btn btn-submit-price-plan select-allpay-payment" data-type="allpay" id="allpay" method ="ideal">
					<?php echo $ap_setting->get_logo();?>
					<p>線上刷卡</p>
				</a>
			</div>
		</li> <?php
	}
}
function build_payment_visitor( $class, $payment_type, $order ) {
    if( $payment_type == 'ALLPAY' ) {
        $class = new ET_AllpaylVisitor( $order );
    }

    return $class;
}
add_filter( 'et_build_payment_visitor', 'build_payment_visitor', 10, 3 );

add_action( 'wp_footer','add_payall_scrip',100 );
function add_payall_scrip(){ ?>
	<script type="text/javascript">

		(function($, Views, Models) {
			  Views.allPay = Backbone.View.extend({
			    el: '.list-payment-gateway',
			    initialize: function() {
			    	var view = this;
			      	this.checkoutData = '';
			      	//this.setupCheckoutButton();

				    AE.pubsub.on('mje:after:setup:checkout', this.afterSetupCheckout, this);
				    $("a.select-allpay-payment").click(function(e){
						view.checkoutData.save('', '', {
					        beforeSend: function() {

					        },
					        success: function(status, response, xhr) {

					        	var api = response.data.api_ap;
					            var form_submit = wp.template("form_submit");
					            var formFullInfo = form_submit(api);
					            console.log(formFullInfo);
								$("#checkout-step2").html(formFullInfo);
								$("#allpay_payment_form").submit();

					        }
					    });
				   });
				},
			    afterSetupCheckout: function(data) {
			      this.checkoutData = data;
			      this.productData = this.checkoutData.get('p_data');
			      this.productData.payment_type = 'allpay';
			      this.checkoutData.set('p_payment', 'allpay');
			    },

			  });

			  $(document).ready(function() {
			    new Views.allPay();
			  });
			})(jQuery, window.AE.Views, window.AE.Models)



	</script>
	<script type="text/html" id="tmpl-form_submit">
		<?php
		$allpay = new Allpay_setting();
		?>
		<form method="POST" id="allpay_payment_form" action="<?php echo $allpay->get_checkout_url();?> ">
		<input type="hidden" name="MerchantID" value="{{{data.MerchantID}}}">
		<input type="hidden" name="MerchantTradeNo" value="{{{data.MerchantTradeNo}}}">
		<input type="hidden" name="MerchantTradeDate" value="{{{data.MerchantTradeDate}}}">
		<input type="hidden" name="PaymentType" value="aio">
		<input type="hidden" name="TotalAmount" value="{{{data.TotalAmount}}}">
		<input type="hidden" name="TradeDesc" value="{{{data.TradeDesc}}}">
		<input type="hidden" name="ItemName" value="{{{data.ItemName}}}">
		<input type="hidden" name="ChoosePayment" value="{{{data.ChoosePayment}}}">
		<input type="hidden" name="ReturnURL" value="{{{data.ReturnURL}}}">
		<input type="hidden" name="PaymentInfoURL" value="{{{data.PaymentInfoURL}}}">
		<input type="hidden" name="ClientBackURL" value="{{{data.ClientBackURL}}}">
		<input type="hidden" name="OrderResultURL" value="{{{data.OrderResultURL}}}">
		<input type="hidden" name="IgnorePayment" value="<?php echo $allpay->IgnorePayment;?>">
		<# if(data.ChoosePayment == 'Credit') {#>
			<input type="hidden" name="Language" value="ENG">
		<#}#>
		<# if( data.ExpireDate ) {#>
			<input type="hidden" name="ExpireDate" value="{{{data.ExpireDate}}}">
		<#}#>
		<input type="hidden" name="CheckMacValue" value="{{{data.CheckMacValue}}}"></form>
	</script>

	<?php
}
add_filter('mje_setup_payment','allpay_generator_api', 10, 3 );
function allpay_generator_api( $response, $payment_type, $order){
	return $response;
}


add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_js' );
function themeslug_enqueue_js(){
	wp_enqueue_script( 'wp-util' );
}
add_filter('mje_render_payment_name','ecpay_add_label' );
function ecpay_add_label($args){
	$args['allpay'] = '<strong>ECPAY</strong>';
	return $args;
}
function debug_order_infor(){
	$order_id = 2345;


    global $wp_query, $ae_post_factory, $user_ID;
	$mjob_order_obj = $ae_post_factory->get( 'mjob_order' );
	$p_order = get_post($order_id);
	$current = $mjob_order_obj->convert($p_order);


    echo '<pre>';

    // var_dump($current);
    // echo $current->amount;
    $mjob_order = 196;


    $order = new MJE_Order($mjob_order);
    //var_dump($order);
    //var_dump($order);
    $order_data = $order->get_order_data();

    $ad = get_post($order_data['product_id']);
    var_dump($order_data);
    $status = $order_data['status'];
    var_dump($status);
    $product = $order->get_products();

    $mjob_order_id = $ad->ID;
    var_dump($mjob_order_id);
    // $products = $order['products'];
    // var_dump($products);
                //ecPayLog('Order Object: ');

                //$cart_amount = $order->get_total();
    //var_dump($product);
    $pay = array_shift($product);
    var_dump($pay);
    $total = round($pay['AMT']);
    var_dump($total);


    echo '</pre>';
}
//add_action('wp_footer','debug_order_infor');
?>