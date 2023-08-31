<?php
add_action('pre_footer','membership_debug');

function membership_debug(){
	global $wpdb, $user_ID;

	$sandbox_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	$live_url = "https://www.paypal.com/cgi-bin/webscr";

	$return_page_id = ae_get_option('membership_successful_return');
	$return_url = home_url();
	if($return_page_id){
		$return_url = get_permalink($return_page_id);
	}
	$notify_url = home_url('?paypalListener=paypal_standard_IPN');


	$tbl_member = $wpdb->prefix . 'fre_membership';
	$tbl_subsc 	= $wpdb->prefix . 'fre_subscriptions';
	$tbl_member = $wpdb->prefix . 'fre_membership';
	$tbl_subsc 	= $wpdb->prefix . 'fre_subscriptions';
	$now = time();
     $sql = "SELECT *  FROM $tbl_member m LEFT JOIN $tbl_subsc sub ON m.subscr_id = sub.id
		WHERE (   CAST(expiration_date AS DATE) = CAST(NOW() AS DATE)  OR expiry_time < $now  )  AND sub.payment_gw = 'fre_credit'  GROUP BY  m.user_id, sub.id LIMIT 50 ";
		$del_id = isset($_GET['del_id']) ? $_GET['del_id'] : 'n';
		//$sql ="DELETE FROM $tbl_member WHERE  user_id =".$del_id;
		//$sql ="DELETE FROM $tbl_member WHERE user_id =1";
		//$result = $wpdb->query($sql);
		//var_dump($result);
		$del_log = isset($_GET['dellog'])? 1: 0;
		if($del_log)
			unlink(PP_LOG_PATH);
	$list_user = $wpdb->get_results($sql);
	foreach($list_user as $user){
		// echo '<pre>';
		// var_dump($user);
		// echo '<pre>';
		//die();
	}
	// $subcriber = get_mebership_of_member();
	// echo '<pre>';
	// var_dump($subcriber);
	// echo '</pre>';
	// echo time();
	$user_id = $user_ID;
	$user_id = 1;
	$sql_check = $wpdb->prepare( " SELECT *  FROM {$tbl_member}  WHERE user_id = %d", $user_id );
	//$record 	= $wpdb->get_row($sql_check, OBJECT);
	//var_dump($record->id);

	?>
	<div class="row" >
		<div class="container">
			<div class="project-detail-box" style="margin-top: 30px; font-size: 16px;">
				<h3> Debug Details</h3>
			<?php

			$pack_type 	= 'pack';
			$sku 		= 'description';


			echo '<pre>';
			membership_get_pack();
			// var_dump($result);
			// var_dump($metas);
			echo '</pre>';

			$tbl_member 		= $wpdb->prefix . 'fre_membership';
			$tbl_subscriptions 	= $wpdb->prefix . 'fre_subscriptions';
			$sql = "SELECT * FROM $tbl_member ORDER BY id DESC LIMIT 10";
			$records = $wpdb->get_results($sql);
			?>
			<a target="_blank" href="<?php echo MEMBERSHIP_URL.'/languages/membership.html';?>"> List Translate</a>
			<a target="_blank" href="<?php echo PP_LOG_URL;?>" >PayPal Log</a> &nbsp; &nbsp;
			<a target="_blank" href="<?php echo ET_CRON_URL;?>" >Lab Cron Log</a> &nbsp; &nbsp;

			<?php
			if( $records ){ ?>
				<table class="table">
				<tr>
					<td> ID </td><td> User ID </td> <td> User Login </td><td> Email</td><td> Sub_ID</td>
				</tr>
				<?php

				foreach ($records as $member) {
					echo '<tr>';
					echo '<td>'.$member->id.' </td>';echo '<td>'.$member->user_id.' </td>';echo '<td>'.$member->user_login.' </td>';echo '<td> '.$member->user_email . ' </td>';
					echo '<td> '.$member->subscr_id . ' </td>';


					echo '</tr>';
				}
				echo '</table>';
			}
			echo 'Show 5 latest subscription records: <br />';

			$sql = "SELECT * FROM $tbl_subscriptions ORDER BY id DESC LIMIT 15";
			$results = $wpdb->get_results($sql);
			if( $results ){ ?>
				<table class="table">
				<tr>
					<td>ID </td>
					<td>User ID </td>
					<td>sku</td>
					<td> price </td>
					<td> remain_posts </td>
					<td> api_subscr_id </td>
					<td> expiry_time </td>
					<td> payment_gw </td>
					<td align="center">Pack </td>
					<td align="center"> Auto Renew</td>
				</tr>
				<?php

				foreach ($results as $member) {
					$expiry_time = date('M d, Y', $member->expiry_time);
					if( $member->expiry_time < time() ){
						$expiry_time = 'Expired';
					}
					echo '<tr>';
					echo '<td>'.$member->id.' </td>';
					echo '<td>'.$member->user_id.' </td>';
					echo '<td> '.$member->plan_sku . ' </td>';
					echo '<td> '.$member->price . ' </td>';
					echo '<td> '.$member->remain_posts . ' </td>';
					echo '<td> '.$member->api_subscr_id . ' </td>';
					echo '<td> '.$expiry_time .' </td>';
					echo '<td  align="center"> '.$member->payment_gw.' </td>';
					echo '<td align="center"> '.$member->pack_type . ' </td>';
					echo '<td align="center"> '.$member->auto_renew . ' </td>';
					echo '</tr>';
				}
				echo '</table>';
			} ?>

			</div>
		</div>
	</div>
	<?php
}

function read_log_file($log_file){

	if(file_exists($log_file)){
		$content = file_get_contents($log_file, true);
		echo $content;
	} else {
		echo 'File '. $log_file .' not exists';
		et_member_log('Create file.', ET_LOG_PATH);
	}
}

function overview_subscriver_debug(){
	global $user_ID;
	$subcriber = get_mebership_of_member();
	echo '<p>';
	echo 'Free bid of current user: '.fre_get_free_bid_current_month();
	echo '<br /> Number bid  available of current user: '.fre_get_total_bid($user_ID);
	$seconds_until_task_will_run =  wp_next_scheduled( 'excute_membership_event' ) - time();
	echo '<br />Number seconds will run schedule: '.$seconds_until_task_will_run;

	echo '<br />Show 5 latest membership records: <br />';
	echo '</p>';
}

?>

