<?php
class MJE_Claim{

	public function claim_mail_free(){
		return '<p>Dear [display_name],</p>
				<p>Thank you for your request, here are the details of your job verification:</p>

				<p><strong>Job verification info</strong></p>
				<p>
					<ul>
					<li>Name: [display_name]</li>
					<li>Skype: [user_skype]</li>
					<li>Email: [user_email]</li>
					<li>Alternate email: [user_email_alt]</li>
					</ul>
				</p>
				<p>Detail: [link_detail]</p>
				<p>Sincerely,<br>[blogname]</p>';
	}

	public function claim_mail_cash(){
		return '<p>Dear [display_name],</p>
				<p>Please send the payment to XXX account to complete the order.</p>
				<p>Here are the details of your transaction:</p>
				<p><strong>Claim info</strong></p>
				<p>
					<ul>
					<li>Name: [display_name]</li>
					<li>Skype: [user_skype]</li>
					<li>Email: [user_email]</li>
					<li>Alternate email: [user_email_alt]</li>
					</ul>
				</p>
				<p>Detail: [link_detail]</p>
				<p><strong>Invoice</strong></p>
				<p>
					<ul>
					<li>Invoice No: [invoice_id]</li>
					<li>Date: [date]</li>
					<li>Payment: [payment]</li>
					<li>Total: [total]</li>
					</ul>
				</p>
				<p>Sincerely,<br>[blogname]</p>';
	}

	public function claim_mail(){
		return '<p>Dear [display_name],</p>
				<p>Thank you for your payment.</p>
				<p>Here are the details of your transaction:</p>

				<p><strong>Claim info</strong></p>
				<p>
					<ul>
					<li>Name: [display_name]</li>
					<li>Skype: [user_skype]</li>
					<li>Email: [user_email]</li>
					<li>Alternate email: [user_email_alt]</li>
					</ul>
				</p>
				<p>Detail: [link_detail]</p>
				<p><strong>Invoice</strong></p>
				<p>
					<ul>
					<li>Invoice No: [invoice_id]</li>
					<li>Date: [date]</li>
					<li>Payment: [payment]</li>
					<li>Total: [total]</li>
					</ul>
				</p>
				<p>Sincerely,<br>[blogname]</p>';
	}

	public function claim_government(){
		ob_start();
		?>
		<ul class="guide-photo">
		<li>Optional: Cover up any confidential information (ex: license or passport number)</li>
		<li>Scan or take a picture of your ID with your digital camera</li>
		<li>Make sure you can clearly see your name, birthday and photo</li>
		<li>Save the photo to your desktop</li>
		<li>Click the button below to upload your ID photo</li>
		</ul>
		<?php
		return ob_get_clean();
	}
	public function get_count_finished($jobid){
		$arg=array(
			'post_type'=>'mjob_order',
			'posts_per_page'=>-1,
			'post_status'=>'finished',
			'post_parent'=>$jobid
		);
		return count(get_posts($arg));
	}

	public function current_claim($jobid){
		$arg=array(
			'post_type'=>'mje_claims',
			'posts_per_page'=>-1,
			'post_status'=>'mje_pending,mje_verifying,mje_approved',
			'post_parent'=>$jobid
		);
		$stt=$this->get_status_claim();
		$arrs=get_posts($arg);
		foreach($arrs as $arr){
			return $stt[$arr->post_status];
		}
		return false;
	}

	public function can_claim($jobid){
		$current_user_id=get_current_user_id();
		$mjob = get_post($jobid);
		$ae_option=new AE_Options;
		$order_finish=($ae_option->get_option('claim_order_finished')<>false)?$ae_option->get_option('claim_order_finished'):2;
		return ($current_user_id == $mjob->post_author && ($mjob->post_status == "publish" or $mjob->post_status == "unpause") && ($this->get_count_finished($jobid) >= $order_finish) && !$this->current_claim($jobid));
	}

    public function current_approved($jobid){
        $arg=array(
            'post_type'=>'mje_claims',
            'posts_per_page'=>-1,
            'post_status'=>'mje_approved',
            'post_parent'=>$jobid
        );
        return count(get_posts($arg));
    }

