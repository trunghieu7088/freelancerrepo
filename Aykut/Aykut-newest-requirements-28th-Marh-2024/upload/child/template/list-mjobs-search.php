<?php

/**

 * Template list all project

 */

global $ae_post_factory;

$post_object = $ae_post_factory->get('mjob_post');
//custom code 28th Mar 2024
$order          = isset($args['order']) ? $args['order'] : 'DESC';
$orderby        = isset($args['orderby']) ? $args['orderby'] : 'date';
$number_posts   = isset($args['number_posts']) ? $args['number_posts'] : 24;
$args = array(
    'post_type' => 'mjob_post',
    'post_status' => 'publish',
    'showposts' => $number_posts,
    'orderby' => $orderby,
    'order' => $order,
);

$mjob_query = new WP_Query($args);
ob_start();
//end custom code 28th mar 2024
?>

<ul class="row mjob-list list-mjobs">

    <?php

    $post_data = array();

    if( have_posts() ) {

        while (  $mjob_query->have_posts() ) {

             $mjob_query->the_post();

            global $post;

            $convert = $post_object->convert( $post );

            $post_data[] = $convert;

            echo '<li class="col-lg-4 col-md-4 col-sm-6 col-xs-6 col-mobile-12 item_js_handle">';

            mje_get_template( 'template/mjob-item.php', array( 'current' => $convert ) );

            echo '</li>';

        }

    } else {

        printf(__('<div class="not-found">This search matches 0 results! <p class="not-found-sub-text"><label for="input-search" class="new-search-link">New search</label> or <a href="%s">back to home page</a></p></div>', 'enginethemes'), get_site_url());

    }

    ?>

</ul>



<?php

echo '<script type="data/json" class="mJob_postdata" >'.json_encode( $post_data ).'</script>';

?>

