<?php
// clienID: 375492077091-4ureejkh29etvm5to0mv1edu8mgc7h0b.apps.googleusercontent.com
//seceret key:gWt6RViHphfOIQT84_4B6ZIJ
// php api https://github.com/googleapis/google-api-php-client

// endpoit verify token: https://developers.google.com/identity/sign-in/web/backend-auth#calling-the-tokeninfo-endpoint

//https://developers.google.com/identity/sign-in/web/backend-auth#calling-the-tokeninfo-endpoint
define('GOOGLE_CLIENT_ID','375492077091-4ureejkh29etvm5to0mv1edu8mgc7h0b.apps.googleusercontent.com');
define('GOOGLE_SECRET_KEY','gWt6RViHphfOIQT84_4B6ZIJ');
class Google_Login{
	public $enable;
	public $client_id;
	public $secret_key;
	function __construct(){

		$this->enable = ae_get_option('gplus_login', false);
		$this->client_id = ae_get_option('gplus_client_id');
		$this->secret_key = ae_get_option('gplus_secret_id');
		$this->client_id = trim($this->client_id);
		$this->secret_key = trim($this->secret_key);

		add_action('wp_head', array($this, 'add_wp_head_script'));
		add_action('new_google_login_btn',array($this, 'add_btn_google_login'));
		add_action('wp_ajax_nopriv_verify_reponse', array($this,'verify_reponse'));

	}

	function is_enabling(){
		if(!$this->enable)
			return 0;
		if( empty($this->client_id) || empty($this->secret_key) )
			return 0;
		return 1;
	}
	function verify_reponse(){
		$id_token = $_REQUEST['id_token'];
		//https://oauth2.googleapis.com/tokeninfo?id_token=XYZ123

		$uri ="https://oauth2.googleapis.com/tokeninfo";
		$headers = array(
			'Host'=> 'oauth2.googleapis.com',
			'Content-Type' =>'application/x-www-form-urlencoded',
		);
		$args = array(
			'method' => 'GET',
			'body' => array(
			    'id_token' => $id_token
			),
			'headers' => $headers,
			'httpversion' => '1.1',
			'timeout'     => 120,
		);


		$remote_get = wp_remote_get( $uri, $args );
		$userinfor = json_decode( $remote_get ['body'] );

		$avatar_url = isset( $userinfor->picture ) ? $userinfor->picture : '';

		$google_id = $userinfor->sub;
		$user = get_social_user('et_google_id', $google_id);

		$msg = __('You have logged with google account',ET_DOMAIN);

		if( $user ){
			// log in here;
			wp_set_auth_cookie( $user->ID );
			wp_set_current_user( $user->ID );
			//wp_redirect( home_url() );

		    // update_user_meta($user_id, 'et_require_set_role', 1);
		} else {


			$user_login = sanitize_user($userinfor->name);
			$user_data = array(
			    'user_login' =>  $user_login,
			    'user_email' =>  $userinfor->email,
			    'user_nicename' =>  $userinfor->name,
			    'display_name'=> $userinfor->name,
			);
			$user_meta = array(
				'et_google_id' => $google_id,
				'et_avatar_url'  => $avatar_url,
				'type' => 'google',
			);

			$user = fre_insert_social_user($user_data, $user_meta);

			$resp = array('success' => true, 'msg' =>$msg, 'redirect_url' => et_get_page_link( "profile" ) );

			if( is_wp_error($user) ){
				$resp['success'] = false;
				$resp['msg'] = $user->get_error_message();
				unset($resp['user']);
				unset($resp['redirect_url']);
			}
			wp_send_json($resp);

		}
		$resp = array(
			'success' => true,
			'msg' => $msg,
			'redirect_url' => et_get_page_link( "profile" ),
		);
		wp_send_json($resp);

	}


