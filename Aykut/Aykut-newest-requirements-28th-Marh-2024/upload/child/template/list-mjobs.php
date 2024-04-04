<?php

/**

 * Template list all mJobs

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

global $post_data;

$post_data  = array();

    if( function_exists('mjob_featured_block')){

        //mjob_featured_block();

    }

?>



<?php

    if(have_posts()) {

        while ($mjob_query->have_posts()) {

            $mjob_query->the_post();

            global $post;

            $convert = $post_object->convert( $post );

            $post_data[] = $convert;

            echo '<li class="'. mje_loop_item_css($convert) .'">';

            mje_get_template( 'template/mjob-item.php', array( 'current' => $convert ) );

            echo '</li>';

        }

    } else {

        ?>

        <div class="not-found"><?php _e('There are no mJobs found!', 'enginethemes'); ?></div>

        <?php

    }

?>



</ul>



<?php

echo '<script type="data/json" class="mJob_postdata" >'.json_encode( $post_data ).'</script>';

?>

