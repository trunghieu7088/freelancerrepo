<div class="item-claim">
	<div class="row">
		<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 clearfix mjob-info">
			<div class="mjob-photo"><a href="{{= mjob_link }}"><img src="{{= mjob_src }}" /></a></div>
			<div class="mjob-meta">
				<div class="mjob-title">
					<span>{{= mjob_title }}</span>
				</div>
				<div class="claim-detail">
					<ul>
						<li><a class="link-claim-detail" href="{{= claim_link }}"><?php _e('View details','mje_verification'); ?></a></li>
						 <# if( is_admin ){ #>
						<li class="only-admin-user-claim"><a class="link-claim-seller"><span class="claim-by"><?php _e('By','mje_verification'); ?></span> {{= author }} </a></li>
						<# } #>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 clearfix claim-status">
				<span class="{{= claim_stt }} tooltip-claim" data-toggle="tooltip" data-html="true" data-placement="top" data-original-title='{{= claim_tooltip }}'>{{= claim_icon }} {{= claim_name }}</span>
		</div>
	</div>
</div>