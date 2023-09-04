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
