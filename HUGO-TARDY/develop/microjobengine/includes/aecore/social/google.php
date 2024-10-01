<?php
class ET_GoogleAuth extends ET_SocialAuth
{
    private $isReady;
    private $gplus_secret_key;
    protected $gplus_client_id;
    protected $gplus_token_info_host;
    protected $gplus_token_info_url;

    public function __construct()
    {
        parent::__construct('google', 'et_google_id', array(
            'title' => __('SIGN IN WITH GOOGLE', 'enginethemes'),
            'content' => __("<h3>Welcome! This seems to be your first time signing in using your Google account.</h3> <p>If you already have an account with %s, use the form below to link it.</p> <p>New user? Enter your email, password, and choose a username on the next page to create your account (one-time setup!).</p><p>Next time, you'll be logged in with Google in a flash!</p>", 'enginethemes'),
            'content_confirm' => __("Please provide a username to continue", 'enginethemes')
        ));

        add_action('wp_head', array($this, 'add_wp_head_google_login_script'), 10, 0);
        $this->add_action('google_login_btn', 'google_login_btn');
        $this->add_action('handle_google_cred_after_login', 'handle_google_cred_after_login');

        $this->isReady = ae_get_option('gplus_login', false);
        $this->gplus_client_id = ae_get_option('gplus_client_id', '');
        $this->gplus_secret_key = ae_get_option('gplus_secret_id', '');

        $this->gplus_token_info_host = 'oauth2.googleapis.com';
        $this->gplus_token_info_url = 'https://oauth2.googleapis.com/tokeninfo';
    }

    public function add_wp_head_google_login_script()
    {
        if (!$this->isGoogleReady())
            return;
?>
        <script src="https://accounts.google.com/gsi/client?hl=<?php echo get_locale(); ?>" async></script>

        <div id="g_id_onload" data-client_id="<?php echo $this->gplus_client_id; ?>" data-context="signin" data-ux_mode="popup" data-login_uri="<?php echo $this->auth_url; ?>" data-auto_prompt="false" data-use_fedcm_for_prompt="true">
        </div>
        <?php
    }

    public function isGoogleReady()
    {
        return ($this->isReady && !empty($this->gplus_client_id) && !empty($this->gplus_secret_key));
    }

    public function google_login_btn($data_type = "icon")
    {
        if ($this->isGoogleReady()) {
            if ("icon" == $data_type) {
        ?>
                <li>
                    <div class="g_id_signin" data-locale="<?php echo get_locale(); ?>" data-type="icon" data-shape="square" data-theme="filled_blue" data-text="signin_with" data-size="medium" data-width="32"></div>
                    <!-- <a href="#" class="gplus gplus_login_btn ">
                    </a> -->
                </li>
            <?php
            } elseif ("standard" == $data_type) {
            ?>
                <li>
                    <div class="g_id_signin" data-locale="<?php echo get_locale(); ?>" data-type="standard" data-shape="rectangular" data-theme="outline" data-text="signin_with" data-size="medium" data-logo_alignment="left">
                    </div>
                </li>
<?php
            } else return;
        }
    }

