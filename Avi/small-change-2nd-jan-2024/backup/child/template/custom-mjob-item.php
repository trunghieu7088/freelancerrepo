<div class="<?php echo $current->mjob_class; ?> custom-gig-new-template">
    <?php
    /**
     * Fire action before mjob item
     *
     * @param object $current
     * @since 1.3.1
     * @author Tat Thien
     */
    do_action('mje_mjob_item_top', $current);
    //custom code here
    $custom_mjob_profile=get_userdata($current->post_author);
    $short_content=wp_trim_words($current->post_content,8,'..');
    //end
    ?>
    <div class="mjob-item__image custom-gig-image">
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

    <div class="mjob-item__entry custom-gig-entry">
        <div class="mjob-item__title custom-gig-title-area">
            <p class="custom-slider-gig-title"><?php echo  $custom_mjob_profile->display_name; ?></p>
            <h2 class="trimmed" title="<?php echo $current->post_title; ?>">
                <a href="<?php echo $current->permalink; ?>"><?php echo $current->post_title; ?></a>
            </h2>
            <div class="custom-gig-description">
                <?php echo $short_content; ?>
            </div>
        </div><!-- end .mjob-item__title -->

        
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