    public function is_verified($jobid){
        $mjob = get_post($jobid);
        $ae_option=new AE_Options;
        $order_finish=($ae_option->get_option('claim_order_finished')<>false)?$ae_option->get_option('claim_order_finished'):2;
        return (($mjob->post_status == "publish" or $mjob->post_status == "unpause") && ($this->get_count_finished($jobid) >= $order_finish) && $this->current_approved($jobid));
    }

    public function get_verififies(){
        $arrs=get_posts(array('post_type'=>'mjob_post','posts_per_page'=>-1,'post_status'=>'all'));
        $datas=array();
        foreach($arrs as $arr){
            if($this->is_verified($arr->ID)){
                $datas[]=$arr->ID;
            }
        }
        return $datas;
    }

	public function get_claims($status="",$page=1){
		$ae_option=new AE_Options;
		$claim_number=($ae_option->get_option('claim_number'))?$ae_option->get_option('claim_number'):10;
		$post_status=($status)?$status:'mje_pending,mje_verifying,mje_declined,mje_approved';
		$user=wp_get_current_user();
		$arg=array(
			'post_type'=>'mje_claims',
			'post_status'=>$post_status,
			'posts_per_page'=>$claim_number,
			'paged'=>$page
		);
		if(!current_user_can( 'administrator' )){
			$arg['author']=$user->ID;
		}
		$total=$this->get_count_claims($status);
		$numpage=($total%$claim_number==0)?($total/$claim_number):(($total-($total%$claim_number))/$claim_number +1);
		return (object)array('datas'=>get_posts($arg),'numpage'=>$numpage);

	}

	public function get_count_claims($status=""){
		$post_status=($status)?$status:'mje_pending,mje_verifying,mje_declined,mje_approved';
		$user=wp_get_current_user();
		$arg=array(
			'post_type'=>'mje_claims',
			'post_status'=>$post_status,
			'posts_per_page'=>-1,
		);
		if(!current_user_can( 'administrator' )){
			$arg['author']=$user->ID;
		}
		return count(get_posts($arg));
	}
	public function update_post_status($postid,$status){
		$my_post = array(
		  'ID'           => $postid,
		  'post_status'  =>$status
	  );
	  return wp_update_post($my_post);
	}
	public function get_page_list(){
		$data=array();
		$arg=array(
			'post_type'=>'page',
			'posts_per_page'=>-1,
		);
		$arrs=get_posts($arg);
		foreach($arrs as $arr){
			$data[$arr->ID]=$arr->post_title;
		}
		return $data;
	}

	public function is_admin(){
		return (current_user_can('administrator'))?true:false;
	}
	//this  function return true for action decision
	public function can_decision($claimid,$status){
		ob_start();
		$claim=get_post($claimid);
		switch ($status) {
			case 'mje_verifying':
				return ($claim->post_status=="mje_pending")?true:false;
				break;
			case 'mje_approved':
				return ($claim->post_status=="mje_verifying")?true:false;
				break;
			case 'mje_declined':
				return ($claim->post_status=="mje_verifying" or $claim->post_status=="mje_approved")?true:false;
				break;
			default:
				return false;
		}
	}
	//this function export button enable or not of button follow current status
	public function button_decision($claimid,$status){
		ob_start();
		$claim=get_post($claimid);
		$class_enable="claim_decision";
		$class_disable="claim-disable";
		$popup="";
		switch ($status) {
			case 'mje_verifying':
				$label= __("Verify","mje_verification");
				if($claim->post_status=="mje_pending"){

					$class="btn-verify ".$class_enable;
				}
				else{
					$label=($claim->post_status=="mje_verifying")? __("Verifying","mje_verification"):$label;
					$class="btn-verify ".$class_disable;
				}

				break;
			case 'mje_approved':
				$label=__("Approve","mje_verification");
				if($claim->post_status=="mje_verifying"){
					$class="btn-approve ".$class_enable;
				}
				else{
					$label=($claim->post_status=="mje_approved")? __("Approved","mje_verification"):$label;
					$class="btn-approve ".$class_disable;
				}
				break;
			case 'mje_declined':
				$label= __("Decline","mje_verification");
				if ($claim->post_status=="mje_verifying" or $claim->post_status=="mje_approved"){

					$class="btn-decline ";
					$popup='data-toggle="modal" data-target="#popup_reason_decline"';
				}
				else{
					$label=($claim->post_status=="mje_declined")? __("Declined","mje_verification"): $label;
					$class="btn-decline ".$class_disable;
				}
				break;
			default:
				return false;
		}
		?>
		<button type="button" class="<?php mje_button_classes(  array( 'submit-payment', 'waves-effect', 'waves-light','action-button-claim' ) ); ?> <?php echo $class ?>" data-id="<?php echo $claimid; ?>" <?php echo $popup; ?> data-status="<?php echo $status ?>"><?php echo $label;  ?></button>
		<?php
		return ob_get_clean();
	}

