<?php
use GuzzleHttp\Client;

function add_page_for_discord_function()
{
$PageGuid = site_url() . "/link-discord";
$check_exist=get_page_by_title('link-discord');
      if(empty($check_exist))
      {
        $LinkDiscord_page = array( 'post_title'     => 'link-discord',
                         'post_type'      => 'page',
                         'post_name'      => 'link-discord',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid );

      $LinkDiscord_page_id=wp_insert_post( $LinkDiscord_page, FALSE ); 
      add_post_meta($LinkDiscord_page_id,'_wp_page_template','page-link-discord.php');
      }


$PageGuid2 = site_url() . "/discord-notification-settings";      
$check_exist2=get_page_by_title('discord-notification-settings');
      if(empty($check_exist2))
      {
        $DiscordNoti_page = array( 'post_title'     => 'discord-notification-settings',
                         'post_type'      => 'page',
                         'post_name'      => 'discord-notification-settings',
                         'post_content'   => '',
                         'post_status'    => 'publish',
                         'comment_status' => 'closed',
                         'ping_status'    => 'closed',
                         'post_author'    => 1,
                         'menu_order'     => 0,
                         'guid'           => $PageGuid2 );

      $DiscordNoti_page_id=wp_insert_post( $DiscordNoti_page, FALSE ); 
      add_post_meta($DiscordNoti_page_id,'_wp_page_template','page-settings-discord.php');
      }

}

add_action( 'init', 'add_page_for_discord_function' );

add_action('init','SetupDiscordAPI');
function SetupDiscordAPI()
{
    $clientID_Discord='1111279517978341540';
    $clientSecret_Discord = '9YlqwFeTvCIhqpzlyUv-W3M4f5eBhn_U';
    $redirectUri_Discord ='https://discord.hieubinh.com/link-discord/';
    $botToken_Discord='MTExMTI3OTUxNzk3ODM0MTU0MA.GJhBeN.p8pvZfjL5x7KxCtbGIc_881njlfrCwOPFm_qrM';
   
    $tokenUrl_Discord = 'https://discord.com/api/v9/oauth2/token';
    $dmUrl_Discord = 'https://discord.com/api/v9/users/@me/channels';
    
    $loginDiscordLink='https://discord.com/api/oauth2/authorize?client_id=1111279517978341540&permissions=2048&redirect_uri=https%3A%2F%2Finstallmje.hieubinh.com%2Flink-discord%2F&response_type=code&scope=bot%20identify%20guilds';

    update_option('clientID_Discord',$clientID_Discord);
    update_option('clientSecret_Discord',$clientSecret_Discord);
    update_option('redirectUri_Discord',$redirectUri_Discord);
    update_option('botToken_Discord',$botToken_Discord);
    update_option('tokenUrl_Discord', $tokenUrl_Discord);
    update_option('dmUrl_Discord', $dmUrl_Discord);
    update_option('loginDiscordLink',$loginDiscordLink);
   
}


add_action('mje_after_user_dropdown_menu','add_discord_menu');
function add_discord_menu()
{
    echo '<li><a href="'.site_url('/discord-notification-settings/').'">Discord Notification</a></li>';
}


add_action('mje_after_user_sidebar_menu', 'add_discord_menu_sidebar_menu');
function add_discord_menu_sidebar_menu()
{
    ob_start();
    ?>
             <li class="hvr-wobble-horizontal"><a href="<?php echo site_url('/discord-notification-settings/'); ?>">Discord Notification</a></a></li>
    <?php
    echo ob_get_clean();
}



add_action('sendMessageDiscord','sendMessageDiscordFunction',10,2);
function sendMessageDiscordFunction($userId_discord,$message)
{
    require 'vendor/autoload.php';

$botToken =  get_option('botToken_Discord');

$userId =$userId_discord;

$dmUrl = get_option('dmUrl_Discord');

// Create a DM channel with the user
$data = array(
    'recipient_id' => $userId
);

$client = new Client();

$response = $client->post($dmUrl, [
    'headers' => [
        'Authorization' => 'Bot ' . $botToken,
        'Content-Type' => 'application/json',
    ],
    'json' => $data,
]);

if ($response->getStatusCode() === 200) {
    $dmData = json_decode($response->getBody(), true);
    $dmChannelId = $dmData['id'];

    // Send the message in the DM channel
    $messageUrl = "https://discord.com/api/v9/channels/$dmChannelId/messages";
    $messageData = array(
        'content' => $message
    );

    $response = $client->post($messageUrl, [
        'headers' => [
            'Authorization' => 'Bot ' . $botToken,
            'Content-Type' => 'application/json',
        ],
        'json' => $messageData,
    ]);

      /*  if ($response->getStatusCode() === 200) {
            echo 'Message sent successfully';
        } else {
            echo 'Error sending message';
        } */

} 
   /* else 
    {
        echo 'Error creating DM channel';
    } */
}


add_action( 'wp_ajax_et_discord_subscribe', 'et_discord_subscribe_init' );

function et_discord_subscribe_init() 
{



        $value = ($_REQUEST['value'] == false || $_REQUEST['value'] == 'false') ? 2 : 1;

        update_user_meta(get_current_user_id(),'et_discord_subscribe', $value);

        wp_send_json( array('success' => true, 'msg' => __('Your setting is update','enginethemes' ) ) );
   
    die();
}

function et_get_discord_subscribe_settings($user_id = 0){

    if( ! $user_id ){
        global $user_ID;
        $user_id = $user_ID;
    }

    $et_discord_subscribe =  get_user_meta($user_id,'et_discord_subscribe', true);

    if( $et_discord_subscribe == '2' || $et_discord_subscribe === 2 )
        return false;

    return true;

}

?>
