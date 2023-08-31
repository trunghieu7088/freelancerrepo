<?php
function update_email_of_membership($confirm_new_email, $email){

	if( !is_wp_error($confirm_new_email) ){

		global $wpdb, $user_ID;
		$membership = $wpdb->prefix . 'fre_membership';
		$sql = $wpdb->prepare(
			"
			UPDATE $membership
			SET user_email = %s
			WHERE user_id = %d
			",
		    $email, $confirm_new_email
		);

		$wpdb->query( $sql);
	}
}
add_action('confirm_new_email','update_email_of_membership',10,2);