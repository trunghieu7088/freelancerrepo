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



    <div class="mjob-item__entry">

        <div class="mjob-item__title">

            <h2 class="trimmed" title="<?php echo $current->post_title; ?>">

                <a href="<?php echo $current->permalink; ?>"><?php echo $current->post_title; ?></a>

            </h2>

        </div><!-- end .mjob-item__title -->



        <div class="mjob-item__author custom-author-wrapper">

            <span><?php echo $current->author_name; ?></span>
            <!-- custom code here 20th March 2024 -->
            <span class="custom-mjob-item-category">
                <?php echo $current->tax_input['mjob_category'][0]->name; ?>
                
            </span>
            <!-- end custom code here 20th March 2024 -->

        </div><!-- end .mjob-item__author -->



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



$rating_score 		= $mjob_post->rating_score;

$mjob_total_reviews = $mjob_post->mjob_total_reviews;	  

$total_reviews 		= WPS_MjE_Online_Payment_Plateform()->mje_get_total_reviews( $current->post_author );



if ( $total_reviews ) {

	$rating_score = $total_reviews['rating_score'];

	$mjob_total_reviews = $total_reviews['mjob_total_reviews'];

}

?>

            <div class="mjob-item__rating">

                <div class="rate-it star" data-score="<?php echo $rating_score; ?>"></div>

                <span class="total-review"><?php printf( '(%s)', $mjob_total_reviews );?></span>

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