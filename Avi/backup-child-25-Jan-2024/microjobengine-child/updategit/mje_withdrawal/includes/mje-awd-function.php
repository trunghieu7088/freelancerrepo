<?php
class MJE_AWD {
	public function registry_post_type($name, $slug, $support_arr, $menu_icon = 'dashicons-admin-post') {
		//$support_arr=array("title","editor","thumbnail","comments");
		$labels = array(
			'name' => _x($name, $name . ' General Name'),
			'singular_name' => _x($name, $name . ' Singular Name'),
			'add_new' => _x('Add New', 'Add New'),
			'add_new_item' => __('Add New ' . $name),
			'edit_item' => __('Edit ' . $name),
			'new_item' => __('New ' . $name),
			'view_item' => __('View ' . $name),
			'search_items' => __('Search'),
			'not_found' => __('Nothing found', 'mjeawd'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => '',
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 50,
			'taxonomies' => array('post_tag'),
			'supports' => $support_arr,
			'menu_icon' => $menu_icon,
		);
		register_post_type($slug, $args);
	}

	function cutstr($string, $c1, $c2) {
		if (strlen($c1) > strlen($string)) {
			return "-1";
		}
		$vt1 = strpos($string, $c1);
		$vt2 = strpos($string, $c2, $vt1 + strlen($c1));
		if ($vt1 === false or $vt2 === false) {
			return "-1";
		} else {
			return substr($string, $vt1 + strlen($c1), $vt2 - ($vt1 + strlen($c1)));
		}

	}

	function get_mail_newrequest() {
		return '<p>Dear [display_name],</p>
				<p>Thank you for your request, here are the details of your withdrawal:</p>

				<p><strong>Withdrawal request info</strong></p>
				<p>
					[request_information]
				</p>
				<p>Sincerely,<br>[blogname]</p>';
	}

	public function is_limited_request(){
		$times=(ae_get_option('awd_times_per_day'))?ae_get_option('awd_times_per_day'):MJE_AWD_MIN_TIMES;
		//$today = getdate();
		$arrs=get_posts(
			array(
				'post_type'=>'mje_auto_withdraw',
				'author'=>get_current_user_id(),
				'posts_per_page'=>-1,
				'post_status'=>'all',
				'date_query' => array(
					array(
					  'year'  => date('Y',current_time( 'timestamp' )),
					  'month' => date('m',current_time( 'timestamp' )),
					  'day'   => date('d',current_time( 'timestamp' )),
					),
			  	),
			)
		);
		return (count($arrs)>=$times);
	}

	public function get_page_list() {
		$data = array();
		$arg = array(
			'post_type' => 'page',
			'posts_per_page' => -1,
		);
		$arrs = get_posts($arg);
		foreach ($arrs as $arr) {
			$data[$arr->ID] = $arr->post_title;
		}
		return $data;
	}

	public function get_field_data_payment($arrs, $bank) {
		ob_start();
		$payments = new MJE_AWD();
		if (!empty($arrs)) {
			foreach ($arrs as $arr) {
				$id = $bank . '_' . $arr['id'];
				$val = $payments->get_payment_info(get_current_user_id(), $bank, $arr['id']);
				switch ($arr['type']) {
				case 'select':
					//
					break;
				case 'textarea':
					//
					break;
				default:
					?>
						<div class="form-group clearfix">
							<div class="input-group">
								<label for="<?php echo $arr['id']; ?>"><?php echo $arr['title'] ?></label>
								<input type="<?php echo $arr['type'] ?>" name="<?php echo $id; ?>" id="<?php echo $id; ?>" placeholder="" value="<?php echo $val; ?>" class="data_payment awd_require <?php echo $arr['class'] ?>">
							</div>
						</div>
						<?php
}
			}
		}
		return ob_get_clean();
	}

	function awd_payment_default() {
		//return array('bank','paypal','credit','2checkout');
		return array('bank', 'paypal');
	}

	function get_payments_all() {
		$datas = array();
		$payments = array();
		$arrs = apply_filters('mje_all_payment', $payments, 10, 2);
		foreach ($arrs as $arr => $payment) {
			$name = $payment['args']['title'];
			$icon = $payment['args']['icon'];
			$defaults = $this->awd_payment_default();
			if (in_array($arr, $defaults)) {
				$op = ae_get_option($arr);
				if ( ( isset($op['enable']) && $op['enable'] ) || $arr == "bank") {
					$datas[] = (object) array('id' => $arr, 'name' => $name, 'icon' => $icon);
				}
			} else {
				if (ae_get_option('mje_' . $arr . '_enable')) {
					$datas[] = (object) array('id' => $arr, 'name' => $name, 'icon' => $icon);
				}
			}

		}
		return $datas;
	}

	function get_payments_active() {
		$datas = array();
		$payments = array();
		$arrs = apply_filters('mje_all_payment', $payments, 10, 2);
		$defaults = $this->awd_payment_default();
		foreach ($arrs as $arr => $payment) {
			if (ae_get_option('awd_enable_' . $arr) && (ae_get_option('mje_' . $arr . '_enable') || in_array($arr, $defaults))) {
				$datas[$arr] = $payment;
			}
		}
		return $datas;
	}

	function get_payments_detail($arr) {
		$payments = array();
		$arrs = apply_filters('mje_all_payment', $payments, 10, 2);
		return $arrs[$arr];
	}

	function get_payments_name($arr) {
		$detail = $this->get_payments_detail($arr);
		return (isset($detail['args']['title'])) ? $detail['args']['title'] : '';
	}

	function get_payments_fields($arr) {
		$datas = array();
		$detail = $this->get_payments_detail($arr);
		$fields = $detail['fields'];
		foreach ($fields as $field) {
			$datas[] = $field['id'];
		}
		return $datas;
	}
	/* get value of field payment info */
	public function get_payment_info($userid, $paymentid, $field) {
		$payment_info = get_user_meta($userid, 'payment_info', true);
		//custom code here
		//if ($paymentid == "paypal") {
		//	return (isset($payment_info[$paymentid])) ? $payment_info[$paymentid] : '';
		//} else {
			return (isset($payment_info[$paymentid][$field])) ? $payment_info[$paymentid][$field] : '';
		//}
		//end custom code
	}
	/* set values of field payment info */
	public function set_payment_info($userid, $paymentid, $datas) {
		$payment_info = get_user_meta($userid, 'payment_info', true);
		$payment_info[$paymentid] = $datas;
		return update_user_meta($userid, 'payment_info', $payment_info);
	}

	public function is_empty_payment($userid) {
		$payment_info = get_user_meta($userid, 'payment_info', true);
		return empty($payment_info);
	}
	public function is_empty_payment_id($userid, $id) {
		$payment_info = get_user_meta($userid, 'payment_info', true);
		return empty($payment_info[$id]);
	}

	public function awd_load_payment_info($id) {
		ob_start();
		if ($this->is_empty_payment_id(get_current_user_id(), $id)) {
			?>
			<div class="withdraw-pending">
				<div><?php _e("You haven't configed for this payment account", 'mjeawd');?></div>
				<a href="<?php echo et_get_page_link('payment-method') . '?tab=' . $id . '-account'; ?>"><?php _e('Config this account', 'mjeawd');?></a>
			</div>
			<?php
} else {
			?>
			<div class="mje-table check-mje-table-html">
				<?php
			$link_payment = $this->get_link_bank($id);
			$fee_html = ($this->is_fix_fee($id)) ? mje_format_price($this->get_fee_amount($id)) : $this->get_fee_amount($id) . '%';
			$detail = $this->get_payments_detail($id);
			$fields = $detail['fields'];
			foreach ($fields as $field) {
				$css_class = 'item-payment-info item-'.$field['id'];
				?>
					<div class="mje-table-row <?php echo $css_class;?>">
						<div class="mje-table-col account-info-title"><?php echo $field['title'] ?>:</div>
						<div class="mje-table-col account-info-value"><?php echo $this->get_payment_info(get_current_user_id(), $id, $field['id']); ?></div>
					</div>
					<?php
			}
			?>
			</div>
			<br/>
			<p class="item-payment-info-transfer-fee"><?php _e('Transfer fee', 'mjeawd');?>: <b><?php echo $fee_html; ?></b></p>
			<?php
			if ($link_payment) {
							?>
						<p><?php _e('You can check related infomation at ', 'mjeawd');?><a href="<?php echo $link_payment; ?>" target="_blank" style="text-decoration: underline;"><?php echo $link_payment; ?></a></p>
						<?php
			}
			?>
			<a href="<?php echo et_get_page_link('payment-method') . '?tab=' . $id . '-account'; ?>"><?php _e('Update account', 'mjeawd');?></a>
			<?php
}
		return ob_get_clean();
	}

	public function get_status_button_admin($id) {
		ob_start();
		$post = get_post($id);
		$awd_status = $this->get_status();
		$status = $post->post_status;
		if (isset($awd_status[$status])) {
			switch ($status) {
			case "awd_pending":
				?>
					<div class="item-bt">
						<span class="awd-span-button button_awd_processing change_status_admin change_status_button" data-status="awd_processing" data-id="<?php echo $post->ID; ?>">
							<?php _e('Processing', 'mjeawd');?>
						</span>
					</div>
					<?php
break;
			case "awd_processing":
				?>
					<div class="item-bt">
						<span class="awd-span-button button_awd_approved change_status_admin change_status_button" data-status="awd_approved" data-id="<?php echo $post->ID; ?>">
							<?php _e('Approve', 'mjeawd');?>
						</span>
					</div>
					<div class="item-bt">
						<span class="awd-span-button button_awd_declined change_status_admin change_status_button" data-status="awd_declined" data-id="<?php echo $post->ID; ?>">
							<?php _e('Decline', 'mjeawd');?>
						</span>
					</div>
					<?php
break;
				/*
					case "awd_approved":
						?>
						<span class="awd-span-button button_awd_approved awd_disable"><?php _e('Aprroved', 'mjeawd'); ?></span>
						<?php
						break;
					case "awd_declined":
						?>
						<span class="awd-span-button button_awd_approved awd_disable"><?php _e('Declined', 'mjeawd'); ?></span>
						<?php
						break;
				*/
			}
		}
		return ob_get_clean();
	}

	public function get_status_text($id) {
		ob_start();
		$post = get_post($id);
		$status = $this->get_status();
		?>
		<span class="mje-table-col col-withdraw-status change_status_text status_<?php echo $post->post_status; ?>" data-id="<?php echo $id; ?>">
			<?php echo (isset($status[$post->post_status])) ? $status[$post->post_status] : $post->post_status; ?>
		</span>
		<?php
return ob_get_clean();
	}

	public function get_link_bank($id) {
		$value = ae_get_option('awd_link_' . $id);
		return ($value != false) ? $value : '';
	}

	public function get_link_mywthdraw() {
		$arr = get_post(ae_get_option('awd_my_widthdrawal_page'));
		return (isset($arr->ID)) ? get_permalink($arr->ID) : '';
	}

	public function is_fix_fee($id) {
		return (ae_get_option('awd_feetype_' . $id) == 1);
	}

	public function get_fee_amount($id) {
		$value = ae_get_option('awd_fee_' . $id);
		return ($value != false) ? $value : 0;
	}

	public function get_fee_fix($id,$total) {
		$value = ae_get_option('awd_fee_' . $id);
		$fee = ($this->is_fix_fee($id)) ? $value : $total * ($value / 100);
		return round($fee, 2);
	}

	public function get_min_amount($id) {
		$value = ae_get_option('awd_min_' . $id);
		return ($value != false) ? $value : MJE_AWD_MIN_AMOUNT;
	}

	public function get_max_amount($id) {
		$revenues = ae_credit_balance_info(get_current_user_id());
		$available = $revenues['available']->balance;
		$fee = ae_get_option('awd_fee_' . $id) ? ae_get_option('awd_fee_' . $id) : 0;

		$max = ($this->is_fix_fee($id)) ? ($available - $fee) : $available / (1 + $fee / 100);
		return round($max, 2);
	}

	public function get_max_amount_by_user($id,$userid) {
		$revenues = ae_credit_balance_info($userid);
		$available = $revenues['available']->balance;
		$fee = ae_get_option('awd_fee_' . $id) ? ae_get_option('awd_fee_' . $id) : 0;

		$max = ($this->is_fix_fee($id)) ? ($available - $fee) : $available / (1 + $fee / 100);
		return round($max, 2);
	}

	public function get_fee_total($id,$total){
		$fee = ae_get_option('awd_fee_' . $id) ? ae_get_option('awd_fee_' . $id) : 0;

		$total_fee = ($this->is_fix_fee($id)) ? ($total + $fee) : $total * (1 + $fee / 100);
		return round($total_fee, 2);
	}

	public function get_status() {
		return array(
			'awd_pending' => 'Pending',
			'awd_processing' => 'Processing',
			'awd_approved' => 'Approved',
			'awd_declined' => 'Declined',
			'awd_cancelled' => 'Cancelled',
		);
	}
	public function get_status_request() {
		return array(
			'awd_pending' => __('Pending','mjeawd'),
			'awd_processing' => __('Processing','mjeawd'),
		);
	}

	public function get_status_history() {
		return array(
			'awd_approved' => __('Approved','mjeawd'),
			'awd_declined' => __('Declined','mjeawd'),
			'awd_cancelled' => __('Cancelled','mjeawd'),
		);
	}

	public function get_status_cancel() {
		return array(
			'awd_pending' => __('Pending','mjeawd'),
			'awd_processing' => __('Processing','mjeawd'),
			'awd_approved' => __('Approved','mjeawd'),
		);
	}

	public function update_status($postid, $status) {
		$my_post = array(
			'ID' => $postid,
			'post_status' => $status,
		);
		return wp_update_post($my_post);
	}

	public function get_status_arg($request = true, $status = "") {
		$all_stt = $this->get_status();
		if ($status) {
			if (isset($all_stt[$status])) {
				return $status;
			}
		} else {
			$awd_status_request = ($request) ? $this->get_status_request() : $this->get_status_history();
			$arr_stt = array();
			foreach ($awd_status_request as $stt_val => $stt_name) {
				$arr_stt[] = $stt_val;
			}
			return $arr_stt;
		}
		return 0;
	}

	public function get_count_awds($request = true, $status = "", $payment = "") {
		$user = wp_get_current_user();
		$arg = array(
			'post_type' => 'mje_auto_withdraw',
			'post_status' => $this->get_status_arg($request, $status),
			'posts_per_page' => -1,
			'author' => $user->ID,
		);
		if ($payment) {
			$arg['meta_query'] = array(array('key' => 'awd_bank_meta', 'value' => $payment));
		}
		return count(get_posts($arg));
	}

	public function get_awds($request = true, $page = 1, $status = "", $payment = "") {
		$awd_number = ae_get_option('awd_display') ? ae_get_option('awd_display') : MJE_AWD_DISPLAY;
		$user = wp_get_current_user();
		$arg = array(
			'post_type' => 'mje_auto_withdraw',
			'post_status' => $this->get_status_arg($request, $status),
			'posts_per_page' => $awd_number,
			'paged' => $page,
			'author' => $user->ID,
		);
		if ($payment) {
			$arg['meta_query'] = array(array('key' => 'awd_bank_meta', 'value' => $payment));
		}
		$total = $this->get_count_awds($request, $status, $payment);
		$numpage = ($total % $awd_number == 0) ? ($total / $awd_number) : (($total - ($total % $awd_number)) / $awd_number + 1);
		return (object) array('datas' => get_posts($arg), 'numpage' => $numpage);
	}

	public function get_all_meta($id) {
		$arr = get_post($id);
		$user = get_user_by('id', $arr->post_author);
		$status = $this->get_status();

		$status_id = $arr->post_status;
		$auto = get_post_meta($id, 'awd_auto_meta', true);
		$posttime = (get_post_meta($id, 'awd_date_meta', true))?get_post_meta($id, 'awd_date_meta', true):strtotime($arr->post_date);
		$postcreate = strtotime($arr->post_date);
		$period = ($auto) ? get_post_meta($id, 'awd_period_meta', true) : 0;
		$amount = (get_post_meta($id, 'awd_amount_meta', true))?get_post_meta($id, 'awd_amount_meta', true):0;

		$nexttime = $posttime + $period * 24 * 60 * 60;
		$posttime_html = date(get_option('date_format'), $posttime);
		$postcreate_html = date(get_option('date_format'), $postcreate);
		$nexttime_html = ($auto) ? date(get_option('date_format'), $nexttime) : '';
		$posttime_html_am = date(get_option('date_format') . ' ' . get_option('time_format'), $posttime);
		$nexttime_html_am = ($auto) ? date(get_option('date_format') . ' ' . get_option('time_format'), $nexttime) : '';
		$postcreate_html_am = date(get_option('date_format') . ' ' . get_option('time_format'), $postcreate);

		$bankid = get_post_meta($id, 'awd_bank_meta', true);

		//$fee=$this->get_fee_fix($bankid,$amount);
		$fee=(get_post_meta($id, 'awd_fee_meta', true))?get_post_meta($id, 'awd_fee_meta', true):$this->get_fee_fix($bankid,$amount);

		//$all=$this->get_fee_total($bankid,$amount);
		$all=$amount+$fee;

		$detail = $this->get_payments_detail($bankid);
		$args = $detail['args'];
		$fields = $detail['fields'];
		if(!empty($fields)){
			foreach ($fields as $field) {
				$bankdata[$field['id']] = array(
					'title' => $field['title'],
					'value' => $this->get_payment_info($user->ID, $bankid, $field['id']),
				);
			}
		}
		return (object) array(
			'author_id' => $user->ID,
			'author_name' => $user->display_name,
			'status_id' => $status_id,
			'status_name' => (isset($status[$status_id])) ? $status[$status_id] : $status_id,
			'auto' => $auto,
			'amount' => $amount,
			'amount_html' => mje_format_price($amount),
			'fee'=>$fee,
			'fee_html'=>mje_format_price($fee),
			'total'=>$all,
			'total_html'=>mje_format_price($all),
			'amount_fee'=>htmlspecialchars(mje_format_price($amount).' + '.mje_format_price($fee), ENT_QUOTES),
			'all' => get_post_meta($id, 'awd_auto_all_meta', true),
			'period' => $period,
			'created' => $postcreate,
			'created_html'=>$postcreate_html,
			'date' => $posttime,
			'next_date' => $nexttime,
			'date_html' => $postcreate_html,
			'next_html' => $nexttime_html,
			'date_html_am' => $postcreate_html_am,
			'next_html_am' => $nexttime_html_am,
			'bank_id' => $bankid,
			'bank_name' => $args['title'],
			'bank_data' => $bankdata,
			'reason' => get_post_meta($arr->ID, 'awd_reason_meta', true),
			'log' => get_post_meta($arr->ID, 'awd_status_history_meta', true),
		);
	}

	function get_tooltip($id){
		$et_admin= new ET_Admin;
        $default_option=$et_admin->get_default_options();
		$arr=get_post($id);
		$str="";
		if($arr->post_status=="awd_declined"){
			$str=get_post_meta($id,'awd_reason_meta',true);
		}
		else{
			$st=explode('_',$arr->post_status);
			if(isset($st[1])){
				$st_tooltip='awd_tooltip_'.$st[1];
				$str = (ae_get_option($st_tooltip))?ae_get_option($st_tooltip):$default_option[$st_tooltip];
			}
		}
		return htmlspecialchars($str, ENT_QUOTES);
	}

	function get_log_html($logs) {
		$logs = json_decode($logs);
		$stt = $this->get_status();
		ob_start();
		?>
		<ul>
			<?php
			foreach ($logs as $log) {
				?>
				<li>
					<span class="status_<?php echo $log->status; ?>"><?php echo $stt[$log->status] ?></span>
					 - <?php echo date(get_option('date_format') . ' ' . get_option('time_format'), $log->date) ?>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		return ob_get_clean();
	}

	public function get_auto() {
		$arrs = get_posts(array(
			'post_type' => 'mje_auto_withdraw',
			'author' => get_current_user_id(),
			'post_status'=>'all',
			'meta_query' => array(array('key' => 'awd_auto_meta', 'value' => 1)),
		));

		if (!empty($arrs)) {
			$status=$this->get_status();
			$arr = $arrs[0];
			return (in_array($status[$arr->post_status],$this->get_status_cancel()))?$arr->ID:false;
		}
		return false;
	}

	public function get_last_auto() {
		$auto = $this->get_auto();
		if ($auto) {
			$all = $this->get_all_meta($auto);
			$fields = $all->bank_data;
			?>
			<div class="box-shadow hide-cancel-request">
				<div class="revenues">
					<div class="title"><?php _e('Current Automated Request', 'mjeawd');?></div>
					<div class="line">
						<span class="line-distance"></span>
					</div>
					<div class="my-withdraw-info-wrap">
						<div class="row">
							<div class="col-sm-5">
								<div class="my-withdraw-info">
									<h4 class="withdraw-payment-title"><?php echo $all->bank_name; ?> account</h4>
									<?php
									foreach ($fields as $field) {
									?>
										<p><?php echo $field['value']; ?></p>
									<?php
									}
									?>
								</div>
							</div>
							<div class="col-sm-7">
								<div class="my-withdraw-info">
									<h4 class="withdraw-payment-title"><?php _e('Request Details', 'mjeawd');?></h4>
									<p><?php _e('Withdraw amount', 'mjeawd');?>: <b><span data-toggle="tooltip" data-placement="top" data-html="true" title="<?php echo $all->amount_fee; ?>"><?php echo $all->total_html; ?></span></b></p>
									<p><?php _e('Period', 'mjeawd');?>: <b><?php echo $all->period; ?> days</b></p>
								</div>
							</div>
						</div>
						<p class="timeline-request"><?php _e('One request has been sent on', 'mjeawd');?> <?php echo $all->created_html; ?> - <?php _e('Next request will be sent on', 'mjeawd');?> <?php echo $all->next_html; ?></p>
						<button class="btn-submit-o click_cancel_button" data-id="<?php echo $auto; ?>"><?php _e('Cancel', 'mjeawd');?></button>
					</div>
				</div>
			</div>
			<?php
			}
	}

	public function add_pending($userid,$amount){
		$wallet = AE_WalletAction()->getUserWallet($userid, 'freezable');
		$wallet->balance+=$amount;
		if(abs($wallet->balance)<0.01){
			$wallet->balance=0;
		}
		return AE_WalletAction()->setUserWallet($userid, $wallet , 'freezable');
	}

	public function add_balance($userid,$amount){
		$wallet = AE_WalletAction()->getUserWallet($userid);
		$wallet->balance+=$amount;
		if(abs($wallet->balance)<0.01){
			$wallet->balance=0;
		}
		return AE_WalletAction()->setUserWallet($userid, $wallet);
	}
	 public function update_logs($id){
	 	$logs=get_post_meta($id,'awd_status_history_meta', true );
		$logs=($logs=="" or $logs=="[]")?array():(array)json_decode($logs);
		$logs[]=array('status'=>get_post_status($id),'date'=>current_time('timestamp'));
		update_post_meta( $id, 'awd_status_history_meta', json_encode($logs) );
		return true;
	 }

	public function get_count_notice(){
		$arrs=get_posts(array(
				'post_type'=>'mje_auto_withdraw',
				'posts_per_page'=>-1,
				'post_status'=>'awd_pending',
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'notice_meta',
						'value'   =>'1',
						'compare' => '!='
					),
					array(
						'key'     => 'notice_meta',
						'compare' => 'NOT EXISTS'
					),
				),
		));
		return count($arrs);
	}

