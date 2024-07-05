<?php

/**
 * Get unread notifications
 *
 * @return array $posts
 * @since 1.3
 * @author Tat Thien
 */
function mje_get_unread_notification()
{
    $notification_action = MJE_Notification_Action::get_instance();
    $posts = $notification_action->get(array(
        'post_status' => 'unread',
        'posts_per_page' => -1
    ));
    return $posts;
}

/**
 * Get the number of unread notifications
 *
 * @return int
 * @since 1.3
 * @author Tat Thien
 */
function mje_get_unread_notification_count()
{
    return count(mje_get_unread_notification());
}

/**
 * Check if current user has notification or not
 *
 * @return bool
 * @since 1.3
 * @author Tat Thien
 */
function mje_is_has_unread_notification()
{
    return mje_get_unread_notification_count() > 0;
}

//fix bug notification not real
function get_unread_new_notification_count()
{
    global $user_ID;
    $args_noti = array(
        'post_type' => 'mje_notification',
        'post_status' => 'unread',
        'numberposts' => -1,
        'author' => $user_ID,
        'fields' => 'ids'
    );
    $notis = get_posts($args_noti);
    $noti_count = count($notis);
    return $noti_count;
}
