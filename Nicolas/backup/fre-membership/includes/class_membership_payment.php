<?php

Class Fre_Membership_Payment{
	public $enable_stripe;
	public $active_stripe;
	public $enable_paypal;
	public $enable_credit;
	public $test_mode;
	public $flag;
	public $thankyou_url;

	function __construct(){
		$this->enable_paypal 	= (boolean) ae_get_option('enable_mebership_paypal', false);
		$this->enable_stripe 	= (boolean) ae_get_option('enable_mebership_stripe', false);
		$this->enable_credit 	= (boolean) ae_get_option('user_credit_system', false) ;
		$this->stripe_api 		= fre_get_stripe_membership_api();
		$this->active_stripe  	= 0;
		if($this->enable_stripe){
			$api = fre_get_stripe_membership_api();
			if( ! empty($api->publishable_key) &&  !empty($api->secret_key) && !empty($api->signing_secret) ){
				$this->active_stripe = 1;
			}

		}

		$this->page_checkout_id = (int) ae_get_option('fre_membership_checkout');
		$this->thankyou_url 	= get_permalink(ae_get_option('membership_successful_return'));

		$this->flag 			= 0;
		$this->test_mode    	= (boolean) ae_get_option('membership_mode', true);
		$this->test_mode  		= ($this->test_mode) ? 1 : 0;
		add_action( 'membership_payment_gateway_checkout',array($this,'add_paypal_checkout_bbutton'), 1);
		add_action( 'membership_payment_gateway_checkout',array($this,'add_stripe_checkout_button'), 5);
		add_action( 'membership_payment_gateway_checkout',array($this,'add_credit_checkout_button'), 10);
		add_action( 'membership_payment_gateway_checkout',array($this,'no_membership_payment'), 10 , 2);
		add_action( 'wp_head', array($this, 'enque_stripe_js_in_header') );
		add_action( 'wp_footer', array($this, 'add_stripe_js_in_footer') );
		add_action( 'wp_footer', array($this,'add_modal_on_footer'));
		add_action('wp_ajax_subscriberViaCredit', array($this,'subscriberViaCredit'));
		add_action('wp_ajax_cancelMemberShip', array($this,'disable_auto_renew'));

	}

	function add_paypal_checkout_bbutton($pack){
		if($this->enable_paypal ){
			$this->flag = 1;
			$paypal_api = fre_get_paypal_membership_api();
			if( !empty($paypal_api->paypal_business) && $pack->et_price > 0 ){ ?>
				<li class="panel paypal-payment-gateway">
					<span class="title-plan" data-type="paypal"><?php _e('PayPal','enginethemes');?><span><?php _e('Send your payment via PayPal.','enginethemes');?></span></span>
					<?php a_paypal_subcriber_button($pack);?>
				</li><?php
			}
		}
	}
	function add_stripe_checkout_button($pack){
		if( $this->active_stripe  && $pack->et_price > 0  ){ $this->flag = true;?>
		<li class="panel stripe-payment-gateway">
			<span class="title-plan" data-type="stripe"><?php _e('Stripe','enginethemes');?><span><?php _e('Send your payment via Stripe.','enginethemes');?></span></span>
			<button class="btn collapsed btn-submit-price-plan select-payment" id="<?php echo $pack->stripe_pricing_id;?>"> <?php _e('SELECT','enginethemes');?>	</button>
		</li>
	<?php }
	}
	function enque_stripe_js_in_header(){
        if( $this->check_enqueue_stripe() ){ ?>

            <script src="https://js.stripe.com/v3"></script>
            <script src="https://checkout.stripe.com/checkout.js"></script>
            <?php
        }
    }
    function check_enqueue_stripe(){
    	if(! is_user_logged_in() || ! $this->active_stripe  )
    		return false;

    	if(  $this->is_membership_checkout_page() && !empty( $this->stripe_api->publishable_key ) ){
    		return true;
    	}

		return false;
    }

    function add_stripe_js_in_footer(){

		if( ! $this->check_enqueue_stripe() ){
			return false;
		}
		global $user_ID;
		$sku 		= $_GET['sku'];
		$pack_type 	= get_pack_type_of_user($user_ID);
		$pack 		= membership_get_pack($sku, $pack_type);
		$subscription_type = 'subscription'; //or payment for one time( no recurring)
		if( !isset($pack->stripe_pricing_id) || ! $pack->stripe_pricing_id ){ // price_1IyGfj2X7QLsSrwxUkPrPCOz
			return;
		}

		$thankyou_id 	= ae_get_option('membership_successful_return');
		$thank_page 	= get_permalink($thankyou_id);
		$var_name 		= 'checkoutButton'.$pack->stripe_pricing_id;
		$current_user 	= wp_get_current_user();

		$cancel_id 		= ae_get_option('membership_cancel_return');
		$cancel_url 	= get_permalink($cancel_id);
		if( empty($cancel) || empty($cancel_id) ){
			$cancel_url = home_url();
		}
		?>
		<script type="text/javascript">
			var stripe 	= Stripe('<?php echo $this->stripe_api->publishable_key;?>');
		</script>

		<script type="text/javascript">

			var handleResult = function (result) {
				console.log('handleResult');
		        if (result.error) {
		          	var displayError			= document.getElementById("error-message");
		          	displayError.textContent 	= result.error.message;
		          	alert(result.error.message);
		        } else {
		        	console.log(result);
		        }
		     };
			var <?php echo $var_name;?> 	= document.querySelector('#<?php echo $pack->stripe_pricing_id;?>');
			<?php echo $var_name;?>.addEventListener('click', function (event) {

		     	stripe.redirectToCheckout({
		            lineItems: [{ price: '<?php echo $pack->stripe_pricing_id;?>', quantity: 1 }],
		           	successUrl: '<?php echo $thank_page;?>',
		            cancelUrl: '<?php echo $cancel_url;?>',
		            clientReferenceId:'<?php echo $user_ID.'||'.$sku;?>', //only catch by using this hook checkout.session.completed
		            customerEmail:'<?php echo $current_user->user_email;?>',
		            //sessionId: 'teststaticss@gmail.com', server allow - not client
		            // ID: 'testsaticID_id', // NO Accept
		            // email: 'teststaticeee@gmail.com', // no accept
		            mode: '<?php echo $subscription_type;?>',
		            // description:'Test Static Description ', NO AAccept
		        }).then(handleResult);
		     });

		</script> <?php
	}

    function is_membership_checkout_page(){
    	if( is_page() ){
			global $post;
			if( has_shortcode($post->post_content,'fre_membership_checkout'))
				return true;
		}
		return false;
    }


	function add_credit_checkout_button($pack){

		if( $this->enable_credit ){
			$this->flag = 1;
			global $user_ID;
			$user_wallet 		= FRE_Credit_Users()->getUserWallet($user_ID); $has_pmgw = true;
			$desposit_page 		= ae_get_option('fre_credit_deposit_page_slug', false);
			$desposit_link 		= get_permalink($desposit_page);
			$disable_deposit 	= ae_get_option('prevent_deposit_page', false);
			$price = get_subscribe_price($pack->et_price);
			if( $user_wallet->balance < $pack->et_price ) {   ?>
				<li class="panel credit-payment-gateway">
					<span class="title-plan" data-type="credit"><?php _e('Credit','enginethemes');?><span><?php _e('You don\'t have enough credit to check out. Please deposit first.','enginethemes');?></span></span>
					<a class="btn collapsed " href="<?php echo $desposit_link;?>"> <?php _e('DEPOSIT','enginethemes');?>	</a>
				</li>

			<?php } else{ ?>
				<li class="panel credit-payment-gateway">
					<span class="title-plan" data-type="credit"><?php _e('Credit','enginethemes');?><span><?php printf(__('System auto deduct %s in your credit balance.','enginethemes'), '<strong>'.fre_price_format($price).'</strong>'  );?></span></span>
					<button class="btn collapsed  btnSubscriberViaCredit"> <?php _e('SELECT','enginethemes');?>	</button>
				</li><?php
			}
		}

	}
	function no_membership_payment($pack, $subscription){
		$flag = apply_filters('no_membership_payment', $this->flag);
		echo '<li class = "subscription-change-explain"><h5>';
		if(! $flag ){
			_e('There is no payment gateway available for subscribe at current time.','enginethemes');

		} else if($subscription) {
			_e('Important: All remaining posts in your current plan will be lost when you change your subscription.','enginetheme');
		}
		echo '</5></li>';

	}

	function subscriberViaCredit(){

		if( ! is_user_logged_in ()  || ! $this->enable_credit  ){
			wp_send_json( array('success' => false, 'msg' => __('Fail. There is something wrong.','enginethemes') ) );
		}

		global $user_ID;
		$user_id = $user_ID;

		if( ae_get_option('fre_credit_secure_code', true) ){
			$resp = array(
                'success'=> false, 'msg'=> __('Please enter a valid secure code!', 'enginethemes')
            );
            $result = FRE_Credit_Users::getInstance()->checkSecureCode($user_ID, $_POST['secureCode']);
            if( !$result ){
                wp_send_json($resp);
            }
        }

		$sku 			= $_POST['sku'];
		$pack_type 		= get_pack_type_of_user($user_ID);
		$pack 			= membership_get_pack($sku, $pack_type);
		$msg 			= __('Your subscription has been updated','enginethemes');
		if(!$pack){
			wp_send_json(array('success' => false, 'msg' => __('Fail.This plan is not availble.','enginethemes') ) );
		}

		if( $pack->et_price == 0 ){
			$sub_free = is_availbale_free_subscribed($user_id);
			if($sub_free){
				// disable re-subscribe a free plan.
				//wp_send_json(array('success' => false, 'msg' => __('You can not re-subscribe free plan at current time.','enginethemes') ) );
				fre_update_sub_id_of_member($sub_free->id, $user_id);
				wp_send_json(array('success' => true, 'msg' => __('Your subscription has been updated.','enginethemes'), 'redirect_url' => $this->thankyou_url ) );
			} else {
				save_free_subscription($user_ID, $pack, $pack_type);
				wp_send_json(array('success' => true, 'msg' => __('Your subscription has been updated.','enginethemes'), 'redirect_url' => $this->thankyou_url ) );
			}
			wp_die('stop.');
		}

		$subscribed 	= is_subscriber_available();
		if($subscribed && $subscribed->plan_sku == $pack->sku){
			$msg = __('Your subscription are still available. Your can not re-subcribe this plan.','enginethemes');
			wp_send_json(array('success' => false, 'msg' =>$msg ) );
		}

		$user_wallet 	= FRE_Credit_Users()->getUserWallet($user_ID);

		if( $user_wallet->balance <  (float) $pack->et_price ){
			wp_send_json(array('success' => false, 'msg' => __('Your balance is not enough. Please deposit first then subscribe again.','enginethemes') )  );
		}
		$user_wallet->balance =$new_balance =  $user_wallet->balance - $pack->et_price;

		$price = get_subscribe_price($pack->et_price);

		$code = 'type=subscriber_credit'; // add_credit, minus_credit
        $code .= '&from=' . $user_wallet->balance;
        $code .= '&to=' . $new_balance;
        $code .= '&amount=' . $price;
        $code .= '&message=subscriber_plan_'.$pack->sku;

        // saveHistory
        FRE_Credit_Users()->setUserWallet($user_ID, $user_wallet);
        // $notification = array(
        //     'post_type'    => 'notify',
        //     'post_content' => $code,
        //     'post_excerpt' => $code,
        //     'post_author'  => $user_ID,
        //    // 'post_title'   => __( "Admin manually top up credits", 'enginethemes' ),
        //     'post_status'  => 'publish',
        // );
        // Fre_Notification::getInstance()->insert($notification);

		// send email here;
        save_credit_history_subscriber($pack);

        $string = "+1 month";
		if( (int) $pack->et_subscription_time > 1 )
			$string = "+{$pack->et_subscription_time} months";

		$expiry_time 	= strtotime($string);

		$args = array(
			'user_id' 			=> $user_ID,
			'plan_sku' 			=> $sku,
			'pack_type' 		=> $pack_type,
			'price' 			=> $price,
			'currency' 			=> fre_get_currency_code(),
			'api_subscr_id' 	=> 'fre_credit',
			'remain_posts' 		=> $pack->et_number_posts,
			'expiry_time' 		=> $expiry_time,
			'payment_gw' 		=> 'fre_credit',
			'payment_status' 	=> 'paid', // paid
			'test_mode' 		=> $this->test_mode,
		);
		$res 		= array('success' => true, 'msg' => __('Thankyou. Your subscription has been updated.','enginethemes'), 'redirect_url' => $this->thankyou_url);
		$args 		= (object)$args;
		$user 	  	= get_userdata($user_id);
		$result 	=  fre_mebership_save_subscrition($args);
		$subscr_id = (int) $result;
		if($subscr_id > 0){
			$m_args = array(
				'user_id' 		=> $user_id,
				'user_email'	=> $user->user_email,
				'user_login' 	=> $user->user_login,
				'subscr_id' 		=> $subscr_id,
			);
			$m_args  =(object)$m_args;
			fre_save_membership( $m_args );

		} else {
			$res = array('success' => false, 'msg' => __('Can not update your subscription.','enginetheme') );
		}
		wp_send_json( $res);
	}


	function disable_auto_renew(){
		$resp = array(
			'success' => true,
			'msg' => __('Your have disabled auto renewal your subscription.','enginethemes')
		);
		global $user_ID;
		$subscriber = get_mebership_of_member($user_ID);
		$payment_gw  = $subscriber->payment_gw;

		if($payment_gw == 'stripe'){

			include_once( FRE_MEMBERSHIP_PATH.'/stripe-php/init.php' );
			$api 			= fre_get_stripe_membership_api();
			$stripe_sub_id 	= $subscriber->api_subscr_id;
			\Stripe\Stripe::setApiKey($api->secret_key);
			$subscription = \Stripe\Subscription::retrieve($stripe_sub_id);
			$check = $subscription->cancel();
			if($check->status == 'canceled'){
				update_auto_renew_status('disable', $subscription->subscr_id);
				do_action('subscriber_disable_auto_renew', $subscriber);
			}


		} else if( $payment_gw == 'paypal' ){

			$paypal_sub_id 	= $subscriber->api_subscr_id;
			$api 			= fre_get_paypal_membership_api();
			$basic 			= base64_encode($api->client_id.":".$api->secret_key);
			$testmode = 	(boolean) ae_get_option('membership_mode', true);
			$paypal_api = "https://api.paypal.com/v1/billing/subscriptions/";
			if($this->test_mode){
				$paypal_api = "https://api.sandbox.paypal.com/v1/billing/subscriptions/";
			}
			$authorization 	= array(
				'Authorization' => "Basic ".$basic,
				'Content-type' => 'application/json',
				'Accept' => 'application/json',
			);
			$header = array(
			        'timeout'     => 120,
			        'httpversion' => '1.1',
			        'headers' => $authorization,
			);
			$sub_url 	= $paypal_api.$paypal_sub_id;
			$json = wp_remote_get( $sub_url,$header);
			$body = wp_remote_retrieve_body($json);
			$data = json_decode( $body );

			if( isset($data->status) && $data->status == 'ACTIVE'){
				$endpoint 	= $paypal_api."{$paypal_sub_id}/cancel";
				$cancel 	= wp_remote_post($endpoint, $header);
				update_auto_renew_status('disable', $subscriber->subscr_id);
				do_action('subscriber_disable_auto_renew', $subscriber);
			}

		} else{
			update_auto_renew_status('disable', $subscriber->subscr_id);
			do_action('subscriber_disable_auto_renew', $subscriber);
		}

		wp_send_json($resp);
	}

	function add_modal_on_footer(){

		if( is_page_template('page-profile.php') ){
			fre_memhership_template_part('templates/modal','cancel-subscription');
		}

		if($this->is_membership_checkout_page()){
			fre_memhership_template_part('templates/modal','subscriber-via-credit');
		}
	}
}
$_GLOBALS['membership_payment'] = new Fre_Membership_Payment();