	public function notice_decision($claimid){
		ob_start();
		$ae_option=new AE_Options;
		$et_admin= new ET_Admin;
		$default_option=$et_admin->get_default_options();
		$chars=100;

		$ver_sel=($ae_option->get_option('claim_notice_verifying_seller'))?strip_tags($ae_option->get_option('claim_notice_verifying_seller')):$default_option['claim_notice_verifying_seller'];
		$app_sel=($ae_option->get_option('claim_notice_approved_seller'))?strip_tags($ae_option->get_option('claim_notice_approved_seller')):$default_option['claim_notice_approved_seller'];
		$dec_sel=($ae_option->get_option('claim_notice_declined_seller'))?strip_tags($ae_option->get_option('claim_notice_declined_seller')):$default_option['claim_notice_declined_seller'];

		$ver_adm=($ae_option->get_option('claim_notice_verifying_admin'))?strip_tags($ae_option->get_option('claim_notice_verifying_admin')):$default_option['claim_notice_verifying_admin'];
		$app_adm=($ae_option->get_option('claim_notice_approved_admin'))?strip_tags($ae_option->get_option('claim_notice_approved_admin')):$default_option['claim_notice_approved_admin'];
		$dec_adm=($ae_option->get_option('claim_notice_declined_admin'))?strip_tags($ae_option->get_option('claim_notice_declined_admin')):$default_option['claim_notice_declined_admin'];

		$arrs=(object)array(
			'ver_sel'=>(object)array(
					'label'=>$this->cutstr($ver_sel,$chars),
					'tooltip'=>(strlen($ver_sel)>strlen($this->cutstr($ver_sel,$chars)))?'data-toggle="tooltip" data-placement="top" title="'.$ver_sel.'"':''
			),
			'app_sel'=>(object)array(
					'label'=>$this->cutstr($app_sel,$chars),
					'tooltip'=>(strlen($app_sel)>strlen($this->cutstr($app_sel,$chars)))?'data-toggle="tooltip" data-placement="top" title="'.$app_sel.'"':''
			),
			'dec_sel'=>(object)array(
					'label'=>$this->cutstr($dec_sel,$chars),
					'tooltip'=>(strlen($dec_sel)>strlen($this->cutstr($dec_sel,$chars)))?'data-toggle="tooltip" data-placement="top" title="'.$dec_sel.'"':''
			),

			'ver_adm'=>(object)array(
					'label'=>$this->cutstr($ver_adm,$chars),
					'tooltip'=>(strlen($ver_adm)>strlen($this->cutstr($ver_adm,$chars)))?'data-toggle="tooltip" data-placement="top" title="'.$ver_adm.'"':''
			),
			'app_adm'=>(object)array(
					'label'=>$this->cutstr($app_adm,$chars),
					'tooltip'=>(strlen($app_adm)>strlen($this->cutstr($app_adm,$chars)))?'data-toggle="tooltip" data-placement="top" title="'.$app_adm.'"':''
			),
			'dec_adm'=>(object)array(
					'label'=>$this->cutstr($dec_adm,$chars),
					'tooltip'=>(strlen($dec_adm)>strlen($this->cutstr($dec_adm,$chars)))?'data-toggle="tooltip" data-placement="top" title="'.$dec_adm.'"':''
			),
		);

		$claim=get_post($claimid);
		$status=$claim->post_status;
		switch ($status) {
			case 'mje_verifying':
				$class="btn-notice btn-verify";
				return ($this->is_admin())?'<span class="'.$class.'" '.$arrs->ver_adm->tooltip.'>'.$arrs->ver_adm->label.'</span>':'<span class="'.$class.'" '.$arrs->ver_sel->tooltip.'>'.$arrs->ver_sel->label.'</span>';
				break;
			case 'mje_approved':
				$class="btn-notice btn-approve";
				return ($this->is_admin())?'<span class="'.$class.'" '.$arrs->app_adm->tooltip.'>'.$arrs->app_adm->label.'</span>':'<span class="'.$class.'" '.$arrs->app_sel->tooltip.'>'.$arrs->app_sel->label.'</span>';
				break;
			case 'mje_declined':
				$class="btn-notice btn-decline";
				return ($this->is_admin())?'<span class="'.$class.'" '.$arrs->dec_adm->tooltip.'>'.$arrs->dec_adm->label.'</span>':'<span class="'.$class.'" '.$arrs->dec_sel->tooltip.'>'.$arrs->dec_sel->label.'</span>';
				break;
			default:
				return '';
		}
	}

