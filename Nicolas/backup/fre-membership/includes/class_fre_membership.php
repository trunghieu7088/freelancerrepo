<?php
class Freelancer_Membership{
	 private static $instance;
	 public $table;
	function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'membership_order';


	}
	static function get_instance(){
		if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
	}



	function get_available_bid_of_freelancer(){
		global $wpdb, $user_ID;
		$table = $wpdb->prefix . 'membership_order';
		$now = strtotime('now');
		$sql =  $wpdb->prepare(
			"
				SELECT remain_posts
				FROM {$table}
				WHERE expiry_time > $now AND user_id =  %d  AND order_status = %s
			",
			$user_ID,'publish'
		);
		return (int) $wpdb->get_row( $sql );
	}


	function get_number_pending_bid(){
		global $wpdb, $user_ID;
		$table = $wpdb->prefix . 'membership';
		$now = strtotime('now');
		$sql =  $wpdb->prepare(
			"
				SELECT remain_posts
				FROM $table
				WHERE expiry_time > $now AND user_id =  %d AND order_status = %s
			",
			$user_ID, 'pending'
		);
		return (int) $wpdb->get_var( $sql );
	}
}
new Freelancer_Membership();
function get_number_bid_available(){
	$fre_member = Freelancer_Membership::get_instance();
	return $fre_member->get_available_bid_of_freelancer();
}

function get_number_bid_pending(){
	$fre_member = Freelancer_Membership::get_instance();
	return (int)$fre_member->get_number_pending_bid();
}
/**
 * get oldest packaage that is not expired and remain_post >0
 * @since: 1.2
*/
function get_oldest_pack_available(){
	global $wpdb, $user_ID;
	$table = $wpdb->prefix . 'membership';

	$sql = "SELECT id, remain_posts FROM {$table}
			WHERE user_id = $user_ID
		 	AND remain_posts > 0 AND `order_status` = 'publish'
		 	ORDER BY expiry_time ASC ";

	$result = $wpdb->get_row($sql);

	if($result){
		return $result;
	}
	return 0;

}
/**
 * @since: 1.2
  * replace function fre_membership_update_bid_left;
  * auto call and run from theme after bid successful.
*/
function update_remain_bid_of_membership(){

	global $user_ID, $wpdb;
	$table = $wpdb->prefix . 'membership';
	$now = strtotime('now');
	$soonest_expire_order = get_oldest_pack_available();
	$new_remains = (int) $soonest_expire_order->remain_posts - 1;
	$wpdb->query(
		$wpdb->prepare(
		"
		UPDATE $table
		SET remain_posts = %d
		WHERE id = %d
		",
		max(0, $new_remains),   $soonest_expire_order->id)
	);
}

function clone_current_member_plan_order( $package, $member_id){
	global $wpdb;
	$member_order = $wpdb->prefix . 'membership_order';

	$remain_posts = (int)$package->et_number_posts;
	$string = "+1 month";
	if( (int) $package->et_subscription_time > 1 )
		$string = "+{$package->et_subscription_time} months";

	$expiry_time = strtotime($string);

	$expiry_date = date( 'Y-m-d h:i:s', $expiry_time );
	$pack_type = $package->post_type;



	$wpdb->insert(
		$member_order,
		array(
			'user_id' => $member_id,
			'order_date' => current_time( 'mysql' ),
			'pack_type' => $pack_type,
			'pack_sku' => $package->sku,
			'expiry_time' =>  $expiry_time,
			'expiry_date' => $expiry_date,
			'remain_posts' =>$remain_posts,
			'order_status' => 'publish',
			'is_sent_mail' => 0,
			'auto_renew' => 'active',

		)
	);
}

