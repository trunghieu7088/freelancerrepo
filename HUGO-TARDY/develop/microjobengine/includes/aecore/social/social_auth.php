<?php
abstract class ET_SocialAuth extends AE_Base
{
    protected $social_option, $social_type, $auth_url, $labels;

    abstract protected function send_created_mail($user_id);

    protected $social_id = false;

    public function __construct($type, $social_option, $labels = array())
    {
        $this->social_type = $type;
        $this->social_option = $social_option;
        $this->auth_url = add_query_arg('type', $this->social_type, et_get_page_link('social-connect'));
        $this->labels   = $labels;
        $this->add_action('template_redirect', 'prepare_social_connect_page');
        $this->add_action('wp_enqueue_scripts', 'add_action_to_auth_script', 99);
        // $this->add_action('mje_after_google_cred_session_writing', 'add_action_to_auth_script');
        $this->add_ajax('et_authentication_' . $type, 'authenticate_user');
        $this->add_ajax('et_confirm_username_' . $type, 'confirm_username');
    }

    public function add_action_to_auth_script()
    {
        if (is_social_connect_page()) {
            if (isset($_REQUEST['type']) && $_REQUEST['type'] == $this->social_type) {
                wp_add_inline_script('et-authentication', 'const ae_auth = ' . json_encode(array(
                    'action_auth' => 'et_authentication_' . $_REQUEST['type'],
                    'action_confirm' => 'et_confirm_username_' . $_REQUEST['type']
                )), 'before');
            }
        }
    }

    public function prepare_social_connect_page()
    {
        $flag = is_social_connect_page();

        if ($flag && is_user_logged_in()) {
            wp_redirect(home_url());
            exit();
        }

        if ($flag) {
            global $et_data;
            if (isset($_GET['type']) && $_GET['type'] == $this->social_type) {
                $et_data['auth_labels'] = $this->labels;
            }
        }
    }

    protected function get_user($social_id)
    {
        $args = array(
            'meta_key' => $this->social_option,
            'meta_value' => trim($social_id),
            'number' => 1
        );
        $users = get_users($args);
        if (!empty($users) && is_array($users)) return $users[0];
        else return false;
    }
    protected function logged_user_in($social_id)
    {
        $ae_user = $this->get_user($social_id);
        if ($ae_user != false) {
            wp_set_auth_cookie($ae_user->ID);
            wp_set_current_user($ae_user->ID);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return WP_User|bool WP_User if logged user in successfully, false if failed
     */
    protected function logged_user_in_if_possible($social_id)
    {
        $args = array(
            'meta_key' => $this->social_option,
            'meta_value' => trim($social_id),
            'number' => 1
        );
        $users = get_users($args);
        if (!empty($users) && is_array($users)) {
            wp_set_auth_cookie($users[0]->ID);
            wp_set_current_user($users[0]->ID);
            return $users[0];
        } else {
            return false;
        }
    }

    protected function _create_user($params)
    {
        // insert user
        $ae_user = AE_Users::get_instance();
        $result = $ae_user->insert($params);
        if (!is_wp_error($result)) {
            // send email here
            $this->send_created_mail($result);
            // login
            $ae_user = wp_signon(array(
                'user_login' => $params['user_login'],
                'user_password' => $params['user_pass']
            ));
            if (is_wp_error($ae_user)) {
                return $ae_user;
            } else {
                // Authenticate successfully
                return true;
            }
        } else {
            return $result;
        }
    }
    protected function connect_user($email, $password)
    {
        if ($this->social_id != false) {
            // get user first
            $ae_user = get_user_by('email', $email);
            if ($ae_user == false) return new WP_Error('et_password_not_matched', __("Username and password does not matched", 'enginethemes'));
            // verify password
            if (wp_check_password($password, $ae_user->data->user_pass, $ae_user->ID)) {
                // connect user
                update_user_meta($ae_user->ID, $this->social_option, $this->social_id);
                return true;
            } else {
                return new WP_Error('et_password_not_matched', __("Username and password does not matched", 'enginethemes'));
            }
        } else {
            return new WP_Error('et_wrong_social_id', __("There is an error occurred", 'enginethemes'));
        }
    }
    protected function social_connect_success()
    {
        wp_redirect(home_url());
        exit;
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

                if (isset($auth_info['user_login'])) {
                    $auth_info['user_login'] = str_replace(' ', '', sanitize_user($auth_info['user_login']));
                    $ae_user = get_user_by('login', $auth_info['user_login']);
                    $ae_user = AE_Users::get_instance();
                    if (!$ae_user) {
                        $result = $ae_user->insert($auth_info);
                        if ($result == false || is_wp_error($result)) throw new Exception(__("Can not authenticate user", 'enginethemes'));
                        else if (empty($_SESSION['et_social_id'])) {
                            throw new Exception(__("Can't find Social ID", 'enginethemes'));
                        } else {
                            update_user_meta($result, $this->social_option, $_SESSION['et_social_id']);
                            do_action('et_after_register', $result);
                            wp_set_auth_cookie($result, 1);
                            unset($_SESSION['et_auth']);
                        }
                        $return = array(
                            'status' => 'linked',
                            'user' => $ae_user,
                            'redirect_url' => home_url()
                        );
                    } else {
                        $return = array(
                            'status' => 'wait'
                        );
                    }
                } else {
                    $return = array(
                        'status' => 'wait'
                    );
                }
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
                else if (empty($social_id)) {
                    throw new Exception(__("Can't find Social ID", 'enginethemes'));
                } else {
                    // creating user successfully
                    update_user_meta((int)$result->ID, $this->social_option, $social_id);
                    do_action('et_after_register', $result);
                    wp_set_auth_cookie((int)$result->ID, 1);
                    unset($_SESSION['et_auth']);
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
}
