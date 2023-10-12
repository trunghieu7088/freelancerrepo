<?php
/**
 * The template for displaying profile in a loop
 * @since  1.0
 * @package FreelanceEngine
 * @category Template
 */
global $wp_query, $ae_post_factory, $post;
$post_object = $ae_post_factory->get( PROFILE );
$current = $post_object->current_post;
if(!$current){
    return;
}
$hou_rate = (int) $current->hour_rate;

//custom code here

//$total_freelancer_has_rank=get_option('total_freelancer_has_rank');
$user_rank_order=get_post_meta($current->ID,'custom_rank_order',true);
//end
?>
<li class="profile-item">
    <div class="profile-list-wrap">
        <a class="profile-list-avatar" href="<?php echo $current->permalink; ?>">
            <?php echo get_avatar($post->post_author); ?>
        </a>
        <h2 class="profile-list-title">
            <a href="<?php echo $current->permalink; ?>"><?php echo $current->author_name; ?>
            <?php

            //custom code for validation here
            $args=array('post_type'=>'validation',
                        'post_status'=>'publish',
                        'author'=>$current->post_author,
                                        );
                                    $validated_document=get_posts($args);
                                    if(!empty($validated_document) && get_post_meta($validated_document[0]->ID,'approve_status',true) == 'publish' )
                                    {
                                         echo '<img src="'.get_stylesheet_directory_uri().'/assets/img/verified.png'.'"><em style="font-size:12px;color:#008000;"> Verified</em>'; 
                                    }    
                                    //end
            ?>
            </a>
        </h2>
        <p class="profile-list-subtitle"><?php echo $current->et_professional_title;?></p>
        <div class="profile-list-info">
            <div class="profile-list-detail">
                <span class="rate-it" data-score="<?php echo $current->rating_score ; ?>"></span>
                <span><?php echo $current->experience ?></span>
                <span><?php echo $current->project_worked; ?></span>

                <?php if( $hou_rate > 0 ) { echo '<span>'; echo $current->hourly_rate_price; echo '</span>'; } ?>

                <span style="font-weight: normal"><?php echo ($current->earned); ?></span>
            </div>
            <div class="profile-list-desc">
	            <?php echo $current->excerpt;?>
            </div>
        </div>
    </div>
</li>
