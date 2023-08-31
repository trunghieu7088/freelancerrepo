<?php 
/**
 * Template Name: Realtime Page
 */

require __DIR__ . '/vendor/autoload.php';
 $options = array(
    'cluster' => 'eu',
    'useTLS' => false
  );
  $pusher = new Pusher\Pusher(
    '79f2750396f1ce73fcd0',
    '01b7e1f73babfb41c11c',
    '1604376',
    $options
  );
if ( is_user_logged_in() )
{
  $user_data = ['id' => (string) get_current_user_id()];
  global $current_user;
  get_currentuserinfo();
  $presence_data = array('name' => $current_user->display_name);   

  echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $current_user->ID, $presence_data);
}
else
{
  header('', true, 403);
  echo "Forbidden";
}