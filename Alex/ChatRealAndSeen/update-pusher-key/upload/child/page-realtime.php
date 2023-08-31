<?php 
/**
 * Template Name: Realtime Page
 */

require __DIR__ . '/vendor/autoload.php';
//require 'vendor/autoload.php';
 $options = array(
    'cluster' => 'eu',
    'useTLS' => false
  );
  $pusher = new Pusher\Pusher(
    '648b4b78f093044403e3',
    'd0a7967a325552e597c0',
    '1626253',
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