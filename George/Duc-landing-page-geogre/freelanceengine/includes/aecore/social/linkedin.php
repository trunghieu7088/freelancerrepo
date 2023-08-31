<?php

/**
 *   Linkedin authication
 * @author Quang Ðat
 */
// https://docs.microsoft.com/en-us/linkedin/shared/authentication/authorization-code-flow
// https://developer.linkedin.com/docs/v2/oauth2-client-credentials-flow
// https://medium.com/@ellesmuse/how-to-get-a-linkedin-access-token-a53f9b62f0ce
class ET_LinkedInAuth extends ET_SocialAuth {
	private $state;
	private $linkedin_secret_key;
	protected $linkedin_api_key;
	protected $linkedin_base_url;
	protected $linkedin_token_url;
	protected $linkedin_people_url;

	public function __construct() {
		parent::__construct( 'linkedin', 'et_linkedin_id', array(
			'title' => __( 'Sign Up With Linkedin', ET_DOMAIN ),
		) );
		$this->state = md5( uniqid() );
		$this->add_ajax( 'ae_linked_auth', 'lkin_redirect' );
		add_action( 'parse_request', array($this, 'validate_linkedin_api' ), 999 );
		$this->linkedin_api_key    = ae_get_option( 'linkedin_api_key' );
		$this->linkedin_secret_key = ae_get_option( 'linkedin_secret_key' );

		// $this->linkedin_base_url   = 'https://www.linkedin.com/uas/oauth2/authorization';
		// $this->linkedin_token_url  = 'https://www.linkedin.com/uas/oauth2/accessToken';

		$this->linkedin_base_url 	= 'https://www.linkedin.com/oauth/v2/authorization';
		$this->linkedin_token_url 	= 'https://www.linkedin.com/oauth/v2/accessToken';



		$this->linkedin_people_url = 'https://api.linkedin.com/v2/people/(id:{profile ID})?projection=(id,firstName,lastName)';
		//$this->linkedin_people_url = $this->linkedin_url.'/~:(id,location,picture-url,specialties,public-profile-url,email-address,formatted-name)?format=json';

	}
	function validate_linkedin_api(){
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'linked_auth_callback' ) {
			if ( ! empty( $this->linkedin_api_key ) && ! empty( $this->linkedin_secret_key ) && ! is_user_logged_in() ) {
				et_log('Call method: linked_auth(insert user)');
				$this->linked_auth();
			} else {
				_e( 'Please enter your Linkedin App id and secret key!', ET_DOMAIN );
				exit();
			}
		}
	}


	// implement abstract method
	protected function send_created_mail( $user_id ) {
		do_action( 'et_after_register', $user_id );
	}

	/**
	 * When user click login button Linkedin, it will execution function bellow
	 * @return $link
	 */
	public function lkin_redirect() {
		try {
			// turn on session
			if ( ! isset( $_SESSION ) ) {
				ob_start();
				@session_start();
			}
			/**
			 * Step1: Request an Authorization Code
			 */
			$redirect_uri = home_url( '?action=linked_auth_callback' );
			$link         = $this->linkedin_base_url . '?';
			$link         .= 'response_type=code&';
			$link         .= 'client_id=' . $this->linkedin_api_key . '&';
			$link         .= 'redirect_uri=' . $redirect_uri . '&';
			$link         .= 'state=' . $this->state . '&';
			//$link         .= 'scope=r_basicprofile r_emailaddress';
			$link         .= 'scope=r_liteprofile%20r_emailaddress%20w_member_social';
			//$link 		  .='r_liteprofile%20r_emailaddress%20w_member_social';
			// wp_set_auth_cookie($link);
			$resp = array(
				'success'  => true,
				'msg'      => 'Success',
				'redirect' => $link
			);
		} catch ( Exception $e ) {
			$resp = array(
				'success' => false,
				'msg'     => $e->getMessage()
			);

		}
		wp_send_json( $resp );
	}

	/**
	 * function handle after linkedin callback
	 */
	public function linked_auth() {
		if ( ( isset( $_REQUEST['code'] ) && ! empty( $_REQUEST['code'] ) ) && ( isset( $_REQUEST['state'] ) || $_REQUEST['state'] == $this->state ) ) {
			try {
				/**
				 * Step2: Exchange Authorization Code for a Request Token
				 */
				$request      = $_REQUEST;
				$redirect_uri = home_url( '?action=linked_auth_callback' );
				$args         = array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'body'        => array(
						'grant_type'    => 'authorization_code',
						'code'          => $request['code'],
						'redirect_uri'  => $redirect_uri,
						'client_id'     => $this->linkedin_api_key,
						'client_secret' => $this->linkedin_secret_key
					),
					'cookies'     => array()
				);

				$remote_post  = wp_remote_post( $this->linkedin_token_url, $args );

				if ( isset( $remote_post ['body'] ) && ! empty( $remote_post ['body'] ) ) {
					$data = json_decode( $remote_post ['body'] );

				} else {
					_e( 'Error to connect to Linkedin server!', ET_DOMAIN );
					exit();
				}
				if ( ! isset( $data->access_token ) || empty( $data->access_token ) ) {
					_e( 'Can not get the access token from Linkedin server!', ET_DOMAIN );
					exit();
				}
				/**
				 * Step3: Make authenticated requests and get user's informations
				 */
				$args1      = array(
					'timeout'     => 120,
					'httpversion' => '1.1',
					'headers'     => array(
						'Authorization' => 'Bearer ' . $data->access_token
					)
				);
				$api_link = "https://api.linkedin.com/v2/me";

				$remote_get = wp_remote_get( $api_link, $args1 );


				$ch = curl_init('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))&oauth2_access_token=' .$data->access_token);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);
				$email_res = curl_exec($ch);
				curl_close($ch);

				$email_obj  = json_decode( $email_res );

				$item 		= $email_obj->elements[0];
				$user_email = '';
				foreach($item as $key=>$node){
					if($key == 'handle~'){
						$user_email = $node->emailAddress ;
					}
				}

				$remote_get = wp_remote_get( $api_link, $args1 );

				if ( isset( $remote_get['body'] ) && ! empty( $remote_get['body'] ) ) {
					$data_user = json_decode( $remote_get['body'] );
				} else {
					_e( 'Error to connect to Linkedin server2!', ET_DOMAIN );
					exit();
				}
				if ( ! isset( $data_user->id ) || empty( $data_user->id ) ) {
					_e( 'Can not get user information from Linkedin server!', ET_DOMAIN );
					exit();
				}

				//et_log('data_user:');
				//et_log($data_user);

				// if user is already authenticated before
				if ( $this->get_user( $data_user->id ) ) {

					et_log('User exits. Auto login.');
					$user     = $this->get_user( $data_user->id );
					$result   = $this->logged_user_in( $data_user->id );
					$ae_user  = AE_Users::get_instance();
					$userdata = $ae_user->convert( $user );
					$nonce    = array(
						'reply_thread' => wp_create_nonce( 'insert_reply' ),
						'upload_img'   => wp_create_nonce( 'et_upload_images' ),
					);
				} else {
					et_log('Fist login => Insert new user.');
					// avatar
					$ava_response = isset( $data_user->pictureUrl ) ? $data_user->pictureUrl : '';
					$sizes        = get_intermediate_image_sizes();
					$avatars      = array();
					if ( $ava_response ) {
						foreach ( $sizes as $size ) {
							$avatars[ $size ] = array(
								$ava_response
							);
						}
					} else {
						$avatars = false;
					}
					$data_user->formattedName = str_replace( ' ', '', sanitize_user( $data_user->formattedName ) );
					$username                 = $data_user->formattedName;
					$params                   = array(
						'user_login' => strtolower($username),
						'user_email' => isset( $data_user->emailAddress ) ? $data_user->emailAddress : false,
						'et_avatar'  => $avatars
					);
					//remove avatar if cant fetch avatar
					foreach ( $params as $key => $param ) {
						if ( $param == false ) {
							unset( $params[ $key ] );
						}
					}
					// @since 1.8.14
					if( NEW_SOCIAL ){ // auto insert new wp_user
						$user_login = $data_user->localizedFirstName.$data_user->localizedLastName;

						$user_data  = array(
							'user_login' => strtolower($user_login),
							'user_email' => $user_email,
						);

						$user_meta = array(
							'et_linkedin_id' => $data_user->id,
							'et_avatar' => $avatars,
							'et_avatar_url' => $avatars,

						);
						$user = fre_insert_social_user($user_data, $user_meta);

						$resp = array('success' => true,'data' => array('redirect_url' => et_get_page_link( "profile" )), 'user' => array() );
						if( is_wp_error($user) ){
							et_log('is_wp_error(user) :');
							//et_log($user);
							$resp['success'] = false;
							$resp['msg'] = $user->get_error_message();
							unset($resp['user']);
							unset($resp['redirect_url']);
							wp_send_json($resp);
						}
						et_log('Redirect to prodile and exit 1.');
						header( 'Location: ' . et_get_page_link( "profile" ) );
						exit();



					}
					// END NEW_SOCIAL

					if ( ! isset( $_SESSION ) ) {
						ob_start();
						@session_start();
					}
					/**
					 * set value into session for save later
					 *
					 */
					$_SESSION['et_auth']      = serialize( $params );
					$_SESSION['et_social_id'] = $data_user->id;
					$_SESSION['et_auth_type'] = 'linkedin';

					et_write_session( 'et_auth', serialize( $params ) );
					et_write_session( 'et_social_id', $data_user->id );
					et_write_session( 'et_auth_type', 'linkedin' );

				}
				et_log('Redirect to prodile and exit 2.');
				header( 'Location: ' . get_post_type_archive_link('project') );
				wp_redirect(get_post_type_archive_link('project'));
				exit();

			} catch ( Exception $e ) {
				_e( 'Error to connect to Linkedin server', ET_DOMAIN );
				exit();
			}
		}
	}
}