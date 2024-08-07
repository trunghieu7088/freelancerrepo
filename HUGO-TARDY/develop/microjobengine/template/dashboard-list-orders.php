<?php



global $ae_post_factory, $user_ID, $wp_query;
$post_obj = $ae_post_factory->get('mjob_order');
$default = array();
if( is_page_template('page-dashboard.php') ){
    $default = array('posts_per_page'=> 5);
}
?>
<div class="list-order">
    <?php
    $args = array(
        'post_type' => 'mjob_order',
        'post_status' => array(
            'publish',
            'late',
            'pending',
            'delivery',
            'disputing',
            'disputed',
            'finished'
            ),
        'author' => $user_ID,
        'orderby'=> 'date',
        'order'=> 'DECS'
    );
    $args = wp_parse_args($args, $default);
    $cus_query = new WP_Query($args);
    $postdata = array();
    if($cus_query->have_posts()) { ?>
        <ul class="list-orders mjob-list mjob-list--horizontal">
            <?php
            while($cus_query->have_posts()) {
                $cus_query->the_post();
                $convert = $post_obj->convert($post);
                $postdata[] = $convert;
                get_template_part('template/order-list', 'item');
            }
            wp_reset_postdata();
            ?>
        </ul>
        <?php if(is_page_template('page-dashboard.php')) : ?>
            <div class="view-all float-center"><a href="<?php echo et_get_page_link('my-list-order'); ?>"><?php _e('View all', 'enginethemes'); ?></a></div>
        <?php endif; ?>

    <?php } else { ?>
        <p class="no-items"><?php _e('There are no orders found!', 'enginethemes'); ?></p>
    <?php } ?>

    <?php
    if( !is_page_template('page-dashboard.php') ):
        echo '<div class="paginations-wrapper float-center">';
        ae_pagination($cus_query, get_query_var('paged'), 'load');
        echo '</div>';
        /**
         * render post data for js
         */
        echo '<script type="data/json" class="order_postdata" >' . json_encode($postdata) . '</script>';
    endif;
    ?>
</div>