    public function handle_google_cred_after_login($cred)
    {
        global $et_data;
        try {
            $args = array(
                'method' => 'GET',
                'body' => array(
                    'id_token' => $cred
                ),
                'headers' => array(
                    'Host' => $this->gplus_token_info_host,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'httpversion' => '1.1',
                'timeout'     => 120,
            );

            $remote_get = wp_remote_get($this->gplus_token_info_url, $args);
            $userinfo = json_decode($remote_get['body']);

            if (!isset($userinfo->sub) ||  empty($userinfo->sub)) {
                $et_data['error'] = $userinfo->error;
                return;
            } else {
                return $this->process_google_acc_info($userinfo);
            }
        } catch (Exception $e) {
            $et_data['error'] = $e->getMessage();
            return new WP_Error('google_login_error', $e->getMessage());
        }
    }

    public function process_google_acc_info($userinfo)
    {
        $user = $this->logged_user_in_if_possible($userinfo->sub);
        // if user is already authenticated before
        if (false !== $user) {
            $redirect_url = apply_filters('ae_social_redirect_link', home_url());
            header('Location: ' . $redirect_url);
            exit();
        } else {

            // turn on session
            if (!isset($_SESSION)) {
                ob_start();
                @session_start();
            }

            /**
             * Hook action connect social
             * @since MicrojobEngine 1.0
             */
            do_action('ae_google_connect_social', $userinfo->sub);

            // avatar
            $ava_response = isset($userinfo->picture) ? $userinfo->picture : '';
            $sizes = get_intermediate_image_sizes();
            $avatars = array();
            if ($ava_response) {
                foreach ($sizes as $size) {
                    $avatars[$size] = array(
                        $ava_response
                    );
                }
            } else {
                $avatars = false;
            }

            $username = str_replace(' ', '', sanitize_user($userinfo->name));
            $params = array(
                'user_login' => $username,
                'user_email' => isset($userinfo->email) ? $userinfo->email : false,
                'et_avatar' => $avatars
            );
            //remove avatar if cant fetch avatar
            foreach ($params as $key => $param) {
                if ($param == false) {
                    unset($params[$key]);
                }
            }
            $_SESSION['et_auth'] = serialize($params);
            $_SESSION['et_social_id'] = $userinfo->sub;
            $_SESSION['et_auth_type'] = 'google';

            et_write_session('et_auth', serialize($params));
            et_write_session('et_social_id', $userinfo->sub);
            et_write_session('et_auth_type', 'google');

            do_action('mje_after_google_cred_session_writing');
        }
        return true;
    }

    public function authenticate_user()
    {
        try {
            $et_session = et_read_session();
            // turn on session
            if (!isset($_SESSION)) {
                ob_start();
                @session_start();
            }
            $data = $_POST['content'];
            // find user first
            if (empty($data['user_email']) || empty($data['user_pass']))
                throw new Exception(__('Login information is missing', 'enginethemes'));
            if (filter_var($data['user_email'], FILTER_VALIDATE_EMAIL) === false)
                throw new Exception(__('Please provide a valid email', 'enginethemes'));
            $email = $data['user_email'];
            $pass = $data['user_pass'];
            $ae_user = get_user_by('email', $email);
            $return = array();
            // if user doesn't exist, create one
            if ($ae_user == false) {

                // save to session, waiting for username input


                if (isset($_SESSION['et_auth'])) $auth_info = unserialize($_SESSION['et_auth']);
                else $auth_info = array();
                $auth_info = wp_parse_args(array(
                    'user_email' => $email,
                    'user_pass' => $pass
                ), $auth_info);
                $_SESSION['et_auth'] = serialize($auth_info);

                // if (isset($auth_info['user_login'])) {
                //     $auth_info['user_login'] = str_replace(' ', '', sanitize_user($auth_info['user_login']));
                //     $ae_user = get_user_by('login', $auth_info['user_login']);
                //     $ae_user = AE_Users::get_instance();
                //     if (!$ae_user) {
                //         $result = $ae_user->insert($auth_info);
                //         if ($result == false || is_wp_error($result)) throw new Exception(__("Can not authenticate user", 'enginethemes'));
                //         else if (empty($_SESSION['et_social_id'])) {
                //             throw new Exception(__("Can't find Social ID", 'enginethemes'));
                //         } else {
                //             update_user_meta($result, $this->social_option, $_SESSION['et_social_id']);
                //             do_action('et_after_register', $result);
                //             wp_set_auth_cookie($result, 1);
                //             unset($_SESSION['et_auth']);
                //         }
                //         $return = array(
                //             'status' => 'linked',
                //             'user' => $ae_user,
                //             'redirect_url' => home_url()
                //         );
                //     } else {
                //         $return = array(
                //             'status' => 'wait'
                //         );
                //     }
                // } else {
                $return = array(
                    'status' => 'wait'
                );
                // }
            }
            // if user does exist, connect them
            else {
                // khi ti`m thay user bang email, kiem tra password
                // neu password dung thi dang nhap luon
                if (wp_check_password($pass, $ae_user->data->user_pass, $ae_user->ID)) {
                    // connect user
                    update_user_meta($ae_user->ID, $this->social_option, $_SESSION['et_social_id']);
                    //
                    wp_set_auth_cookie($ae_user->ID, 1);
                    unset($_SESSION['et_auth']);
                    et_destroy_session();
                    $return = array(
                        'status' => 'linked',
                        'user' => $ae_user,
                        'redirect_url' => apply_filters('ae_social_redirect_link', home_url())
                    );
                } else {
                    throw new Exception(__("This email address is already existed. If you are the owner, please enter the correct password", 'enginethemes'));
                }
            }
            $resp = array(
                'success' => true,
                'msg' => __("You have signed in successfully!", 'enginethemes'),
                'data' => $return
            );
        } catch (Exception $e) {
            $resp = array(
                'success' => false,
                'msg' => $e->getMessage()
            );
        }
        wp_send_json($resp);
    }

    public function confirm_username()
    {
        try {
            $et_session = et_read_session();

            if (!isset($_SESSION)) {
                ob_start();
                @session_start();
            }
            // get data
            $data = $_POST['content'];
            $auth_info = unserialize($_SESSION['et_auth']);

            //Add $data post content in auth info
            $auth_info = apply_filters('ae_social_auth_info', $auth_info, $data);

            $username = $data['user_login'];
            if (isset($data['user_role']) && $data['user_role'] != '') {
                $user_roles = ae_get_option('social_user_role', false);
                if (!$user_roles) {
                    $user_roles = ae_get_social_login_user_roles_default();
                }
                if ($user_roles && in_array($data['user_role'], $user_roles) && $data['user_role'] != 'Administrator') {
                    $auth_info['role'] = $data['user_role'];
                }
            }
            if ($et_session['et_social_id']) {
                $social_id = $et_session['et_social_id'];
            } else {
                $social_id = $_SESSION['et_social_id'];
            }
            // verify username
            $ae_user = get_user_by('login', $username);
            $return = array();
            if ($ae_user != false) throw new Exception(__('Username is existed, please choose another one', 'enginethemes'));
            else {
                $auth_info['user_login'] = $username;
                // create user
                $ae_user = AE_Users::get_instance();
                //$result = $ae_user->insert($auth_info);

                if (!isset($auth_info['user_pass'])) {
                    $auth_info['user_pass'] = $_POST['user_pass'];
                }
                $result = $ae_user->insert_social_user($auth_info); // add @since in version 1.3.5.1
                if (is_wp_error($result)) throw new Exception($result->get_error_message());
                else {
                    if (!empty($social_id)) {
                        // creating user successfully
                        update_user_meta((int)$result->ID, $this->social_option, $social_id);
                    }
                    do_action('et_after_register', $result);
                    wp_set_auth_cookie((int)$result->ID, 1);
                    unset($_SESSION['et_auth']);
                    et_destroy_session();
                    $return = array(
                        'user_id' => $result,
                        'redirect_url' => apply_filters('ae_social_redirect_link', home_url())
                    );

                    $resp = array(
                        'success' => true,
                        'msg' => $result->msg,
                        'data' => $return
                    );
                }
            }
        } catch (Exception $e) {
            $resp = array(
                'success' => false,
                'msg' => $e->getMessage()
            );
        }
        wp_send_json($resp);
    }

    // implement abstract method
    protected function send_created_mail($user_id)
    {
        do_action('et_after_register', $user_id);
    }

    /**
     * @deprecated
     * @since v1.5
     */
    public function ae_gplus_redirect()
    {
        exit();
    }

    /**
     * @deprecated
     * @since v1.5
     */
    public function auth_google()
    {
        exit();
    }
}
