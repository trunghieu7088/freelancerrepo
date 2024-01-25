<?php
   add_shortcode('custom_homepage_slider', 'custom_homepage_slider_init');

   function custom_homepage_slider_init()
   {
    ob_start();
    ?>
     <div class="block-items" style="margin-top:0px !important;padding-top: 30px!important;">
            <div class="container" style="position: relative;padding:5px !important;">
                <p class="block-title float-center" style="margin-bottom:30px !important;">NEWEST GIGS</p>
               <?php global $count_featured;
                    $count_featured = 0;
                    $showposts = 20;
                    $featured_html = '';

                    if( function_exists('html_mjob_featured_home') ){
                        $featured_html = html_mjob_featured_home();
                        $showposts = 20 - $count_featured;
                    }

                    $args = array(
                        'post_type' => 'mjob_post',
                        'post_status' => array(
                            'publish',
                            'unpause',
                        ),
                        'showposts' => $showposts,
                        'orderby' => 'date',
                        'order' => 'DESC',
                    );

                   if( function_exists('html_mjob_featured_home') ){
                        //$args['meta_key'] = 'et_featured';
                        $args['meta_query'] = array(
                            'relation' => 'OR',
                            array(
                                //check to see if et_featured has been filled out
                                'key' => 'et_featured',
                                'compare' => 'IN',
                                'value' =>  array('', '0'),
                            ) ,
                            array(
                                //if no et_featured has been added show these posts too
                                'key' => 'et_featured',
                                'compare' => 'NOT EXISTS'
                            )
                        );
                    }
                    $home_query = new WP_Query($args);
                    global $ae_post_factory;
                    $post_object = $ae_post_factory->get('mjob_post');

                    ?>
                    <ul class="row mjob-list swiper mySwiper" style="position:relative;margin-left:auto !important;margin-right:auto !important;  -ms-overflow-style: none;scrollbar-width: none;">
                        <?php
                        if( ! empty( $featured_html ) )
                            echo $featured_html;
                        ?>
                        <div class="swiper-wrapper">
                            <?php while ($home_query->have_posts()):

                                $home_query->the_post();
                                global $post;
                                $convert = $post_object->convert($post);?>

                                <li class="<?php echo mje_home_loop_item_css($convert);?> swiper-slide custom-slider-swiper">
                                    <?php

                                    mje_get_template('template/custom-mjob-item.php', array('current' => $convert)); ?>
                                </li>
                                
                            <?php endwhile;?>                                           
                        </div>                                                    
                    </ul>

                    <?php wp_reset_postdata();?>
                <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/next-icon-blue.png';  ?>" class="next-icon-css" name="nextslidep" id="nextslidep" >

                <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/previous-icon-blue.png';  ?>" class="previous-icon-css" name="prevslidep" id="prevslidep" >
            </div>  
            
            <div class="view-all-jobs-wrap mje-home-default" style="padding-bottom:25px !important;margin-bottom:10px;">
                <a class="btn-order waves-effect waves-light btn-submit mjob-order-action" href="<?php echo get_post_type_archive_link('mjob_post'); ?>">
                    <?php _e('View all jobs', 'enginethemes');?>
                </a>
            </div>

          </div> 
    <?php       
        wp_reset_query();
        return ob_get_clean();
    }
   