<?php
/**
 * Template Name: Link Discord
 */
$clientId = get_option('clientID_Discord');
$clientSecret = get_option('clientSecret_Discord');
$redirectUri =get_option('redirectUri_Discord');
$tokenUrl = get_option('tokenUrl_Discord');
$authorizationCode = $_GET['code'];

$data = array(
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'grant_type' => 'authorization_code',
    'code' => $authorizationCode,
    'redirect_uri' => $redirectUri,
    'scope' => 'identify' // Replace with your desired scopes
);

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$response = curl_exec($ch);

if ($response === false) {
    echo 'Error requesting access token: ' . curl_error($ch);
} else {
    $tokenData = json_decode($response, true);
    if (isset($tokenData['access_token'])) {
        $accessToken = $tokenData['access_token'];
       // echo 'Access token: ' . $accessToken;// ko show ra de tranh loi redirect 
        update_user_meta(get_current_user_id(),'discord_token',$accessToken);
    } else {
        echo 'Error getting access token: ' . $response;
    }
}

curl_close($ch);

$userUrl = 'https://discord.com/api/v9/users/@me';

$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $accessToken
));

$response = curl_exec($ch);

if ($response === false) {
    //echo 'Error fetching user information: ' . curl_error($ch);
    return;
} else {
    $userData = json_decode($response, true);
    if (isset($userData['id'])) {
        $userId = $userData['id'];
      //  echo 'User ID: ' . $userId;
        update_user_meta(get_current_user_id(),'DiscordID',$userId);
        update_user_meta(get_current_user_id(),'et_discord_subscribe',1);
    } else {
        echo 'Error getting user ID: ' . $response;
    }
}
curl_close($ch);
wp_redirect(site_url('/discord-notification-settings/'));
exit;