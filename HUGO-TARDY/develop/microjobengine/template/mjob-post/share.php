<div class="sharing">
    <?php
    /**
     * Fire action before mjob detail dock bar
     *
     * @since 1.3.1
     * @author Tat Thien
     */
    do_action('mje_before_mjob_dock', $mjob_post);
    ?>
    <ul class="link-social list-share-social addthis_toolbox addthis_default_style">
        <?php
        /**
         * Fire action before social list
         *
         * @since 1.3.1
         * @author Tat Thien
         */
        do_action('mje_before_mjob_social_list', $mjob_post);
        ?>
        <li class="facebook"><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $mjob_post->permalink; ?>" class="face" title="<?php _e('Facebook', 'enginethemes'); ?>"><i class="fa fa-facebook"></i></a></li>
        <li class="twitter"><a href="https://twitter.com/intent/tweet?url=<?php echo $mjob_post->permalink; ?>" class="twitter" title="<?php _e('Twitter', 'enginethemes'); ?>"><i class="fa-brands fa-x-twitter"></i></a></li>
        <?php
        /**
         * Fire action after social list
         *
         * @since 1.3.1
         * @author Tat Thien
         */
        do_action('mje_after_mjob_social_list', $mjob_post);
        ?>
    </ul>
    <?php
    /**
     * Fire action before mjob detail dock bar
     *
     * @since 1.3.1
     * @author Tat Thien
     */
    do_action('mje_after_mjob_dock', $mjob_post);
    ?>
</div>