<?php
add_filter('teeny_mce_buttons', 'custom_ce_teeny_mce_buttons',99);
function custom_ce_teeny_mce_buttons($buttons)
{
    return array(
        'format',
        'bold',
        'italic',
        'underline',
        'bullist',
        'numlist',     
    );
}


//small change on 18 Dec 2023
add_filter('wp_title','custom_title_session',999,1);

function custom_title_session($title) {
	 if (is_post_type_archive('mjob_post')) {
    $title = 'Session Archive - Expert guidance in live one-on-one sessions.';
    }
   
    return $title;
}