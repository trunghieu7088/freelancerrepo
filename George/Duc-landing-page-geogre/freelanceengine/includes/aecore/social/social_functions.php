<?php

function get_social_role(){

	$social_user_roles = ae_get_option( 'social_user_role', false );
	if( ! $social_user_roles )
		return 'BOTH';
	$number = count($social_user_roles);
	$role = FREELANCER;

	if( $number == 1 ){
		$role = $social_user_roles[0];

	} else if($number == 2) {
		$role = 'BOTH';
	}
	return $role;
}
function fre_insert_social_user($user_data, $user_meta){

	$role = get_social_role();
	$update_role = 1; // role == BOTH options.
	$user_data['role'] = NULL;
	$user_meta['et_require_set_role'] = 1;

	if( $role == FREELANCER || $role == EMPLOYER ){
		$user_data['role'] = $role;
		$user_meta['et_require_set_role'] = 0;
		unset($user_meta['et_require_set_role']);
	}

	$user_pass = wp_generate_password(15, true);
	$user_login = $user_data['user_login'];
	$user_data['user_pass'] = $user_pass;

	$user_id = wp_insert_user($user_data);

	if( is_wp_error( $user_id ) ){

		if( username_exists($user_login) ){
			$user_login = $user_login.rand(2,10);
			$user_data['user_login'] = $user_login;
		}
		if( isset( $user_data['user_email'] ) && email_exists($user_data['user_email']) ){
			$resp = array(
				'success' => false,
				'redirect_url' => et_get_page_link('login'),
				'msg' =>__('Your email is exists. Please use your email to login.',ET_DOMAIN),
			);
			wp_send_json($resp);
			unset($user_data['user_email']);
		}

		$user_id = wp_insert_user($user_data);

	}
	if( $user_id ){
		foreach ($user_meta as $meta_key => $meta_value) {
			update_user_meta($user_id, $meta_key, $meta_value);
		}

		$creds = array( 'user_login' =>  $user_login, 'user_password' => $user_pass, 'remember' =>true );
		$user = wp_signon( $creds, false );
		if($user){
			wp_set_current_user( $user_id, $user_login );
			wp_set_auth_cookie( $user_id );
		}

	}
	return $user_id;

}
function fre_insert_google_user($userinfo, $avatar){
	$emails          = explode( "@", $userinfor->email );
	$userinfor->name = $username = $emails[0];
	$username        = $userinfor->name;
	$role = get_social_role();
	$user_pass = wp_generate_password(18,true);
	$args = array(
		'user_login' => $username,
		'user_pass' => $user_pass,
		'user_email' =>  $userinfor->email,
	);
	if($role !== 'BOTH'){
		$args['role'] = $role;
	}
	$user_id =  wp_insert_user($args);
	if( ! is_wp_error($user_id) ){

		update_user_meta($user_id,'et_avatar', $avatar);
		update_user_meta($user_id,'et_google_id', $avatar);
		$creds = array( 'user_login' =>  $username, 'user_password' => $user_pass, 'remember' =>true );
		$user = wp_signon( $creds, false );
      	if ( ! is_wp_error($user) ){
        	$userID = $user->ID;
			wp_set_current_user( $userID, $user_login );
			wp_set_auth_cookie( $userID, true, false );
			do_action( 'wp_login', $userinfor->email );
      	}
	}
	return $user_id;

}
 function get_social_user( $social_name, $social_id ) {
	$args  = array(
		'meta_key'   => $social_name,
		'meta_value' => trim( $social_id ),
		'number'     => 1
	);
	$users = get_users( $args );
	if ( ! empty( $users ) && is_array( $users ) ) {
		return $users[0];
	} else {
		return false;
	}
}
function fre_social_set_role_popup(){ ?>
	<div class="modal modal-setrole" tabindex="-1" id="setRoleModal" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
				    <h5 class="modal-title"><?php _e('Update Role For Your Account',ET_DOMAIN);?></h5>

				</div>
				<form id="fre_set_role">
				  	<div class="modal-body">

					    	<div class="form-group">

							    <select class="fre-select-role required " name="role" >
							     	<option selected disabled value=""><?php _e('Choose your role',ET_DOMAIN);?></option>
							      	<option value="<?php echo FREELANCER;?>"><?php _e('Freelancer',ET_DOMAIN);?></option>
							      	<option value="<?php echo EMPLOYER;?>"><?php _e('Employer',ET_DOMAIN);?></option>

							    </select>
							  </div>


					 </div>
				  	<div class="modal-footer">
				    	<button type="button" class="btn btn-secondary btn-close" data-dismiss="modal">Close</button>
				    	<button type="submit" class="fre-btn fre-btn primary-bg-color btn-save-role"><?php _e('Save Role',ET_DOMAIN);?></button>
				  </div>
			   </form>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$('#setRoleModal').modal('show');
				$('.fre-select-role').chosen({
					width: '100%',
		            inherit_select_classes: true,
		            disable_search:true,
		            disable_search_threshold: 3,
				});
			})
		})(jQuery);
	</script>
	<?php
}
function fre_set_role(){
	global $user_ID;
	$role = $_POST['role'];

	if(empty($role)){
		$resp = array(
			'success' => false,
			'msg' => __('Please select a role.',ET_DOMAIN),
		);
		wp_send_json($resp);

	}
	if( ! in_array( $role, array(FREELANCER, EMPLOYER) ) ){
		$role = FREELANCER;
	}

	$user_id = wp_update_user(array('ID' => $user_ID,'role' => $role ));
	$resp = array(
		'success' => true,
		'msg' => __('You have set role successful',ET_DOMAIN),
		'redirect_url' => et_get_page_link('profile'),
	);

	if(  ! is_wp_error($user_id) ){
		update_user_meta($user_ID, 'et_require_set_role', 0);
	} else {
		$resp['msg'] = __('Update Role Fail',ET_DOMAIN);
	}

	wp_send_json($resp);
}
add_action('wp_ajax_fre_set_role', 'fre_set_role');
?>