	public function cutstr($str,$num, $str_more=" ..."){
		$cut=strrpos(substr($str,0,$num)," ");
		$stri =  substr($str,0,($cut>0)?$cut:strlen($str));
		return (strlen($str)<=$num)?$str:$stri.$str_more;
	}

	public function get_status_claim(){
		return array(
			'mje_pending'=> __('Pending','mje_verification'),
			'mje_verifying'=> __('Verifying','mje_verification'),
			'mje_declined'=>__('Declined','mje_verification'),
			'mje_approved'=>__('Approved','mje_verification'),
		);
	}

	public function get_status_claim_full($claim_id){
		$ae_option=new AE_Options;
		$et_admin= new ET_Admin;
		$default_option=$et_admin->get_default_options();
		$pending=($ae_option->get_option('claim_tooltip_pending'))?strip_tags($ae_option->get_option('claim_tooltip_pending')):$default_option['claim_tooltip_pending'];
		$verifying=($ae_option->get_option('claim_tooltip_verifying'))?strip_tags($ae_option->get_option('claim_tooltip_verifying')):$default_option['claim_tooltip_verifying'];
		$approved=($ae_option->get_option('claim_tooltip_approved'))?strip_tags($ae_option->get_option('claim_tooltip_approved')):$default_option['claim_tooltip_approved'];
		$declined=get_post_meta($claim_id,'decline_reason_meta',true);
		$claim=get_post($claim_id);
		$stt=$claim->post_status;

		switch ($stt) {
			case 'mje_pending':
				return (object)array('stt'=>'mje_pending','name'=>__('Pending','mje_verification'),'icon'=>'<i class="fa fa-hourglass-start" aria-hidden="true"></i>','tooltip'=>$pending);
				break;
			case 'mje_verifying':
				return (object)array('stt'=>'mje_verifying','name'=>__('Verifying','mje_verification'),'icon'=>'<i class="fa fa-clock-o" aria-hidden="true"></i>','tooltip'=>$verifying);
				break;
			case 'mje_approved':
				return (object)array('stt'=>'mje_approved','name'=>__('Approved','mje_verification'),'icon'=>'<i class="fa fa-check" aria-hidden="true"></i>','tooltip'=>$approved);
				break;
			case 'mje_declined':
				return (object)array('stt'=>'mje_declined','name'=>__('Declined','mje_verification'),'icon'=>'<i class="fa fa-ban" aria-hidden="true"></i>','tooltip'=>$declined);
				break;
			default:
				return false;
		}
	}

	public function get_claim_log_status($claimid){
		ob_start();

			$logs=get_post_meta($claimid,'status_history_meta',true);
			$logs=($logs=="" or $logs=="[]")?array():(array)json_decode($logs);
			$stt=$this->get_status_claim();
			if(!empty($logs)){
			?>
			<table class="hr_table_status">
				<thead>
					<tr>
						<th class="hr_left"><span><?php _e('Date & Time','mje_verification'); ?></span></th>
						<th class="hr_right hr_25"><span><?php _e('Status','mje_verification'); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach($logs as $log){
						?>
						<tr>
							<td><span><?php echo date(get_option('date_format').' '.get_option('time_format'),$log->date)?></span></td>
							<td class="hr_right"><span class="<?php echo $log->status ?>"><?php echo $stt[$log->status] ?></span></td>
						</tr>
						<?php
						}
					?>
				</tbody>
			</table>
			<?php
			}
			else{
			?>
			<div class="empty_logs_claim"><span><?php _e("- No changes in this request's status -",'mje_verification'); ?></span></div>
			<?php
			}
		return ob_get_clean();
	}