	public function get_auto_withdraw(){
		$datas=array();
		$arrs=get_posts(array(
			'post_type'=>'mje_auto_withdraw',
			'posts_per_page'=>-1,
			'post_status'=>'awd_approved',
			'meta_query' => array(
				array(
					'key'     => 'awd_auto_meta',
					'value'   =>'1',
					'compare' => '='
				)
			),
		));
		$today=strtotime(date('Y-m-d',current_time( 'timestamp' )));
		foreach($arrs as $arr){
			$wallet = AE_WalletAction()->getUserWallet($arr->post_author);
			$money=$wallet->balance;
			$bankid=get_post_meta($arr->ID,'awd_bank_meta',true);
			$awd_amount_meta=get_post_meta($arr->ID,'awd_amount_meta',true);
			$date_change=get_post_meta($arr->ID,'awd_date_meta',true);
			$date_point=($date_change)?$date_change:strtotime(date('Y-m-d',strtotime($arr->post_date)));
			$loop=(get_post_meta($arr->ID,'awd_period_meta',true))?get_post_meta($arr->ID,'awd_period_meta',true):0;
			$date_ex=$date_point + $loop*24*3600;
			$withdraw_all=get_post_meta($arr->ID,'awd_auto_all_meta',true);
			$money_need=($withdraw_all)?$this->get_min_amount($bankid):$this->get_fee_total($bankid, $awd_amount_meta);
			if(date('Y-m-d',$date_ex)<=date('Y-m-d',$today) && $loop >0){
				if($money>=$money_need){
					$datas[]=$arr;
				}
				else{
					update_post_meta($arr->ID, 'awd_date_meta', current_time('timestamp'));
					update_user_meta($arr->post_author,'awd_notice_meta',current_time('timestamp'));
				}

			}
		}
		return $datas;
	}

}
?>