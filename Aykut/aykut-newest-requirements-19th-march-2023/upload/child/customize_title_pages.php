<?php
add_filter( 'wp_title', 'custom_wp_title', 999, 2 );

function custom_wp_title($title,$sep)
{
    if(is_post_type_archive('mjob_post'))
    {
        $title='Ghostwriter Angebote';
    }
    return $title;
}