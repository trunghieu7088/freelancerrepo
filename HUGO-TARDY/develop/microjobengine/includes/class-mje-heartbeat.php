<?php

/**
 * Class MJE_Heartbeat
 * Manage all heartbeat actions
 *
 * @since 1.3.2
 * @author Tat Thien
 */
class MJE_Heartbeat
{
    /**
     * MJE_Heartbeat constructor.
     */
    public function __construct()
    {
        add_filter('heartbeat_settings', array($this, 'config_heartbeat'));
        add_filter('heartbeat_received', array($this, 'response_heartbeat'), 15, 2);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_heartbeat_scripts'));
    }

    /**
     * Hook into heartbeat config filters
     *
     * @param array $settings
     * @return array $settings
     */
    public function config_heartbeat($settings)
    {
        $settings['interval'] = 15;
        return $settings;
    }

    /**
     * Add heartbeat related scripts
     */
    public function enqueue_heartbeat_scripts()
    {
        if (is_user_logged_in()) {
            wp_enqueue_script('heartbeat');
            wp_enqueue_script('mje-heartbeat', get_template_directory_uri() . '/assets/js/heartbeat.js', array(
                'front'
            ), ET_VERSION, true);

            /**
             * Fire filter for heartbeat localize
             *
             * @param array
             */
            $heartbeat_localize = apply_filters('mje_heartbeat_localize', array(
                'conversation_nonce' => wp_create_nonce('mje_heartbeat_conversation_nonce'),
                'notification_nonce' => wp_create_nonce('mje_heartbeat_notification_nonce'),
            ));
            wp_localize_script('mje-heartbeat', 'mje_heartbeat', $heartbeat_localize);
        }
    }

    /**
     * Hook into heartbeat response filters
     *
     * @param array $response
     * @param array $data
     * @return array $response
     */
    public function response_heartbeat($response, $data)
    {
        global $user_ID;

        if (isset($data['conversation_nonce']) && wp_verify_nonce($data['conversation_nonce'], 'mje_heartbeat_conversation_nonce')) {
            try {
                if (isset($data['conversation_id']) && isset($data['last_read_time']) && is_array($data['last_read_time'])) {

                    $dateString = $data['last_read_time']['date'];
                    $timezoneOffset = $data['last_read_time']['timezone'];
                    $last_read = new DateTime($dateString, new DateTimeZone($timezoneOffset));
                    $last_read_sql = $last_read->format('Y-m-d H:i:s');

                    // only return unread_messages when turning read status from unread to read
                    if (mje_mark_post_as_read($data['conversation_id'], $user_ID)) {
                        $response['unread_messages'] = $this->get_unread_messages_of_conversation($data['conversation_id'], $last_read_sql);
                    }
                };
                // only counts normal conversations --> for message icon in header
                $response['unread_conversations'] = $this->get_unread_conversations($data);
            } catch (Exception $e) {
                return;
            }
        }

        //return notification data in header
        if (isset($data['notification_nonce']) && wp_verify_nonce($data['notification_nonce'], 'mje_heartbeat_notification_nonce')) {
            $response['unread_notification'] = $this->get_mje_unread_notification_count($data);
        }
        //end

        return $response;
    }

    /**
     * Get unread messages for heartbeat response
     *
     * @return array $response
     */

    //fix bug notification not real
    public function get_mje_unread_notification_count($data)
    {
        $response['notification_count'] = get_unread_new_notification_count();
        return $response;
    }
    // end 

    public function get_unread_conversations($data)
    {
        $response = array();

        $unread_conversations_count = mje_get_unread_conversation_count();
        if ($unread_conversations_count === 0) {
            return $response;
        }

        $response['count'] = $unread_conversations_count;

        ob_start();
        mje_get_user_dropdown_conversation();
        $output = ob_get_clean();
        $response['dropdown_html'] = $output;

        return $response;
    }

    public function get_unread_messages_of_conversation($conversation, $last_read)
    {
        $args = array(
            'post_type' => 'ae_message',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post_parent' => (int)$conversation,
            'fields'          => 'ids',
            'date_query' => array(
                array(
                    'after' => $last_read,
                    'inclusive' => false,
                ),
            ),
        );
        $unread_messages = get_posts($args);
        $return = array();

        if (!empty($unread_messages)) {
            global $ae_post_factory;
            $ae_message_obj = $ae_post_factory->get('ae_message');
            foreach ($unread_messages as $msg_id) {
                $return[] = $ae_message_obj->get($msg_id);
            }
        }
        return $return;
    }
}

new MJE_Heartbeat();
