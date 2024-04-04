
<?php
add_filter('mje_mjob_filter_query_args', 'override_posts_per_page', 999);

function override_posts_per_page($query_args)
{
    $query_args['showposts']=24;        
    return $query_args;
}

