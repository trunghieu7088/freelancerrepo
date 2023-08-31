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
global $check_page;
?>

    <div class="mjob-item__image">

        <?php

/**

 * Fire action before mjob image

 *

 * @param object $current

 */

do_action('mje_mjob_item_before_image', $current);

//custom code for online / offline here
$profile_id_freelancer=get_user_meta($current->post_author,'user_profile_id',true);
$custom_online_status=get_post_meta($profile_id_freelancer,'custom_online_status',true) ? get_post_meta($profile_id_freelancer,'custom_online_status',true) : 'offline';
if($custom_online_status=='online')
{
   $online_status_text='en ligne';
   $style_status='width: 10px;height: 10px;border:1px solid #4F5854;background-color:#55FF33;display: inline-block;';
   $style_div='position:relative;color:#589D3D;';
}
else
{
   $online_status_text='hors-ligne';
   $style_status='width: 10px;height: 10px;border:1px solid #636461;background-color:#C5C6C2;display: inline-block;';
   $style_div='position:relative;color:#6B6C6A;';
}
//end custom
?>



        <a <?php echo $check_page; ?> href="<?php echo $current->permalink; ?>" class="<?php echo $current->class_video; ?>">

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

                <a <?php echo $check_page; ?> href="<?php echo $current->permalink; ?>"><?php echo $current->post_title; ?></a>

            </h2>

        </div><!-- end .mjob-item__title -->



        <div class="mjob-item__author">

            <span><?php echo $current->author_name; ?></span>

            <div class="text-right text-uppercase" style="<?php echo $style_div; ?>">
                 <div class="img-rounded" id="dot-status-list" title="<?php echo $online_status_text; ?>" style="<?php echo $style_status; ?>"></div>
             <strong> <?php echo $online_status_text; ?>  </strong>      
             </div>

        </div><!-- end .mjob-item__author -->



        <div class="mjob-item__price">

            <div class="mjob-item__price-inner">

                <span class="starting-text"><i class="fa fa-eye" aria-hidden="true"></i><?php echo $current->view_count ? $current->view_count : 0 ?></span>
                <!-- custom code hide price -->
               <!-- <span class="price-text customize-color"><?php echo $current->et_budget_text ?></span>  -->
               <span class="price-text customize-color">Discuter</span>
                  <!-- end custom code hide price -->
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

                <span class="total-review"><?php printf('(%s)', $current->mjob_total_reviews);?></span>

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