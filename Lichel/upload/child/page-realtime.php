<?php 
/**
 * Template Name: Realtime Page
 */

require __DIR__ . '/vendor/autoload.php';
 $options = array(
    'cluster' => 'ap1',
    'useTLS' => false
  );
  $pusher = new Pusher\Pusher(
    '6d999a97908fc45a5632',
    'f5666cba025faef9935e',
    '1444563',
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