	public function get_claim_log_price($claimid){
		ob_start();

		$logs=get_post_meta($claimid,'price_history_meta',true);
		$logs=($logs=="" or $logs=="[]")?array():(array)json_decode($logs);
		if(!empty($logs)){
		?>
		<table class="hr_table_status">
			<thead>
				<tr>
					<th class="hr_left"><span><?php echo _e('Date & Time','mje_verification'); ?></span></th>
					<th class="hr_right hr_25"><span class="mje-blue"><?php echo _e('From','mje_verification'); ?></span></th>
					<th class="hr_right hr_25"><span class="mje-red"><?php echo _e('To','mje_verification'); ?></span></th>
				</tr>
			</thead>
			<tbody>

				<?php
					foreach($logs as $log){
					$prices=explode(' -> ',$log->price);
					$price_from=(isset($prices[0]))?mje_shorten_price($prices[0]):'';
					$price_to=(isset($prices[1]))?mje_shorten_price($prices[1]):'';
					?>
					<tr>
						<td><span><?php echo date(get_option('date_format').' '.get_option('time_format'),$log->date)?></span></td>
						<td class="hr_right"><span class="mje-blue"><?php echo $price_from; ?></span></td>
						<td class="hr_right"><span class="mje-red"><?php echo $price_to; ?></span></td>
					</tr>
					<?php
					}
				?>
			</tbody>
		</table>
		<?php
		}
		else{
		?>
		<div class="empty_logs_claim"><span><?php _e("- No changes in this mjob's price -",'mje_verification'); ?></span></div>
		<?php
		}

		return ob_get_clean();
	}

