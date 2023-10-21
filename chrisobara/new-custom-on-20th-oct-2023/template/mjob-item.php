<div class="<?php echo $current->mjob_class; ?> ">
    <?php
    /**
     * Fire action before mjob item
     *
     * @param object $current
     * @since 1.3.1
     * @author Tat Thien
     */
    do_action('mje_mjob_item_top', $current);
    ?>
    <div class="mjob-item__image">
        <?php
        /**
         * Fire action before mjob image
         *
         * @param object $current
         */
        do_action('mje_mjob_item_before_image', $current);
        ?>        

        <a href="<?php echo $current->permalink; ?>" class="<?php echo $current->class_video; ?>">
            <?php echo $current->mje_get_thumbnail; ?>
        </a>

        <?php
        /**
         * Fire action after mjob image
         *
         * @param object $current
         */
        do_action('mje_mjob_item_after_image', $current);
        ?>
    </div><!-- end .mjob-item__image -->
    <?php 
    //custom code here
    $user_profile_id=get_user_meta($current->post_author,'user_profile_id',true);
    if(isset($user_profile_id) && !empty($user_profile_id))
    {
        $billing_country_mjob=get_post_meta($user_profile_id,'billing_country',true);
        $billing_country_text=get_term($billing_country_mjob);
        
        if(!empty($billing_country_text) && !is_wp_error($billing_country_text))
        {
            $billing_country_show=$billing_country_text->name;
           
        }     
        else
        {
            $billing_country_show='No location';
        }
    }
    else
    {
        $billing_country_show='No location';
    }
    //end
    ?>
    <div class="mjob-item__entry">
        <div class="mjob-item__title">
            <h2 class="trimmed" title="<?php echo $current->post_title; ?>">
                <a href="<?php echo $current->permalink; ?>"><?php echo $current->post_title; ?></a>
            </h2>
        </div><!-- end .mjob-item__title -->

        <div class="mjob-item__author trimmed">
            <!-- custom code here -->
            <a style="font-size:14px !important;color:#2a394e;" href="<?php echo get_author_posts_url($current->post_author); ?>"><span title="<?php echo $current->author_name; ?>"><?php echo $current->author_name; ?></span></a>
            <!-- end custom code here -->
        </div><!-- end .mjob-item__author -->

        <!-- custom code here -->
        <p class="text-left" style="font-size:14px !important;"><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo $billing_country_show; ?></p>
        <?php 
            $mjob_cat=wp_get_post_terms($current->ID,'mjob_category');     
        ?>
        <p class="text-left" style="font-size:14px !important;"><?php  echo $mjob_cat[0]->name; ?></p>
        <!-- end custom code here -->

        <div class="mjob-item__price">
            <div class="mjob-item__price-inner">
                <span class="starting-text"><i class="fa fa-eye" aria-hidden="true"></i><?php echo $current->view_count ? $current->view_count : 0 ?></span>
                <span class="price-text customize-color"><?php echo $current->et_budget_text ?></span>
            </div>
        </div><!-- end .mjob-item__price -->

        <div class="mjob-item__bottom clearfix">
            <?php
            /**
             * Fire action before mjob rating
             *
             * @author Tat Thien
             */
            do_action('mje_mjob_item_before_rating', $current);
            ?>
            <div class="mjob-item__rating">
                <div class="rate-it star" data-score="<?php echo $current->rating_score; ?>"></div>
                <span class="total-review"><?php printf('(%s)', $current->mjob_total_reviews); ?></span>
            </div><!-- end .mjob-item__ratings -->
        </div>
    </div>

    <?php
    /**
     * Fire action after mjob item
     *
     * @param object $current
     */
    do_action('mje_mjob_item_bottom', $current);
    ?>
</div>