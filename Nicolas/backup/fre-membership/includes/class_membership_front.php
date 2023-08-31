<?php
Class Fre_MemberShip_Front{

	public $membership_url;
	public $post_fre;
	public $bid_free;
	public $checkout_url;
	function __construct(){

		$this->page_checkout_id = (int) ae_get_option('fre_membership_checkout');
		$this->checkout_url 	= get_permalink($this->page_checkout_id);
		$this->page_member_id 	= (int) ae_get_option('fre_membership_plans');
		$this->membership_url 	= get_permalink($this->page_member_id);
		$this->post_free  		= (boolean) ae_get_option('disable_plan', false);
		$this->pay_to_bid  		= (boolean) ae_get_option( 'pay_to_bid', false );

		add_action('wp_enqueue_scripts', array($this,'enqueue_script'));

		add_action('after_my_account_block', array($this, 'show_subscription_info') );
		add_filter('body_class',array($this, 'check_membership_shortcode'));
		add_action('template_redirect', array($this,'custom_redirects' ), 99 );
		add_filter('is_post_project_free', array($this, 'is_post_free'), 999 );

		if( ! $this->post_free){
			// ae-project-sync method: create
			add_action('ae_insert_project', array( $this,'reduce_post_project_left') );
			add_filter('ae_pre_insert_project', array($this, 'check_pre_post_project'), 9999 ); // this can use to apply project_type.
			add_action('fre_above_post_project', array($this,'overview_employer_post_infor')); // top my-project page of employer role.
		}

		if( $this->pay_to_bid ){
			// không dùng ae_insert_bid vì để kiểm tra xem trong theme có trừ credit không.
			add_action('fre_update_bid_left', array( $this,'reduce_bid_left') );
			add_filter('modal_purchase_bid',array($this,'replac_text_in_modal_purchase_bid'));
			add_action('overview_freelancer_bid_info', array($this,'overview_freelancer_bid_info') ); // in the top of my-project page for freelancer role
			add_filter('get_subscription_number_bid', array($this, 'get_number_subscription_bid'), 10 ,2);
			add_filter( 'url_purchase_bid', array($this, 'replase_upgrade_account_link') );
		}

		add_action('ae_insert_user',array($this, 'auto_assing_free_plan_to_new_user'), 10 , 2);
		add_shortcode('fre_membership_plans',array($this, 'fre_shortcode_mebership_plan') );
	}
	// vô hiệu hóa một số step trong chọn package và syn (update/insert project);
	function is_post_free($current_value){
		return true;
	}
	function fre_shortcode_mebership_plan($args) {
		global $user_ID, $ae_post_factory ;
		$pack_type 		= get_pack_type_of_user($user_ID);
		if(! is_user_logged_in() ){
			$pack_type = 'bid_plan';
		}
	    $ae_pack    	= $ae_post_factory->get($pack_type);
	    $packs      	= $ae_pack->fetch($pack_type);
		$subscribed 	= get_mebership_of_member(); // subscription plan of currnt user;
		$is_available 	= is_subscriber_available($subscribed);

		if( ($pack_type == 'bid_plan' && ! $this->pay_to_bid) || ( $pack_type == 'pack'  && $this->post_free) )
			return ''; //don't show list pricing if post_free or bid_free.

		ob_start(); ?>
		<div id="pricing-tables" class="row list-membership-plans">
			<div class="col-md-1 hidden-sm"></div>
			<div class="col-md-10">
			<?php

			if ( ! empty( $packs ) ) {
				foreach ($packs as $key => $package) {
					$this->a_membership_plan_html($package , $is_available);
				}
			}?>
			</div>
			<div class="col-md-1 hidden-sm"></div>
		</div>
		<?php
		return ob_get_clean();
	}
	function a_membership_plan_html($package, $is_available){

		$btn_url 	= add_query_arg( array( 'sku' => $package->sku, ), $this->checkout_url );
		$btn_text 	= $df_text = __('Select','enginethemes');
		$disabled_class = '';
		$plan_time_text = sprintf(__('%s<span>/Month</span>','enginethemes'),$package->et_price);

		if($package->et_price == 0){ $btn_text = __('FREE','enginethemes'); }

		if( (int) $package->et_subscription_time > 1){
			$plan_time_text = sprintf(__('%s<span>/%d Months</span>','enginethemes'),$package->et_price,$package->et_subscription_time );
			if( (int) $package->et_subscription_time == 12 ) {
					$plan_time_text = sprintf(__('%s<span>/1 Year</span>','enginethemes'),$package->et_price,$package->et_subscription_time );
			}
		}

		if( $is_available && ( ( $is_available->plan_sku == $package->sku) || ( $is_available->price == 0 && $package->et_price == 0 ) ) ) {
			$btn_text = __(' Current plan','enginethemes');
			$btn_url = 'javascript:;';
			$disabled_class = 'disabled';
		}
		$plan_html = '

		<div class="col-md-4 col-xs-12 col-cs-50 col-sm-4 membership-plan membership-plan-'.$package->sku.'">
		    <div class="single-table text-center">
		        <div class="plan-header">
		            <h3>'.$package->post_title.'</h3>
		            <p>'.$package->et_sub_title.'</p>
		            <h4 class="plan-price">'.fre_currency_sign(0).$plan_time_text.'</h4>
		        </div>
				<ul class="plan-features"><li>'.$package->post_content.'</li></ul>
		        <a href="'.$btn_url.'" class="redirect-checkout plan-submit hvr-bounce-to-right '.$disabled_class.'">'.$btn_text.'</a>
		    </div>
		</div>';
		$plan_html = apply_filters('membership_plan_html', $plan_html, $package);
		echo $plan_html;
	}

	function auto_assing_free_plan_to_new_user($user_id, $user_data){

		$auto_assign 	= $this->pay_to_bid ;
		$pack_type 		= 'bid_plan';
		$user_role 		= isset($user_data['role']) ? $user_data['role'] : 'freelancer';
		if( $user_role !== 'freelancer'  ){
			$pack_type = 'pack';
			$auto_assign = !$this->post_free; // thêm nếu không post_free;
		}
		if($auto_assign){
			$test_mode    	= (boolean) ae_get_option('membership_mode', true);
			$test_mode  	= ($test_mode) ? 1 : 0;

			$plan_free = get_plan_free($pack_type);

			if($plan_free){

				$sku 	= $plan_free->sku;
				$pack 	= membership_get_pack($sku, $plan_free->post_type);
				save_free_subscription( $user_id, $pack, $test_mode);
			}
		}
	}

	function replac_text_in_modal_purchase_bid($args){
		// in modal  can-not-bid.php .  reason: bid_left == 0

        $args['modal_purchase_bid_des'] = __('This project requires at least one avaialble bid to take bid action. You can get available bids by subscribe to a plan.', 'enginethemes' );
        $args['modal_purchase_bid_btn_text'] =  __( 'Subscribe', 'enginethemes' );

		return $args;

	}

	/**
	 * disable post project if number_post = 0.
	 **/
	function check_pre_post_project($args){
		global $user_ID;
		$project_type = $this->get_project_type($user_ID);

		if( $project_type ) $args['project_type'] = $project_type;

		if( current_user_can('manage_options') ){
			return $args;
		}

		$post_left 	= get_number_post_of_emp();
		if( $post_left < 1  ){
			return new WP_Error( 'zero_post', __( "Sorry. Please subscriber to get more more post.", "enginethemes" ) );
		}

		return $args;
	}

	/**
	 */
	function get_project_type($user_id){
		$project_type = false;
		$subscribed = get_mebership_of_member($user_id);
		$availble = is_subscriber_available($subscribed);
		if($availble){
			global $ae_post_factory;
			$pack    = $ae_post_factory->get( 'pack' );
			$package = $pack->get( $availble->plan_sku );

			if ( isset( $package->project_type ) && $package->project_type ) {
				$term_type = get_term_by( 'id', $package->project_type, 'project_type' );
				if ( $term_type && ! is_wp_error( $term_type ) ) {
					$project_type = $term_type->term_id;
				}
			}
		}
		return $project_type;
	}
	/**
	 * Show overview bid infor of freelancer user in the top page my-project
	*/
	function overview_freelancer_bid_info($total_bid){
		if( ! $this->pay_to_bid ) return ;

		$btn_text =  __( 'Select a plan', 'enginethemes' );
		$subscribed = get_mebership_of_member();

		if($subscribed){
			$btn_text =  __( 'Change Plan', 'enginethemes' );
			$availble = is_subscriber_available($subscribed);
			if($availble){
				$pack = membership_get_pack($subscribed->plan_sku, $subscribed->pack_type);

				if( $pack ){
					echo '<p>';printf( __( 'You are currently in the <b>%s</b> plan. You have <span><b>%d</b></span> available bid(s) left.', 'enginethemes' ), $pack->post_title, $total_bid ); echo '</p>';
				} else{
					echo '<p>';printf( __( 'You have <b>%d</b> available bid(s) left.', 'enginethemes' ), $total_bid );echo '</p>';
				}

			} else {
				echo '<p>';
					printf( __( 'You have <b>%d</b> available bid(s) left. Subscribe to a plan to get more bids.', 'enginethemes' ), $total_bid );
				echo '</p>';
			}

		} else {
			echo '<p class= "no-subscriber nooooosupcriberplan">';
			printf( __( 'You have <b>%d</b> available bid(s) left. Subscribe to a plan to get more bids.', 'enginethemes' ), $total_bid );
			echo '</p>';
		}


		?> <a class="fre-normal-btn-o" href="<?php echo $this->membership_url; ?>"><?php echo $btn_text;?></a><?php

	}

	/**
	 * Show overivew subscriber info in the top of post project form.
	 */
	function overview_employer_post_infor(){
		if($this->post_free) return;

		global $user_ID;
		$subscriber = get_mebership_of_member($user_ID);
		$btn_text  	= __('Select a plan','enginethemes');
		$page_id 	= ae_get_option('fre_membership_plans');
		$post_left 	= get_number_post_of_emp($subscriber);
		$post_left =  max($post_left,0);
		?>
        <div class="fre-post-project-box">
            <div class="step-change-package show_overview_subscribered">
            	<?php
					if($subscriber){

						$availble = is_subscriber_available($subscriber);

						if($availble){
							$btn_text 	= __('Change Plan','enginethemes');
							$pack = membership_get_pack($subscriber->plan_sku, $subscriber->pack_type);
							echo '<p>';printf( __( 'You are currently in the <b>%s</b> plan. You have <span><b>%d</b></span> available post(s) left.', 'enginethemes' ), $pack->post_title, $post_left );
							echo '</p>';
						} else {
							echo '<p>';
								printf( __( 'You have <b>%d</b> available post(s) left. Subscribe to a plan to get more posts.', 'enginethemes' ), $post_left );
							echo '</p>';
						}

					} else {
						echo '<p class= "no-subscriber nooooosupcriberplan">';
						printf( __( 'You have <b>%d</b> post(s) left. Subscribe to a plan to get more posts.', 'enginethemes' ), $post_left );
						echo '</p>';
					}
				?>
		        <a class="fre-btn-o  primary-color" href="<?php echo $this->membership_url;?>"><?php echo $btn_text;;?></a>

            </div>

        </div> <?php
	}

	function get_number_subscription_bid($result, $user_id){
		$subscription 	= get_mebership_of_member($user_id);
		$available 		= is_subscriber_available($subscription);
		if( $available && (int) $available->remain_posts > 0 ){
			return (int) $available->remain_posts;
		}
		return 0;
	}
	function reduce_bid_left($reduce_remain_post){
		if( !$used_credit_bid ){
			// bid free sẽ check và sử dụng trước ở trong theme. nếu k có free => dùng credit_number, sau đó mới dùng số bid của subscriber.
			// chi update remain_bid khi người dùng hết bid free, gết credit number mua từ pay_to_bid
			$subscription = is_subscriber_available();
			if($subscription){
				fre_reduce_post_left_of_current_user($subscription);
			}
		}
	}
	function reduce_post_project_left($post_id){

		if( fre_get_free_post_left() > 0){ // sử dụng post_free => trừ 1
			fre_increase_number_free_posted();
		} else {
			$subscription = is_subscriber_available();

			if($subscription){
				fre_reduce_post_left_of_current_user($subscription);
				//start update et_duration project
				$package       = membership_get_pack($subscription->plan_sku, $subscription->pack_type);
				if( $package ){
					$duration = (int) $package->et_duration;
					$expired_date = date( 'Y-m-d H:i:s', strtotime( "+{$duration} days" ) );
					update_post_meta( $post_id, 'et_expired_date', $expired_date );

				}
			} // end
		}
	}

	function replase_upgrade_account_link(){
		return $this->membership_url;
	}



    function is_membership_checkout_page(){
    	if( is_page() ){
			global $post;
			if( has_shortcode($post->post_content,'fre_membership_checkout'))
				return true;
		}
		return false;
    }

    /**
     *
    **/
	function custom_redirects() {
		if( is_page() ){
			if( current_user_can('manage_options') ) return true;
	    	global $post;
	    	$page_id = (int) ae_get_option('fre_membership_checkout');

	    	if( $post->ID == $this->page_checkout_id || $post->ID == $this->page_member_id ){
	    		if( ! is_user_logged_in() ){
	    			wp_redirect(et_get_page_link('login')); exit;
	    		}
	    	}
	    	if( is_page_template('page-submit-project.php') && ! $this->post_free ){
	    		$number_posts = get_number_post_of_emp();
				if(  $number_posts < 1 ){ wp_redirect($this->membership_url); exit; }
	    	}
	    	if( is_page_template('page-upgrade-account.php') && $this->pay_to_bid ){ // pay_to_bid for freelancer
	    		wp_redirect($this->membership_url); exit;
			}
	    }
	}

	function check_membership_shortcode($body_class){
		if( is_page() ){
			global $post;
			if(has_shortcode($post->post_content,'fre_membership_plans'))
				$body_class[] = 'has-membership-plans';
		}
		return $body_class;
	}

	function enqueue_script(){
		if( is_page_template('page-profile.php') || $this->is_membership_checkout_page() ){
			wp_enqueue_script( 'membership', MEMBERSHIP_URL. '/assets/membership.js', array(
			'jquery',
			'underscore',
			'backbone',
			'appengine'
			), FRE_MEMBERSHIP_VER , true );
		}
		wp_enqueue_style( 'style-membership', MEMBERSHIP_URL. '/assets/membership.css', array(), FRE_MEMBERSHIP_VER ) ;

	}
	function show_subscription_info($user_role){

		global $user_ID, $wpdb, $renewal_date, $plan_sku, $pack_type;
		$pack_type = 'bid_plan';
		if($user_role !== FREELANCER ){
			$pack_type = 'pack';
		}
		$pay_to_bid 	= ae_get_option( 'pay_to_bid', false );
		$is_free 		= ae_get_option('disable_plan', false);
		if( $user_role == FREELANCER && !$pay_to_bid ){
			return;
		}
		if( $user_role !== FREELANCER && $is_free ){
			return;
		}

		$table 			= $wpdb->prefix . 'membership';
		$subscription 	= get_mebership_of_member();
		$btn_html 		= $plan_info = $df_text = $renewal_text = '';
		$membership_page_id = ae_get_option('fre_membership_plans');
		$membership_url 	= get_permalink($membership_page_id);
		$df_text  = $plan_name	= '';

		if( $subscription ){
			$plan  = $pack 	= membership_get_pack($subscription->plan_sku, $subscription->pack_type);

			$expiry_time 	= (int) $subscription->expiry_time;
			$renewal_date 	= date('d M, Y', $subscription->expiry_time);
			$plan_name 	=	$subscription->plan_sku;
			$renewal_text = sprintf(__('Renewal Date','enginethemes'));

			if( $subscription->auto_renew == 'active' ){
				$btn_html = '<a  href="javascript:void(0)" class="fre-normal-btn-o btnCancelMembership">'.__('Cancel','enginethemes').'</a>';
			}

			$label = __('Your subscription is available.','enginethemes');
			if( $plan ){
				$plan_name = $plan->post_title;
			}

			if($expiry_time < time() ){
				$label = sprintf(__('Your subscription is expired. Your can check <a href="%s">membership plans</a> and subscribe to a plan.','enginethemes'), $membership_url );
				$renewal_text = __('Expired At','enginethemes');
			}

		} else {
			$label = '<p class="df-text-show">'.sprintf(__('You are currently not in any plan. Get started with one of our <a href="%s">membership plans</a> here.','enginethemes'), $membership_url).'</p>';
		} ?>

		<!--  Start fre-membership-block !-->
		<div class="fre-profile-box fre-membership-block" id="membershipBlock">
	        <div class="portfolio-container">
	            <div class="profile-freelance-portfolio">
	                <div class="row">
	                    <div class="col-sm-6 col-xs-12">
	                        <h2 class="freelance-portfolio-title"><?php _e('Subscription Details','enginethemes');?> </h2>
	                    </div>
						<div class="col-sm-6 col-xs-12">
	                        <div class="freelance-portfolio-add">
	                        	<?php echo $btn_html;?>
	                        </div>
	                    </div>
					</div>
					<div class="subscription-info">

						<?php  if($label) { ?>
							<p class="df-text-show"> <?php echo $label; ?></p>
							<?php } ?>
							<?php  if($plan_name) { ?>
							<p class='pack-info pack-name-line'> <span><?php _e('Plan Name','enginethemes');?></span><?php echo $plan_name; ?></p>
					<?php } ?>
					<?php  if($renewal_text) { ?>
						<p class="pack-info"><span> <?php echo $renewal_text;?></span><?php echo $renewal_date; ?></p>
					<?php } ?>


					</div>
				</div>
	        </div>
	    </div>
	    <!--  END fre-membership-block !-->
		<?php
	}

}
new Fre_MemberShip_Front();
add_action('after_setup_theme','init_fre_membership');
function init_fre_membership(){
}