<?php

add_action('wp_footer','ok_membership_debug');
function ok_membership_debug(){

	global $wpdb;
	echo '<div class="row" > <div class="container">'; echo '<pre>';

	$tbl_member =$tbl_membership = $wpdb->prefix . 'membership';
	$tbl_order = $wpdb->prefix . 'membership_order';


	$sql 	= "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_membership}'";
	$struct = $wpdb->get_results($sql);
	$cols = array();
	foreach($struct as $col){
		$cols[] = $col->COLUMN_NAME;
	}
	echo $tbl_membership.'('.join(",",$cols).')';

	$sql 	= "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND  TABLE_NAME = '{$tbl_order}'";
	$struct = $wpdb->get_results($sql);
	$cols = array();
	foreach($struct as $col){
		$cols[] = $col->COLUMN_NAME;
	}
	echo $tbl_membership.'('.join(",",$cols).')';

	echo '</pre>';
	echo '</div></div>';
}