	function add_btn_google_login(){
		if( $this->is_enabling() ){ ?>
			<li class="google" id="google">
				<span id="signInGoogleBtn" class="signInGoogleBtn sc-icon color-google">
					<i class="fa fa-google-plus-square"></i>
					<span class="social-text">Google</span>
				</span>
				<script>
					(function($) {
						$('.signInGoogleBtn').click(function(event) {
							var Views = window.AE.Views;
							var blockUi = new Views.BlockUi();
						    // signInCallback defined in step 6.
						   	// auth2.grantOfflineAccess().then(signInCallback);
						   	blockUi.block($(event.currentTarget));

						   	auth2.signIn().then( //GoogleAuth.then(onInit, onError)
						   	//https://developers.google.com/identity/sign-in/web/reference#googleauththenoninit_onerror
						   		function() {
						   			// onInit function

								    var user = auth2.currentUser.get();

								    var id_token = user.getAuthResponse().id_token;
								    var data = {
										id_token: id_token,
										action: "verify_reponse"
									};
								    jQuery.ajax({
											type: 'POST',
											dataType: "json",
											url: ae_globals.ajaxURL,

											success: function(result) {
												blockUi.unblock();
												if(result.success){
													// Handle or verify the server response.
													if(result.redirect_url){
														window.location.href = result.redirect_url;
													}
													var AA = window.AE;
													AA.pubsub.trigger('ae:notification', {
		                                                msg: result.msg,
		                                                notice_type: 'success',
		                                            });
												} else {
													var AA = window.AE;
													AA.pubsub.trigger('ae:notification', {
		                                                msg: result.msg,
		                                                notice_type: 'error',
		                                            });
												}
											},
											error: function(res){
												blockUi.unblock();
											},
											//processData: false,

											data: data,
										});

								},
								function (res){
									//onError

									$(".loading-blur").remove();
								}
							);
						});
				   })(jQuery);
				</script>
			</li>
			<?php
		}

	}
	function add_wp_head_script(){
		if( ! $this->is_enabling() )
			return;

		?>
		<!--
		<meta name="google-signin-scope" content="profile email">
	    <meta name="google-signin-client_id" content="YOUR_CLIENT_ID.apps.googleusercontent.com">
	    <script src="https://apis.google.com/js/platform.js" async defer></script>
	    !-->
	    <script src="https://apis.google.com/js/client:platform.js?onload=start" async defer></script>
	    <script>

		    function start() {

		      	gapi.load('auth2', function() {
		        	auth2 = gapi.auth2.init({
		          	client_id: '<?php echo $this->client_id;?>',
		         	redirect_uri:'postmessage',
		          // Scopes to request in addition to 'profile' and 'email'
		          //scope: 'additional_scope'
		        });

		      });
		    }

		    function signInCallback(authResult) {
		    	console.log('authResult');
		    	console.log(authResult);
				if (authResult['code']) {

					// Hide the sign-in button now that the user is authorized, for example:
					//$('#signInGoogleBtn').attr('style', 'display: none');

					// Send the code to the server

					var data = {
						code: authResult['code'],

						action: "verify_reponse"
					};

					jQuery.ajax({
						type: 'POST',
						dataType: "json",
						url: ae_globals.ajaxURL,
						// Always include an `X-Requested-With` header in every AJAX request,
						// to protect against CSRF attacks.
						// headers: {
						// 'X-Requested-With': 'XMLHttpRequest'
						// },
						//contentType: 'application/octet-stream; charset=utf-8',
						success: function(result) {
							console.log(result);
							// Handle or verify the server response.
						},
						error: function(res){
							console.log(res);
						},
						//processData: false,

						data: data,
					});
				} else {
					console.log('empty code');
					// There was an error.
				}
			}

		  </script>
		<?php
	}
}
new Google_Login();
/**
	keep to undestand how wp_remote_ query correct
	*/
	function verify_reponse_old(){

		$code = $_REQUEST['code'];
		$resp = array(
			'success' => true,
			'msg' =>'OK'
		);

		// $this->gplus_exchange_url = 'https://www.googleapis.com/oauth2/v3/token';

		$url = "https://oauth2.googleapis.com/token?code=$code&
		client_id=your_client_id&
		client_secret=your_client_secret&
		redirect_uri=https%3A//oauth2.example.com/code&
		grant_type=authorization_code";

		$redirect_uri = home_url('?action=gplus_auth_callback');
		//https://lab.enginethemes.com/qae/?action=gplus_auth_callback
		//https://lab.enginethemes.com/qae/?action=gplus_auth_callback"
		$redirect_uri = esc_url('https://lab.enginethemes.com/qae/?action=gplus_auth_callback');

		$headers = array(
			'Host'=> 'oauth2.googleapis.com',
			'Content-Type' =>'application/x-www-form-urlencoded',
		);

		$args = array(
			'method' => 'POST',
			'body' => array(
			    'grant_type' => 'authorization_code',//refresh_token, authorization_code
			    'code' => $_REQUEST['code'],
			    //'redirect_uri' =>  $redirect_uri,
			    'redirect_uri' => 'postmessage',
			    'client_id' => GOOGLE_CLIENT_ID,
			    'client_secret' => GOOGLE_SECRET_KEY,
			    'scope' => 'profile https://www.googleapis.com/auth/drive.metadata.readonly',

			),
			'headers' => $headers,
			'httpversion' => '1.1',
			'timeout'     => 120,
		);
		$uri = "https://oauth2.googleapis.com/token";



		$remote_post = wp_remote_post( $uri, $args ); // same same et_google_auth --- auth_google
		//https://developers.google.com/identity/protocols/oauth2/web-server#httprest_3
		$data = json_decode( $remote_post ['body'] );
		//var_dump($remote_post);

		$access_token = $data->access_token;
		$uri = "https://www.googleapis.com/drive/v2/files";


		$headers = array(
			'Authorization' => "Bearer ".$access_token,

		);


		$args = array(
			'method' => 'GET',
			'body' => array(
			    'access_token' => $access_token,


			),
			'headers' => $headers,
			'httpversion' => '1.1',
			'timeout'     => 120,
		);


		$remote_get = wp_remote_get( $uri, $args );

		$data = json_decode( $remote_get ['body'] );
		//https://oauth2.googleapis.com/tokeninfo?id_token=XYZ123

		var_dump($data);

		//header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));

		wp_send_json($resp);
	}