<?php
global $wp_query, $ae_post_factory, $post, $user_ID;
// Get author data
$user_id = $post->post_author;

if($user_id == $user_ID) {
    $seller_id = get_post_meta($post->ID, 'seller_id', true);
    if(!empty($seller_id)) {
        $user_id = $seller_id;
    }
}

$user = mJobUser::getInstance();
$user_data = $user->get($user_id);

// Convert profile
$profile_obj = $ae_post_factory->get('mjob_profile');
$profile_id = get_user_meta($user_id, 'user_profile_id', true);
if($profile_id) {
    $profile = get_post($profile_id);
    if($profile && !is_wp_error($profile)) {
        $profile = $profile_obj->convert($profile);
    }
}

// User profile information
$description = !empty($profile->profile_description) ? $profile->profile_description : "";
$display_name = isset($user_data->display_name) ? $user_data->display_name : '';
$country_name = isset($profile->tax_input['country'][0]) ? $profile->tax_input['country'][0]->name : '';
$languages = isset($profile->tax_input['language']) ? $profile->tax_input['language'] : '';

//get rating score of profile
$custom_rating_score=mje_get_total_reviews_by_user($user_id);
if(!$custom_rating_score)
{
    $custom_rating_score=0;
}
//get expertise
$expertise=get_term(get_post_meta($profile->ID,'expertise',true),'mjob_category');
if(!$expertise || empty($expertise) || $expertise=='' || is_wp_error($expertise)) 
{
    $expertiseShow='None';
}
else
{
    $expertiseShow=$expertise->name;
}

//get all reviews count of seller
$review_info=get_all_reviews_of_seller($user_id);
$number_of_reviews=$review_info['all_reviews_count'];
?>
<div class="custom-single-public-profile-part">
    <div class="profile-part-header">

        <div class="profile-part-header-info">
            <p class="custom-profile-name"><?php echo $display_name; ?></p>
            <p><?php echo $expertiseShow; ?></p>
            <a class="custom-viewPF-button" href="<?php echo get_author_posts_url($user_id); ?>"><i class="fa fa-user"></i> View Profile</a>
        </div>

        <div class="profile-part-header-avatar">
            <?php echo mje_avatar($user_id, 80); ?>
            <div class="rate-it custom-rating-score" data-score="<?php echo $custom_rating_score; ?>"></div>
            <p class="custom-rating-text-info"><?php echo sprintf("Score %.1f | %d reviews",$custom_rating_score,$number_of_reviews) ?></p>
        </div>

        <!-- <div class="custom-view-profile-button-area">
             <a href="#">View Profile</a>
        </div> -->
    </div>

    <div class="profile-part-body">
        <div class="custom-info-hour">
            <span><i class="fa fa-map-marker country-icon"></i> <?php echo $country_name; ?></span>
            <span><i class="fa fa-globe language-icon"></i> 
            <?php

                    if(!empty($languages)) {
                        $totalLanguages = count($languages);
                        $counter = 0;
                        foreach($languages as $language) {
                            ?>
                            <?php echo $language->name;
                                   $counter++;
                                   if ($counter < $totalLanguages) {
                                       echo ' | ';
                                   }
                            ?>
                            <?php
                        }
                    }
                    ?>
            </span>
            <span class="hourly-rate"><i class="fa fa-briefcase hour-icon"></i> 120$ | hour</span>
        </div>

        <div class="bio-area">
            <span class="bio-title">About me</span>
            <div class="bio-content">                
                <?php echo wp_trim_words($description, 50, '...'); ?>
            </div>
        </div>

        <div class="contact-area">
           <!-- <a href="#" class="custom-contact-btn"><i class="fa fa-comment"></i> Contact me</a> -->
           <?php custom_profile_show_contact_btn($user_id); ?>
        </div>

    </div>


</div>

<?php wp_reset_query(); ?>