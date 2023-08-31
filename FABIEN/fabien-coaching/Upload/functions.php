<?php


function custom_login_redirect() {

return 'https://123coaching-image.fr/le-concept/';

}

add_filter('login_redirect', 'custom_login_redirect');

require('delete_lang.php');