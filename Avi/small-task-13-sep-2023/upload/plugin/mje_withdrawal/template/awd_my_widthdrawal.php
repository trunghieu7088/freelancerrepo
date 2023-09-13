<div class="mje-my-withdraw">
	<?php
		$awd = new MJE_AWD();
		echo $awd->get_last_auto();
		$request_active=(isset($_GET['tab']) && $_GET['tab']=="withdraw-history")?'':'active';
		$history_active=(isset($_GET['tab']) && $_GET['tab']=="withdraw-history")?'active':'';
	?>

	<div class="information-items-detail box-shadow information-withdraw-list">
		<div class="tabs-information">
			<ul class="nav nav-tabs nav-tabs-payment" role="tablist">
				<li role="presentation" class="<?php echo $request_active; ?>">
					<a href="#withdraw-request" role="tab" data-toggle="tab"><span><?php _e('Withdrawal Request', 'mjeawd'); ?></span></a>
				</li>
				<li role="presentation" class="next <?php echo $history_active; ?>">
					<a href="#withdraw-history" role="tab" data-toggle="tab"><span><?php _e('Withdrawn History', 'mjeawd'); ?></span></a>
				</li>
			</ul>
			<div class="tab-content">
				<div id="withdraw-request" class="tab-pane <?php echo $request_active; ?>" role="tabpanel">
					<div class="withdraw-filter-wrap">
						<div class="withdraw-filter">
							<select name="" id="awd_bank_request" class="withdraw-filter-account awd_select_option" data-request="true" data-page="1">
								<option value=""><?php _e('All Account', 'mjeawd'); ?></option>
								<?php
									$bank_actives=$awd->get_payments_active();
									foreach($bank_actives as $bankid=>$bank_active){
										?>
										<?php //custom code avi ?>
										<?php if($bankid != 'bank') : ?>
										<option value="<?php echo $bankid; ?>"><?php echo $bank_active['args']['title']; ?></option>
										<?php endif; ?>
										<?php //end custom code avi ?>
										<?php
									}
								?>
							</select>

							<select name="" id="awd_status_request" class="withdraw-filter-status awd_select_option" data-request="true" data-page="1">
								<option value=""><?php _e('All Status', 'mjeawd'); ?></option>
								<?php
								$arrs=$awd->get_status_request();
								foreach($arrs as $value=>$name){
								?>
									<option value="<?php echo $value; ?>"><?php echo $name ?></option>
								<?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="withdraw-request-list">
						<div class="mje-table">
							<div class="mje-table-head">
								<div class="mje-table-col col-withdraw-account"><?php _e('Withdraw Account', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-date"><?php _e('Requested Date', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-log"><?php _e('Admin Event Logs', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-amount"><?php _e('Total Amount', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-status"><?php _e('Status', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-action"><?php _e('Action', 'mjeawd'); ?></div>
							</div>
							<div class="mje-table-body awd_list_request">
								<?php
								$posts=$awd->get_awds(true);
								$arrs=$posts->datas;
								foreach($arrs as $arr){
									$all=$awd->get_all_meta($arr->ID);
									$log =__('N/A','mje_awd');
									if($all->log<>"" and $all->log<>"[]"){
										$logs=json_decode($all->log);
										$last_log=$logs[count($logs)-1];
										$log=date(get_option('date_format'),$last_log->date);
									}
								?>
								<div class="mje-table-row hide-cancel-request">
									<div class="mje-table-col col-withdraw-account"><?php echo $all->bank_name.' '.__('account','mjeawd').' <span class="visible-sm visible-xs">'.__('Withdraw','mjeawd').'<b>'.$all->amount_html.'</b></span>'; ?></div>
									<div class="mje-table-col col-withdraw-date"><?php echo '<span class="visible-sm-inline visible-xs-inline">'.__('Requested on','mjeawd').'</span>'.$all->date_html; ?></div>
									<div class="mje-table-col col-withdraw-log"><?php echo $log; ?></div>
									<div class="mje-table-col col-withdraw-amount hidden-sm hidden-xs">
										<span data-toggle="tooltip" data-placement="top" data-html="true" title="<?php echo $all->amount_fee;  ?>">
											<?php echo $all->total_html; ?>
										</span>
									</div>
									<div class="mje-table-col col-withdraw-status"><?php echo '<span class="status_'.$all->status_id.'" data-toggle="tooltip" data-placement="top" data-html="true" title="'.$awd->get_tooltip($arr->ID).'">'.$all->status_name.'</span>'; ?></div>
									<div class="mje-table-col col-withdraw-action"><?php echo '<span class="withdraw-cancel-btn click_cancel_button" data-id="'.$arr->ID.'">'.__('Cancel','mjeawd').'</span>'; ?></div>
								</div>
								<?php
								}
								?>
							</div>
						</div>
						<div class="mje-table-pag paginations-wrapper">
							<div class="paginations pag_request">
								<?php
									$page=1;
									$pages=$posts->numpage;
									if($pages>1){
										echo ($page>1)?'<a class="page-numbers awd_page_click" href="javascript:void(0)" data-page="'.($page-1).'" data-request="true"><i class="fa fa-angle-double-left"></i></a>':'';
										for($i=1; $i<=$pages ;$i++){
											if($page==$i){
											?>
												<span class="page-numbers current"><?php echo $i; ?></span>
											<?php
											}
											else{
											?>
												<a class="page-numbers awd_page_click" href="javascript:void(0)" data-page="<?php echo $i ?>" data-request="true">
													<?php echo $i; ?>
												</a>
											<?php
											}
										}
										echo ($page<$pages)?'<a class="page-numbers awd_page_click" href="javascript:void(0)" data-page="'.($page+1).'" data-request="true"><i class="fa fa-angle-double-right"></i></a>':'';
									}
								?>
							</div>
						</div>
					</div>
				</div>
				<div id="withdraw-history" class="tab-pane <?php echo $history_active; ?>" role="tabpanel">
					<div class="withdraw-filter-wrap">
						<div class="withdraw-filter">
							<select name="" id="awd_bank_history" class="withdraw-filter-account awd_select_option" data-request="false" data-page="1">
								<option value=""><?php _e('All account', 'mjeawd'); ?></option>
								<?php
									$bank_actives=$awd->get_payments_active();
									foreach($bank_actives as $bankid=>$bank_active){
										?>
										<?php //custom code avi ?>
										<?php if($bankid != 'bank') : ?>
										<option value="<?php echo $bankid; ?>"><?php echo $bank_active['args']['title']; ?></option>
										<?php endif; ?>
										<?php //end ?>
										<?php
									}
								?>
							</select>

							<select name="" id="awd_status_history" class="withdraw-filter-status awd_select_option" data-request="false" data-page="1">
								<option value=""><?php _e('All status', 'mjeawd'); ?></option>
								<?php
								$arrs=$awd->get_status_history();
								foreach($arrs as $value=>$name){
								?>
									<option value="<?php echo $value; ?>"><?php echo $name ?></option>
								<?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="withdraw-request-list">
						<div class="mje-table">
							<div class="mje-table-head">
								<div class="mje-table-col col-withdraw-account"><?php _e('Withdraw Account', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-date"><?php _e('Requested Date', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-log"><?php _e('Admin Event Logs', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-amount"><?php _e('Total Amount', 'mjeawd'); ?></div>
								<div class="mje-table-col col-withdraw-status"><?php _e('Status', 'mjeawd'); ?></div>
							</div>
							<div class="mje-table-body awd_list_history">
								<?php
								$posts=$awd->get_awds(false);
								$arrs=$posts->datas;
								foreach($arrs as $arr){
									$all=$awd->get_all_meta($arr->ID);
									$log =__('N/A','mje_awd');
									if($all->log<>"" and $all->log<>"[]"){
										$logs=json_decode($all->log);
										$last_log=$logs[count($logs)-1];
										$log=date(get_option('date_format'),$last_log->date);
									}
								?>
								<div class="mje-table-row">
									<div class="mje-table-col col-withdraw-account"><?php echo $all->bank_name.' '.__('account','mjeawd').' <span class="visible-sm visible-xs">'.__('Withdraw','mjeawd').'<b>'.$all->amount_html.'</b></span>'; ?></div>
									<div class="mje-table-col col-withdraw-date"><?php echo '<span class="visible-sm-inline visible-xs-inline">'.__('Requested on','mjeawd').'</span>'.$all->date_html; ?></div>
									<div class="mje-table-col col-withdraw-log"><?php echo $log; ?></div>
									<div class="mje-table-col col-withdraw-amount hidden-sm hidden-xs">
										<span data-toggle="tooltip" data-placement="top" data-html="true" title="<?php echo $all->amount_fee;  ?>">
											<?php echo $all->total_html; ?>
										</span>
									</div>
									<div class="mje-table-col col-withdraw-status"><?php echo '<span class="status_'.$all->status_id.'" data-toggle="tooltip" data-placement="top" data-html="true" title="'.$awd->get_tooltip($arr->ID).'">'.$all->status_name.'</span>'; ?></div>
								</div>
								<?php
								}
								?>
							</div>
						</div>
						<div class="paginations-wrapper">
							<div class="paginations pag_history">
								<?php
									$page=1;
									$pages=$posts->numpage;
									if($pages>1){
										echo ($page>1)?'<a class="page-numbers awd_page_click" href="javascript:void(0)" data-page="'.($page-1).'" data-request="false"><i class="fa fa-angle-double-left"></i></a>':'';
										for($i=1; $i<=$pages ;$i++){
											if($page==$i){
											?>
												<span class="page-numbers current"><?php echo $i; ?></span>
											<?php
											}
											else{
											?>
												<a class="page-numbers awd_page_click" href="javascript:void(0)" data-page="<?php echo $i ?>" data-request="false">
													<?php echo $i; ?>
												</a>
											<?php
											}
										}
										echo ($page<$pages)?'<a class="page-numbers awd_page_click" href="javascript:void(0)" data-page="'.($page+1).'" data-request="false"><i class="fa fa-angle-double-right"></i></a>':'';
									}
								?>
							</div>
						</div>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="modal fade" id="awd_cancel_request" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">
						<img src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt="">
					</span>
				</button>
                <h4 class="modal-title" id="myModalLabelForgot"><?php _e('Cancel request confirmation', 'mjeawd');?></h4>
            </div>

            <div class="modal-body">
                <div class="withdraw-detail-modal">
                	<p style="margin-bottom: 40px;"><?php _e('Are you sure want to cancel this request?', 'mjeawd'); ?></p>
					<button class="waves-effect waves-light btn-submit awd_cancal_request"><?php _e('Yes', 'mjeawd');?></button>
				</div>
            </div>
        </div>
    </div>
</div>

<script type="text/templates" id="awd_item_request">
	<div class="mje-table-row hide-cancel-request">
		<div class="mje-table-col col-withdraw-account">{{= arr.account }}</div>
		<div class="mje-table-col col-withdraw-date">{{= arr.date }}</div>
		<div class="mje-table-col col-withdraw-log">{{= arr.log }}</div>
		<div class="mje-table-col col-withdraw-amount hidden-sm hidden-xs">
			<span data-toggle="tooltip" data-placement="top" data-html="true" title="{{= arr.amount_fee }}">{{= arr.amount }}</span>
		</div>
		<div class="mje-table-col col-withdraw-status">{{= arr.status }}</div>
		<div class="mje-table-col col-withdraw-action">{{= arr.button }}</div>
	</div>
</script>
<script type="text/templates" id="awd_item_history">
	<div class="mje-table-row">
		<div class="mje-table-col col-withdraw-account">{{= arr.account }}</div>
		<div class="mje-table-col col-withdraw-date">{{= arr.date }}</div>
		<div class="mje-table-col col-withdraw-log">{{= arr.log }}</div>
		<div class="mje-table-col col-withdraw-amount hidden-sm hidden-xs">
			<span data-toggle="tooltip" data-placement="top" data-html="true" title="{{= arr.amount_fee }}">{{= arr.amount }}</span>
		</div>
		<div class="mje-table-col col-withdraw-status">{{= arr.status }}</div>
	</div>
</script>