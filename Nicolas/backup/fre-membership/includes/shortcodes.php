<?php



function fre_checkout_form_html(){
	ob_start();
	fre_memhership_template_part('templates/checkout','form');
	return ob_get_clean();
 }
add_shortcode( 'fre_membership_checkout', 'fre_checkout_form_html' );

function fre_membership_thankyou($args) {
	ob_start();
	fre_memhership_template_part('templates/thank','you');?>
	<?php
	return ob_get_clean();
}
add_shortcode( 'membership_successful_return', 'fre_membership_thankyou' );


