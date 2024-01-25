<?php
class mJobUserAction extends AE_UserAction
{
    public static $instance;
    public $mail;

    /**
     * Get instance method
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor of class
     */
    public function __construct()
    {
        $user = new mJobUser();
        parent::__construct($user);
        $this->mail = AE_Mailing::get_instance();

        // Add ajax
        $this->add_ajax('mjob_sync_user', 'mJobUserSync');

        // Filter result when register new user
        $this->add_filter('ae_after_insert_user', 'mJobFilterRegisterUser');
        $this->add_filter('ae_after_login_user', 'mJobFilterSignInUser');
        $this->add_filter('ae_reset_pass_response', 'mJobFilterResetPassword');
        $this->add_filter('ae_convert_user', 'mJobFilterUser');
        $this->add_filter('ae_confirm_user_time_out', 'mJobConfirmUser');
        $this->add_filter('ae_social_auth_info', 'mJobSocialAuthInfo', 10, 2);

        // User action
        $this->add_action('ae_insert_user', 'mJobAfterRegisterUser', 10, 2);
        $this->add_action('ae_user_forgot', 'mJobAfterForgotPassword', 10, 2);
        $this->add_action('ae_user_reset_pass', 'mJobAfterResetPassword', 10, 1);

        // Add scripts
        $this->add_action('wp_enqueue_scripts', 'mJobAuthScripts');

        if (!is_user_logged_in()) {
            // Add template
            $this->add_action('wp_footer', 'mJobAddModalSignIn');
            $this->add_action('wp_footer', 'mJobAddModalSignUpStepOne');
            $this->add_action('wp_footer', 'mJobAddModalSignUp');
            $this->add_action('wp_footer', 'mJobAddModalForgotPassword');
        }
    }

    /**
     * User sync
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobUserSync()
    {
        global $current_user;

        // Check active user
        if (!mje_is_user_active($current_user->ID)) {
            wp_send_json(array(
                'success' => false,
                'msg' => __('Your account is pending. You have to activate your account to continue this step.', 'enginethemes')
            ));
        }

        $request = $_REQUEST;
        $result = array();
        if (isset($request['do_action']) && !empty($request['do_action'])) {
            switch ($request['do_action']) {
                case 'check_email':
                    $result = $this->validateEmail($request['user_email']);
                    wp_send_json($result);
                    break;

                case 'update_payment_method':
                    $payment_info = get_user_meta($current_user->ID, 'payment_info', true);
                    if (empty($payment_info)) {
                        $payment_info = array();
                    }

                    if (isset($request['paypal_email'])) {
                        //$payment_info['paypal'] = strip_tags($request['paypal_email']);
                        //custom code Avi Paypal info
                        $payment_info['paypal'] = array(
                            'paypal_first_name' => strip_tags($request['paypal_first_name']),
                            'paypal_middle_name' => strip_tags($request['paypal_middle_name']),
                            'paypal_last_name' => strip_tags($request['paypal_last_name']), 
                            'paypal_custom_address'  => strip_tags($request['paypal_custom_address']),                           
                            'paypal_custom_tel'  => strip_tags($request['paypal_custom_tel']),                           
                            'paypal_email' => strip_tags($request['paypal_email']),
                        );
                        //end
                        update_user_meta($current_user->ID, 'payment_info', $payment_info);

                    } else if (isset($request['bank_account_no'])) {
                        $payment_info['bank'] = array(
                            'first_name' => strip_tags($request['bank_first_name']),
                            'middle_name' => strip_tags($request['bank_middle_name']),
                            'last_name' => strip_tags($request['bank_last_name']),
                            'name' => strip_tags($request['bank_name']),
                            'swift_code' => strip_tags($request['bank_swift_code']),
                            'account_no' => strip_tags($request['bank_account_no'])
                        );
                        update_user_meta($current_user->ID, 'payment_info', $payment_info);
                    }
                    break;
            }
        }

        parent::sync();
    }

    /**
     * Validate email: empty, format, exist
     * @param string $email
     * @return array
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author Tat Thien
     */
    public function validateEmail($email)
    {
        // Check email empty
        if (empty($email)) {
            return array(
                'success' => false,
                'msg' => __('Email field is empty.', 'enginethemes')
            );
        }
        // Check email valid
        if (!is_email($email)) {
            return array(
                'success' => false,
                'msg' => __('Email field is invalid.', 'enginethemes')
            );
        }
        // Check email exist
        if (email_exists($email)) {
            return array(
                'success' => false,
                'msg' => __('This email is already used on this site. Please enter a new email.', 'enginethemes')
            );
        }
        return array(
            'success' => true
        );
    }

