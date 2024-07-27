<div class="item-claim-mobile mjob-item">
	<div class="clearfix mjob-info-mobile">
		<div class="mjob-photo-mobile">
			<a href="{{= mjob_link }}"><img src="{{= mjob_src_medium }}" /></a>
		</div>
		<div class="mjob-item__entry">
			<div class="mjob-item__title">
				<h2 class="trimmed" title="job 7">
					<div class="dotdotdot">
						<a href="{{= mjob_link }}">{{= mjob_title }}</a>
					</div>
				</h2>
			</div>
			<# if( is_admin ){ #>
				<div class="only-admin-user-claim">
					<a class="link-claim-seller"><span class="claim-by"><?php _e('By','mje_verification'); ?></span> {{= author }} </a>
				</div>
			<# } #>
			<hr />
			<div class="row">
				<div class="col-xs-6 clearfix">
					<span class="{{= claim_stt }} tooltip-claim" data-toggle="tooltip" data-html="true" data-placement="top" data-original-title='{{= claim_tooltip }}'>{{= claim_icon }} {{= claim_name }}</span>
				</div>
				<div class="col-xs-6 clearfix view-claim-details">
					<a href="{{= claim_link }}"><?php _e('View details','mje_verification'); ?></a>
				</div>
			</ul>
		</div>
	</div>
</div>