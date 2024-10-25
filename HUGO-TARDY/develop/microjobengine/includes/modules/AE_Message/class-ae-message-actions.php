<?php

/**
 * Private message action class
 */
class AE_Private_Message_Actions extends MJE_Post_Action
{
    public static $instance;
    /**
     * get_instance method
     *
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * The constructor
     *
     * @param void
     * @return void
     * @since 1.0
     * @author Tambh
     */
    public function __construct($post_type = 'ae_message')
    {
        $this->post_type = 'ae_message';
        parent::__construct($post_type);
        $this->ruler = array(
            'post_content' => 'required',
        );
    }
    /**
     * Init for class AE_Private_Message_Actions
     * @param void
     * @return void
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function init()
    {
        $this->add_ajax('ae-fetch-ae_message', 'fetch_post');
        $this->add_ajax('ae-ae_message-sync', 'syncMessage');
        $this->add_action('wp_footer', 'ae_message_add_template');
        $this->add_action('wp_enqueue_scripts', 'aeMessageScript');
        $this->add_ajax('ae-fetch-ae_custom_post', 'fetch_post');

        // moved this func body to mje-conversation-action convert
        // $this->add_filter('ae_convert_ae_message', 'convert');
    }
    /**
     * enqueue script for ae message
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function aeMessageScript()
    {
        wp_enqueue_script('ae-message-js', get_template_directory_uri() . '/includes/modules/AE_Message/js/ae_message.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front'
        ), 1.0, true);
    }
    /**
     * Add private message modal
     * @param void
     * @return void
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public  function ae_message_add_template()
    {
    }
    /**
     * Sync private message data
     * @param void
     * @return void
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function syncMessage()
    {
        $request = $_REQUEST;

        if (isset($request['conversation_content'])) {
            $request['post_content'] = $request['conversation_content'];
        };

        do_action('ae_message_validate_before_sync', $request);

        $validateRequest = $this->validatePost($request);
        if (!$validateRequest['success']) {
            wp_send_json($validateRequest);
        }

        $request['post_status'] = 'publish';

        if (!isset($request['post_title']) || empty($request['post_title'])) {
            $request['post_title'] = __('Message for: ', 'enginethemes');
            if (isset($request['post_parent'])) {
                $parent = get_post($request['post_parent']);
                if ($parent) {
                    $request['post_title'] .= $parent->post_title;
                }
            }
        }

        if (isset($request['type']) && ('reject' == $request['type'] || 'decline' == $request['type'])) {
            unset($this->ruler['post_content']);
        }

        $response = $this->sync_post($request);
        do_action('ae_after_message', $response, $request);

        $response = apply_filters('ae_message_response', $response, $request);
        if (in_array($request['type'], array('decline', 'reject'))) {
            $response['data']->custom_order_id = $request['custom_order_id'];
        }

        wp_send_json($response);
    }
    /**
     * validate data
     *
     * @param array $data
     * @return array $result
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function validatePost($request)
    {
        global $user_ID;
        if (!isset($request['from_user']) || $request['from_user'] != $user_ID) {
            $result = array(
                'success'   => false,
                'msg'       => __("Missing sender information.", 'enginethemes')
            );
        } else {
            $result = array(
                'success' => true,
                'msg' => __('Successful!', 'enginethemes')
            );
        }
        return $result;
    }
    /**
     * convert
     *
     * @param void
     * @return void
     * @since 1.0
     * @package FREELANCEENGINE
     * @category FRE CREDIT
     * @author Jack Bui
     */
    public function convert($result)
    {
        global $user_ID;
        $result->author_avatar = mje_avatar($result->post_author);
        if (current_user_can('manage_options') || $result->post_author == $user_ID || $result->to_user == $user_ID || ae_user_role($result->post_author) == 'administrator') {
            $result->et_carousels = get_children(array(
                'numberposts' => 15,
                'order' => 'ASC',
                'post_parent' => $result->ID,
                'post_type' => 'attachment'
            ));
        }

        return $result;
    }

