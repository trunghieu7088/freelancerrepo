<?php
function fre_credit_debug(){
	global $wpdb;
	$post_id =2279;
	$sql = "SELECT * FROM $wpdb->postmeta where post_id = ".$post_id;
	$record = $wpdb->get_results($sql);
	echo '<pre>';
	var_dump($record);
	echo '</pre>';
}
add_action('wp_footer','fre_credit_debug');