	public function registry_post_type($name,$slug,$support_arr){
		//$support_arr=array("title","editor","thumbnail","comments");
		$labels = array(
				'name' => _x($name, $name.' General Name'),
				'singular_name' => _x($name, $name.' Singular Name'),
				'add_new' => _x('Add New', 'Add New'),
				'add_new_item' => __('Add New'.$name),
				'edit_item' => __('Edit '.$name),
				'new_item' => __('New '.$name),
				'view_item' => __('View '.$name),
				'search_items' => __('New'),
				'not_found' =>  __('Nothing found', 'u-design'),
				'not_found_in_trash' => __('Nothing found in Trash'),
				'parent_item_colon' => ''
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
						'menu_position' => 7,
						'taxonomies' => array('post_tag'),
						'supports'=>$support_arr
					);
		register_post_type( $slug , $args);
	}
	public function get_count_notice_claim(){
		$arrs=get_posts(array(
						'post_type'=>'mje_claims',
						'posts_per_page'=>-1,
						'post_status'=>'mje_pending,mje_verifying,mje_approved',
						'meta_query' => array(
							array(
								'key'     => 'notice_meta',
								'value'   => 1,
								'compare' => '=',
							),
						),
				  ));
		return count($arrs);
	}
	public function claim_option_meta($arrs){
		ob_start();
		global $post;
		foreach($arrs as $val=>$arr){
			$type=(isset($arr['type'])?$arr['type']:'text');
			switch ($type) {
				case 'status':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<select name="<?php echo $val; ?>" class="class_<?php echo $val; ?>">
							<?php
							$datas = $arr['data'];
							foreach($datas as $dt_v=>$dt_name){
							$select=($dt_v==$post->post_status)?'selected="selected"':'';
							?>
							<option value="<?php echo $dt_v; ?>" <?php echo $select ?>><?php echo $dt_name; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					<?php
					break;
				case 'select':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<select name="<?php echo $val; ?>" class="class_<?php echo $val; ?>">
							<?php
							$datas = $arr['data'];
							foreach($datas as $dt_v=>$dt_name){
							$select=($dt_v==get_post_meta($post->ID,$val,true))?'selected="selected"':'';
							?>
							<option value="<?php echo $dt_v; ?>" <?php echo $select ?>><?php echo $dt_name; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					<?php
					break;
				case 'textarea':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<textarea class="class_<?php echo $val; ?>" name="<?php echo $val ?>"><?php echo get_post_meta($post->ID,$val,true) ?></textarea>
					</div>
					<?php
					break;
				case 'file':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<input type="text" name="<?php echo $val ?>" value="<?php echo get_post_meta($post->ID,$val,true) ?>" />
						<a href="<?php echo get_post_meta($post->ID,$val,true) ?>" download>Download</a>
					</div>
					<?php
					break;
				case 'date':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<input type="date" name="<?php echo $val ?>" value="<?php echo get_post_meta($post->ID,$val,true) ?>" />
					</div>
					<?php
					break;
				case 'json_price':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<textarea style="display:none" name="<?php echo $val ?>"><?php echo get_post_meta($post->ID,$val,true) ?></textarea>
						<?php
						$arrs=get_post_meta($post->ID,$val,true);
						$arrs=($arrs=="" or $arrs=="[]")?json_decode('[]'):json_decode($arrs);
						if(!empty($arrs)){
						?>
						<ul>
							<?php
							foreach($arrs as $arr){
							$price_arr=explode(' -> ',$arr->price);
							$str_price=(isset($price_arr[1]))?mje_shorten_price($price_arr[0]).' -> '.mje_shorten_price($price_arr[1]):$price_arr[0];
							?>
							<li><span class="mje_verifying"><?php echo $str_price; ?></span> - <?php echo date(get_option('date_format').' '.get_option('time_format'),$arr->date)?></li>
							<?php
							}
							?>
						</ul>
						<?php
						}
						?>
					</div>
					<?php
					break;
				case 'json_status':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<textarea style="display:none" name="<?php echo $val ?>"><?php echo get_post_meta($post->ID,$val,true) ?></textarea>
						<?php
						$arrs=get_post_meta($post->ID,$val,true);
						$arrs=($arrs=="" or $arrs=="[]")?json_decode('[]'):json_decode($arrs);
						$stt=$this->get_status_claim();
						if(!empty($arrs)){
						?>
						<ul>
							<?php
							foreach($arrs as $arr){
							?>
							<li><span class="<?php echo $arr->status; ?>"><?php echo $stt[$arr->status] ?></span> - <?php echo date(get_option('date_format').' '.get_option('time_format'),$arr->date)?></li>
							<?php
							}
							?>
						</ul>
						<?php
						}
						?>
					</div>
					<?php
					break;
				case 'photo':
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<input style="display:none"  name="<?php echo $val ?>" value="<?php echo get_post_meta($post->ID,$val,true) ?>" />
						<?php
						$url=wp_get_attachment_url( get_post_meta($post->ID,$val,true) );
						?>
						<a class="claim_link_image" href="<?php echo $url; ?>" target="_blank"><img src="<?php echo $url ?>" height="100px" /></a>
					</div>
					<?php
					break;
				case 'link':
					$target=(isset($arr['target']))?'target="'.$arr['target'].'"':'';
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<a href="<?php echo $arr['href']?>" <?php echo $target; ?>><?php echo $arr['html']?></a>
					</div>
					<?php
					break;
				case 'text_price':
					$target=(isset($arr['target']))?'target="'.$arr['target'].'"':'';
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<span><?php echo mje_shorten_price(get_post_meta($post->ID,$val,true)); ?></span>
                        <input type="hidden" name="<?php echo $val; ?>" value="<?php echo get_post_meta($post->ID,$val,true); ?>" />
                    </div>
					<?php
					break;
				case 'text_percent':
					$target=(isset($arr['target']))?'target="'.$arr['target'].'"':'';
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<span><?php echo get_post_meta($post->ID,$val,true)."%"; ?></span>
                        <input type="hidden" name="<?php echo $val; ?>" value="<?php echo get_post_meta($post->ID,$val,true); ?>" />
					</div>
					<?php
					break;
				case 'hidden':
					?>
					<input style="display:none"  name="<?php echo $val ?>" value="<?php echo get_post_meta($post->ID,$val,true) ?>" />
					<?php
					break;
				default:
					?>
					<div class="mje_item">
						<label><?php echo _e($arr['name'],'mje_verification') ?></label>
						<input type="text" name="<?php echo $val; ?>" value="<?php echo get_post_meta($post->ID,$val,true); ?>" />
					</div>
					<?php
			}//end switch
		}//end foreach
		return ob_get_clean();
	}


}
?>