<?php
class MJE_Post_Action extends AE_PostAction
{
    public static $instance;
    public $ruler;
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
     * the constructor of this class
     *
     */
    public  function __construct($post_type = 'post')
    {
        parent::__construct($post_type);
    }
    /**
     * sync post
     *
     * @param array $request
     * @return array $result
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function sync_post($request)
    {
        // Default result
        $result = array(
            'success' => false,
            'msg'     => __('Failed!', 'enginethemes')
        );

        // check form nonce first
        if (!de_verify_nonce($request['_wpnonce'], 'ae-mjob_post-sync')) {
            $result = array(
                'success' => false,
                'msg' => __("You are not allowed to do this action!", 'enginethemes')
            );
            return $result;
        }

        // check user status, return if user is pending
        $checkPending  = $this->checkPendingAccount($request);
        if (!$checkPending['success']) {
            return $checkPending;
        }

        // run through validator again 
        $this->ruler = wp_parse_args($this->ruler, array(
            'post_type' => 'required',
        ));
        $error_message = array(
            'post_type.required' => __('Post type missing!', 'enginethemes'),
            'nonce.required' => __('You are not allowed to do this action!', 'enginethemes'),
            'nonce.nonce' => __('You are not allowed to do this action!', 'enginethemes'),
        );
        $validator = new AE_Validator($request, $this->ruler, array(), $error_message);
        if ($validator->fails()) {
            $result['msg']  = __('Invalid input. Please try again.', 'enginethemes');
            $result['data'] = $validator->getMessages();
            return $result;
        }

        // Request pass all the rules, call post object to sync it
        global $ae_post_factory, $user_ID;

        // apparently we're still not sure after all the above checks, so we check again @@ 
        if (!isset($request['post_type'])) {
            $result['success'] = false;
            $result['msg']     = __('You are not allowed to do this action!', 'enginethemes');
            return $result;
        }

        // do we have this post type machine ready?
        $post_object = $ae_post_factory->get($request['post_type']);
        if (NULL == $post_object) {
            $result['success'] = false;
            $result['msg']     = __('You are not allowed to do this action!', 'enginethemes');
            return $result;
        }

        // unset package data when edit place if user can edit others post
        if (isset($request['archive'])) {
            $request['post_status'] = 'archive';
        }
        if (isset($request['publish'])) {
            $request['post_status'] = 'publish';
        }
        if (isset($request['delete'])) {
            $request['post_status'] = 'trash';
        }
        if (isset($request['disputed'])) {
            $request['post_status'] = 'disputed';
        }
        if (isset($request['pause'])) {
            $request['post_status'] = 'pause';
            unset($request['pause']);
        }
        if (isset($request['unpause'])) {
            $request['post_status'] = 'unpause';
            unset($request['unpause']);
        }
        if (isset($request['finished'])) {
            $request['post_status'] = 'finished';
            unset($request['finished']);
        }

        /**
         * add var data_price before sync mjob to get the old et_budget value
         * this is to support MjE Job Verification plugin
         * TODO: recheck this later
         * author : Tan Hoai
         * version 1.3.1
         */
        $id = isset($request['ID']) ? $request['ID'] : 0;
        $data_prices = array(
            'ID' => $id,
            'old' => get_post_meta($id, 'et_budget', true),
            'new' => isset($request['et_budget']) ? $request['et_budget'] : 0,
        );

        // Call instance sync, return if failed to sync post
        $post = $post_object->sync($request);

        if (is_wp_error($post)) {
            //Not inserted
            $result['success'] = false;
            $result['msg']     = $post->get_error_messages();
            $result['data']    = $post->get_error_data();
            return $result;
        }

        // if it is a remove request, return here
        if ('remove' === $request['method']) {
            $result['success'] = true;
            $result['data']    = $post_object->convert($post);
            $result['msg']     = __('Delete successfully!', 'enginethemes');
            return $result;
        }

        // successfully added the post, now attach the previously uploaded image to the post
        if (('remove' !== $request['method']) && isset($request['et_carousels']) && !empty($request['et_carousels'])) {
            // loop request carousel id
            foreach ($request['et_carousels'] as $key => $value) {
                $att = get_post($value);
                // just admin and the owner can add carousel
                global $user_ID;
                if (!empty($att) && isset($att->post_author)) {
                    if (current_user_can('manage_options') || $att->post_author == $user_ID) {
                        wp_update_post(array(
                            'ID' => $value,
                            'post_parent' => $post->ID
                        ));

                        // no request to set the featured image, but post doesn't have thumbnail, set it
                        if (!isset($request['featured_image']) && !has_post_thumbnail($post)) {
                            set_post_thumbnail($post->ID, $value);
                        }
                    }
                }
            }
        }

        /**
         * Add action cheat price change
         * this is to support MjE Job Verification plugin
         * TODO: recheck this later
         * @since 1.3.1
         * @author Tan Hoai
         */
        do_action('mje_action_after_update_mjob', $data_prices);

        // everything done successfully, return
        $result['success'] = true;
        $result['msg']     = __('Successful!', 'enginethemes');
        $result['data']    = $post_object->convert($post);
        return $result;
    }
    /**
     * check pending account
     *
     * @param void
     * @return void
     * @since 1.0
     * @package MicrojobEngine
     * @category void
     * @author JACK BUI
     */
    public function checkPendingAccount($request)
    {
        global $user_ID;
        $result = array(
            'success' => true,
            'msg' => __('Success', 'enginethemes')
        );
        if (!AE_Users::is_activate($user_ID)) {
            $result = array(
                'success' => false,
                'msg' => __("Your account is pending. You have to activate your account to continue this step.", 'enginethemes')
            );
        }
        return apply_filters('mjob_check_pending_account', $result, $request);
    }
}