    /**
     * Validate data
     * @param array $data
     * @return array $response
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function ae_private_message_validate($data)
    {
        global $user_ID;
        if (!empty($data)) {
            if (isset($data['method']) && $data['method'] == 'create') {
                $response = ae_private_message_created_a_conversation($data);
                if (!$response['success']) {
                    return $response;
                }
                //check sender can send a message
                if (isset($data['from_user']) && $data['project_id']) {
                    $response = $this->ae_private_message_authorize_sender($data['from_user'], $data['project_id'], $data);
                } else {
                    $response = array(
                        'success' => false,
                        'msg' => __("Your account can't send this message!", 'enginethemes')
                    );
                    return $response;
                }
                // check receiver can receive a message
                if (isset($data['to_user']) && $data['bid_id']) {
                    $response = $this->ae_private_message_authorize_receiver($data['to_user'], $data['bid_id'], $data);
                } else {
                    $response = array(
                        'success' => false,
                        'msg' => __("Your account can't send this message!", 'enginethemes')
                    );
                    return $response;
                }
                //check message content
                $response = $this->ae_private_message_authorize_message($data);
            }
        } else {
            $response = array(
                'success' => false,
                'msg' => __('Data is empty!', 'enginethemes')
            );
        }
        return $response;
    }
    /**
     * authorize sender
     * @param integer $user_id
     * @param integer $project_id
     * @param array $data
     * @return array $response
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function ae_private_message_authorize_sender($user_id, $project_id, $data)
    {
        global $user_ID;
        if ($user_id == $user_ID) {
            if (is_project_owner($user_ID, $project_id)) {
                $response = array(
                    'success' => true,
                    'msg' => __("Authorize successful", 'enginethemes'),
                    'data' => $data
                );
                return $response;
            }
        }
        $response = array(
            'success' => false,
            'msg' => __("Your account can't send this message!", 'enginethemes')
        );
        return $response;
    }
    /**
     * authorize receiver
     * @param integer $user_id
     * @param integer $bid_id
     * @param array $data
     * @return array $response
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function ae_private_message_authorize_receiver($user_id, $bid_id, $data)
    {
        global $user_ID;
        if ($user_id == $user_ID) {
            if (is_bid_owner($user_ID, $bid_id)) {
                $response = array(
                    'success' => true,
                    'msg' => __("Authorize successful", 'enginethemes'),
                    'data' => $data
                );
                return $response;
            }
        }
        $response = array(
            'success' => false,
            'msg' => __("Your account can't send this message!", 'enginethemes')
        );
        return $response;
    }
    /**
     * authorize message content
     * @param array $data
     * @return array $response
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function ae_private_message_authorize_message($data)
    {
        if (isset($data['post_title']) &&  $data['post_title'] !== '') {
            $response = array(
                'success' => true,
                'msg' => __("Valid title!", 'enginethemes'),
                'data' => $data
            );
        } else {
            $response = array(
                'success' => false,
                'msg' => __("Please enter your message's subject!", 'enginethemes')
            );
            return $response;
        }
        if (isset($data['post_content']) &&  $data['post_content'] != '') {
            $response = array(
                'success' => true,
                'msg' => __("This content is valid!", 'enginethemes'),
                'data' => $data
            );
        } else {
            $response = array(
                'success' => false,
                'msg' => __("Please enter your message's content!", 'enginethemes')
            );
            return $response;
        }
        return $response;
    }

    /**
     * Convert private message
     * @param object $result
     * @return object $result
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function ae_message_convert($result)
    {

        return $result;
    }
    /**
     * Filter args when fetch data
     * @param array $query_args
     * @return array $query_args
     * @since 1.0
     * @package FREELANCEENGINE
     * @category PRIVATE MESSAGE
     * @author Tambh
     */
    public function filter_query_args($query_args)
    {
        global $user_ID;
        $query = $_REQUEST['query'];
        if (isset($query['meta_query'])) { // add condition 1.3.6
            $query_args['meta_query'] = $query['meta_query'];
        }

        return $query_args;
    }
}
$instance = AE_Private_Message_Actions::get_instance();
$instance->init();