    /**
     * Filter response value when register new user
     * @param object $result
     * @return object $result
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobFilterRegisterUser($result)
    {
        return $result;
    }

    public function mJobFilterSignInUser($result)
    {
        return $result;
    }

    /**
     * Filter response value after reset password
     * @param array $result
     * @return array $result
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobFilterResetPassword($result)
    {
        $result['redirect_url'] = et_get_page_link('sign-in');
        return $result;
    }

    public function mJobFilterUser($result)
    {
        $result->avatar = mje_avatar($result->ID, 35);
        $result->payment_info = get_user_meta($result->ID, 'payment_info', true);
        $timezone = isset($result->timezone_local) ? $result->timezone_local : '';
        // Setting user_data timezone
        $result->timezone_local_edit = mje_get_timezone($timezone);
        $result->timezone_local = mje_get_timezone($timezone, true);

        return $result;
    }

    /**
     * Update user key confirm and send register email
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAfterRegisterUser($result, $user_data)
    {
        if (isset($user_data['timezone_local'])) {
            update_user_meta($result, 'timezone_local', $user_data['timezone_local']);
        }
        // add key confirm for user
        if (ae_get_option('user_confirm')) {
            update_user_meta($result, 'register_status', 'unconfirm');
            update_user_meta($result, 'key_confirm', wp_hash(md5($user_data['user_email'] . time())));
        }

        // send email registration to user
        $this->mail->register_mail($result);


        // A new user registration notification is sent to admin email.
        wp_new_user_notification($result);
    }

    /**
     * Send link reset password
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAfterForgotPassword($result, $key)
    {
        $this->mail->forgot_mail($result, $key);
    }

    /**
     * Send email after reset password successfully
     * @param int $user_id
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAfterResetPassword($user_id)
    {
        $this->mail->resetpass_mail($user_id);
    }

    /**
     * Add scripts for authentication
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAuthScripts()
    {
        wp_enqueue_script('mjob-auth', get_template_directory_uri() . '/includes/modules/AE_Authentication/js/mjob-auth.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front'
        ), ET_VERSION, true);
    }

    /**
     * Add modal sign in into footer
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAddModalSignIn()
    {
        mJobModalSignIn();
    }

    /**
     * Add modal sign up step one into footer
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAddModalSignUpStepOne()
    {
        $default_intro = "<p><strong>Welcome to MicrojobEngine!</strong></p><p>If you have amazing skills, we have amazing mJobs. MicrojobEngine has opportunities for all types of fun. Let's turn your little hobby into Big Bucks.</p>";

        $intro = ae_get_option("sign_up_intro_text", $default_intro);

        mJobModalSignUpStepOne($intro);
    }

    /**
     * Add modal sign up into footer
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAddModalSignUp()
    {
        mJobModalSignUp();
    }

    /**
     * Add modal forgot password into footer
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobAddModalForgotPassword()
    {
        mJobModalForgotPassword();
    }

    /**
     * Set time out for confirm user
     * @param int $time     mili second
     * @return int $time
     * @since 1.0
     * @package MicrojobEngine
     * @category Authentication
     * @author Tat Thien
     */
    public function mJobConfirmUser($time)
    {
        $time = 4000;
        return $time;
    }

    /**
     * Get user payment information
     * @param int $user_id
     * @return array $payment_info
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author Tat Thien
     */
    public function mJobGetPaymentInfo($user_id)
    {
        $payment_info = get_user_meta($user_id, 'payment_info', true);
        return $payment_info;
    }

    /**
     * Check user payment info
     * @param int $user_id
     * @return boolean
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author Tat Thien
     */
    public function mJobCheckPaymentInfo($user_id, $account_type)
    {
        $payment_info = $this->mJobGetPaymentInfo($user_id);
        if (empty($payment_info)) {
            return false;
        } else if ($account_type == 'paypal' && (!isset($payment_info['paypal']) || empty($payment_info['paypal']))) {
            return false;
        } else if ($account_type == 'bank' && (!isset($payment_info['bank']) || empty($payment_info['bank']))) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author Dang Bui
     */
    public function mJobSocialAuthInfo($auth_info, $data)
    {
        if (isset($data['timezone_local']))
            $auth_info['timezone_local'] = $data['timezone_local'];
        return $auth_info;
    }
}

$new_instance = mJobUserAction::getInstance();

if (!function_exists('mJobUserAction')) {
    function mJobUserAction()
    {
        return mJobUserAction::getInstance();
    }
}
