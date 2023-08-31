<script type="text/template" id="template_edit_form">
	<form action="qa-update-badge" class="edit-plan engine-payment-form">
		<input type="hidden" name="id" value="{{= id }}">

		<div class="form payment-plan membership-plan">
			<div class="form-item f-left-all clearfix">
				<div class="width33p">
					<div class="label"><?php _e("SKU", 'enginethemes'); ?><span class="dashicons dashicons-editor-help" title="SKU must be unique and should be character or number."></span></div>
					<input value="{{= sku }}" class="bg-grey-input width50p not-empty required" name="sku" type="text" />
				</div>
				<?php
				$mebership_stripe = ae_get_option('enable_mebership_stripe', false);
				if($mebership_stripe){?>
					<div class="width33p">
						<div class="label"><?php _e("Stripe Pricing ID",'enginethemes');?></div>
						<input class="bg-grey-input not-empty required" name="stripe_pricing_id" value="{{= stripe_pricing_id }}" placeholder="price_..." type="text" />
					</div> <!-- end form-item stripe!-->
				<?php } ?>

			</div>
			<div class="form-item">
				<div class="label"><?php _e("Enter a name for your plan", 'enginethemes'); ?></div>
				<input value="{{= post_title }}" class="bg-grey-input not-empty required" name="post_title" type="text">
			</div>
			<div class="form-item">
				<div class="label"><?php _e("This text will appear below your plan name", 'enginethemes'); ?></div>
				<input value="{{= et_sub_title }}" class="bg-grey-input not-empty required" name="et_sub_title" type="text">
			</div>
			<div class="form-item f-left-all clearfix">
				<div class="width33p col-et-4">
					<div class="label"><?php _e("Price", 'enginethemes'); ?></div>
					<input value="{{= et_price }}" class="bg-grey-input width50p not-empty is-number required number" min="0"  name="et_price" type="text" />
					<?php ae_currency_code(); ?>
				</div>

				<div class="width33p col-et-4">
					<div class="label"><?php _e("Subscription Type",'enginethemes');?></div>
						<select class=" et_subscription_time" name="et_subscription_time" >
							<option value='1'  <# if ( et_subscription_time =='1' ) { #> selected <# } #> >Monthly </option>
							<option value='3'  <# if ( et_subscription_time =='3' ) { #> selected <# } #>>3 Months</option>
							<option value='6'  <# if ( et_subscription_time =='6' ) { #> selected <# } #>>6 Months</option>
							<option value='12' <# if ( et_subscription_time =='12' ) { #> selected <# } #> >1 Year</option>
						</select>

				</div>
				<div class="width33p col-et-4 et-f-right">

					<div class="label"><?php  _e("Number of Projects",'enginethemes');; ?></div>
					<input value="{{= et_number_posts }}" class=" bg-grey-input width50p not-empty is-number required" type="text" name="et_number_posts" />
				</div>


			</div>
			<div class="form-item f-left-all clearfix">
				<div class="width33p col-et-4">
					<div class="label"><?php _e("Project availability time",'enginethemes');?></div>
					<input value="{{= et_duration }}" class="bg-grey-input width50p not-empty is-number required number" type="text" name="et_duration" /><?php _e('days', 'enginethemes');?>

				</div>
				<div class="col-et-4 ">
					<div class="label"><?php _e("Project type",'enginethemes');?></div>
					<?php ae_tax_dropdown( 'project_type' ,
						array(  'class' => 'chosen-single tax-item',
								'hide_empty' => false,
								'hierarchical' => true ,
								'id' => 'project_type' ,
								'show_option_all' => __("Select project type", 'enginethemes') ,
								'value' => 'id',
								'name' => 'project_type'
							)
						) ;?>
				</div>


			</div> <!-- end form-item!-->


			<div class="form-item">
				<div class="label"><?php _e("Short description about this plan",'enginethemes');?></div>
				<input class="bg-grey-input not-empty" name="post_content" type="text" value="{{= post_content }}" />
			</div>
			<div class="submit">
				<button  class="btn-button engine-submit-btn add_payment_plan">
					<span><?php _e( 'Save Package' , 'enginethemes' ); ?></span><span class="icon" data-icon="+"></span>
				</button>
				or <a href="#" class="cancel-edit"><?php _e( "Cancel" , 'enginethemes' ); ?></a>
			</div>
		</div>
	</form>
</script>