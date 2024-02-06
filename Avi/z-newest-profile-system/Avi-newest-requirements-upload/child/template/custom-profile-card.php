<?php global $profile_item_passed; ?>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 profile-card-top-wrapper">

    <div class="profile-card-wrapper">

        <div class="profile-card-header">
            <div class="profile-card-header-info">
                <p class="custom-card-profile-name"><?php echo $profile_item_passed['display_name']; ?></p>
                <p><?php echo $profile_item_passed['expertise']; ?></p>
                <a class="custom-card-viewPF-button" href="<?php echo $profile_item_passed['author_link']; ?>"><i class="fa fa-user"></i> View Profile</a>
            </div>

            <div class="profile-card-header-avatar">
                <?php echo $profile_item_passed['avatar']; ?>
                <div class="rate-it custom-card-rating-score" data-score="<?php echo $profile_item_passed['custom_rating_score']; ?>"></div>
                <p class="custom-card-rating-text-info"><?php echo sprintf("Score %.1f | %d reviews",$profile_item_passed['custom_rating_score'],$profile_item_passed['number_of_reviews']); ?></p>
            </div>
            
        </div>

        <div class="profile-card-body">
            <div class="custom-card-info-hour">
                <span><i class="fa fa-map-marker country-icon"></i> <?php echo $profile_item_passed['country_name']; ?></span>
                <span><i class="fa fa-globe language-icon"></i> 
                <?php                
                    if(!empty($profile_item_passed['languages']))
                    {
                        if($profile_item_passed['languages'] == 'None')
                        {
                            echo $profile_item_passed['languages'];                           
                        }
                        else
                        {
                            $totalLanguages = count($profile_item_passed['languages']);
                            $counter = 0;
                            foreach($profile_item_passed['languages'] as $language) {
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
                        
                    }                    
                    ?>
                </span>
                <span class="hourly-rate"><i class="fa fa-briefcase hour-icon"></i> 120$ | hour</span>
            </div>

            <div class="card-bio-area">
                <span class="bio-title">About me</span>
                <div class="bio-content">                
                    <?php echo $profile_item_passed['description']; ?>
                </div>
             </div>

            <div class="card-contact-area">
                <!-- <a href="#" class="custom-contact-btn"><i class="fa fa-comment"></i> Contact me</a> -->
                <?php custom_profile_show_contact_btn($profile_item_passed['profile_id']); ?>
            </div>
           

        </div>


    </div>

</div>
