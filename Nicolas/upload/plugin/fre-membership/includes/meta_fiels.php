<?php
function add_membership_plan_meta($meta){
	array_push($meta, 'et_subscription_time');
	array_push($meta, 'et_sub_title');
	array_push($meta,'subscription_type');
	$mebership_stripe = ae_get_option('enable_mebership_stripe', false);
	if($mebership_stripe){
		array_push($meta, 'stripe_pricing_id');

	}
	return $meta;
}
add_filter('ae_package_metas','add_membership_plan_meta');
add_filter('bid_plan_metas','add_membership_plan_meta');

