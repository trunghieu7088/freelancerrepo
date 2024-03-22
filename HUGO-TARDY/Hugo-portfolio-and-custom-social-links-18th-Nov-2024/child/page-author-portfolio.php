<?php
/**
 * Template Name: Page Author Portfolio
 */
global $wp_query, $ae_post_factory, $post, $current_user, $user_ID;

//author page code
if(!isset($_GET['pauthor']) || empty($_GET['pauthor']))
{
    wp_redirect(site_url());
}

$getUser=get_user_by('login',$_GET['pauthor']);

$author_id 	= $getUser->ID;

$author = mJobUser::getInstance();
$author_data = $author->get($author_id);

$user_ID=$author_id;
$user_data=$author_data;

// Convert profile
$profile_obj = $ae_post_factory->get('mjob_profile');
$profile_id = get_user_meta($author_id, 'user_profile_id', true);
if($profile_id) {
    $post = get_post($profile_id);
    if($post && !is_wp_error($post)) {
        $profile = $profile_obj->convert($post);
    }
}
//end  author page code

// If user profile id is valid
if($profile_id) {
    // Get profile
    $post = get_post($profile_id);
    // If profile is valid
    if($post && !is_wp_error($post)) {
        // Check if user has profile or not
        if($post->post_author == $user_ID && $post->post_type == 'mjob_profile') {
            $profile = $profile_obj->convert($post);
        } else {
            // Create a new profile, if user has not profile,
            $profile_id = wp_insert_post(array(
                'post_type' => 'mjob_profile',
                'post_status' => 'publish',
                'post_title' => $author_data->display_name,
                'post_author' => $author_data->ID
            ));
            update_user_meta($author_data->ID, 'user_profile_id', $profile_id);
            $profile = $profile_obj->convert(get_post($profile_id));
        }
    }
    echo '<script type="text/json" id="mjob_profile_data" >'.json_encode($profile).'</script>';
}
// Get profile infomation
$description = !empty($profile->profile_description) ? $profile->profile_description : __('There is no content', 'enginethemes');

//custom code here 17th Feb 2023
$custom_website=!empty($profile->custom_website) ? $profile->custom_website : 'There is no content';
$custom_facebook=!empty($profile->custom_facebook) ? $profile->custom_facebook : 'There is no content';
$custom_twitter=!empty($profile->custom_twitter) ? $profile->custom_twitter : 'There is no content';
$custom_youtube=!empty($profile->custom_youtube) ? $profile->custom_youtube : 'There is no content';
$custom_myspace=!empty($profile->custom_myspace) ? $profile->custom_myspace : 'There is no content';
$custom_linkedin=!empty($profile->custom_linkedin) ? $profile->custom_linkedin : 'There is no content';
$custom_instagram=!empty($profile->custom_instagram) ? $profile->custom_instagram : 'There is no content';
$custom_email=!empty($profile->custom_email) ? $profile->custom_email : 'There is no content';
$custom_pinterest=!empty($profile->custom_pinterest) ? $profile->custom_pinterest : 'There is no content';
$custom_soundcloud=!empty($profile->custom_soundcloud) ? $profile->custom_soundcloud : 'There is no content';
$custom_tumblr=!empty($profile->custom_tumblr) ? $profile->custom_tumblr : 'There is no content';

//end 

$country_name = isset($profile->tax_input['country'][0]) ? $profile->tax_input['country'][0]->name : '';
$languages = isset($profile->tax_input['language']) ? $profile->tax_input['language'] : '';

//get portfolios

$my_portfolios=get_all_portfolio($author_id);

if(isset($_GET['port'])&& !empty($_GET['port']))
{
    $chosen_port=$_GET['port'];
}
else
{
    $chosen_port='all';
}

if($chosen_port !='all')
{
    $chosen_port_info=get_chosen_portfolio($chosen_port);
}
else
{
    $chosen_port_info=false;
}

if($chosen_port_info)
{
    $portfolio_title=$chosen_port_info->post_title;
}
else
{
    $portfolio_title='All';
}

$porfolio_items=get_portfolio_images($author_id,$chosen_port);

if(!empty($porfolio_items)) 
{
    $counted_items=count($porfolio_items);
}
else
{
    $counted_items=0;
}

$banner_url_image=get_post_meta($profile_id,'banner_image_url',true);
if(!$banner_url_image)
{
    $banner_url_image=DEFAULT_BANNER_URL_PORTFOLIO;
}

get_header();
?>
<div class="topoverlay">

</div>
<div class="portfolio-top-wrapper">
    <div class="portfolio-container">

        <!-- main content -->

        <div class="port-main-content">          

            <!-- banner background -->
            <div class="port-banner-background">
                <div id="update-banner-btn-area" class="update-banner-btn-area">
                    
                    <!-- open profile sidebar button ( only show on ipad and mobile) -->
                        <a class="show-sidebar-btn" href="javascript:void(0)">Open Profile <i class="fa fa-user"></i></a>
                    <!-- end open profile sidebar button -->

                    
                </div>
                <img id="port-banner-image" src="<?php echo $banner_url_image; ?>">
            </div>

            <div class="port-head-title-info">
                <p>
                    <?php echo $portfolio_title; ?> <span class="counted-images"><?php echo $counted_items; ?> images</span>
                    <?php if($chosen_port_info): ?>
                        <span class="portfolio-description">Description: <?php echo $chosen_port_info->post_content; ?></span>
                       
                    <?php endif; ?>
                </p>
            </div>
           
            <div class="port-title-list">
                <a class="<?php if($chosen_port=='all') echo 'port-active'; ?>" href="<?php echo site_url('author-portfolio').'?pauthor='.$author_data->user_login; ?>">All</a>
                <?php if(!empty($my_portfolios)): ?>
                    <?php foreach($my_portfolios as $my_portfolio) : ?>
                        <?php 
                        if($chosen_port==$my_portfolio->post_name)    
                            $active_class='port-active';
                        else
                            $active_class='';
                        ?>
                        <a class="<?php echo $active_class; ?>"  href="<?php echo site_url('author-portfolio').'?pauthor='.$author_data->user_login.'&port='.$my_portfolio->post_name; ?>"><?php echo $my_portfolio->post_title; ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>             
            </div>

            <div class="port-folio-collection">
                <?php if(!empty($porfolio_items)): ?>
                    <?php foreach($porfolio_items as $porfolio_item) : ?>
                        <div class="port-item-wrapper">
                            <a data-port-item-id="<?php echo $porfolio_item->ID; ?>" data-lightbox="portfolio-item" href="<?php echo wp_get_attachment_image_url( $porfolio_item->ID, 'medium_large'); ?>">
                                <img src="<?php echo wp_get_attachment_image_url( $porfolio_item->ID, 'thumbnail'); ?>">                            
                            </a>
                            <p class="chosen-image-select"><a class="image-selector-port" data_select_status="false" data_attachment_id_select="<?php echo $porfolio_item->ID; ?>" href="javascript:void(0)">Select</a></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
               <!-- <a href="#">
                    <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/view1.png'; ?>">
                </a> -->
               
            </div>

        </div>

        <!-- sidebar -->

        <div class="port-sidebar">

            <!-- hide port sidebar -->
              <div class="hide-port-sidebar-mobile">
                    <a href="javascript:void(0)" class="hide-port-sidebar-btn">
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </a>
              </div>          
            <!-- end hide port sidebar -->

            <div class="port-profile-info-area">
                <div class="port-profile-avatar">
                    <?php echo mje_avatar($user_ID, 75); ?>
                </div>
                <div class="port-profile-name">
                    <?php echo $author_data->display_name ; ?>
                </div>  
                <div class="port-profile-bio">
                    <?php echo $description; ?>
                </div> 
                <div class="port-profile-location">
                    <p><i class="fa fa-map-marker"></i> <span><?php echo $country_name; ?></span></p>
                    <p><i class="fa fa-globe"></i>
                    <?php if(!empty($languages)) 
                    {
                            foreach($languages as $language) 
                            {
                                ?>
                            <span><?php echo $language->name; ?></span>
                                <?php
                            }
                     }?>
                    </p>
                   
                </div> 
                <!-- <div class="port-profile-viewbtn">
                    <a href="<?php echo get_author_posts_url($author_id); ?>" class="port-view-pf-btn">
                        View Profile
                    </a>
                </div> -->
                <div class="port-profile-socialblock">                    
                     <div class="port-list-social-icons">
                        <p class="port-socialblock-title">Follow on Social</p>
                        <div class="port-list-social-items">
                            <?php display_custom_social_icons_block($profile_id); ?>
                        </div>
                     </div>
                </div>

                <div class="port-profile-add-portfolio">                    
                        
                    <a class="port-add-portfolio-btn" href="<?php echo get_author_posts_url($author_id); ?>">
                            View Profile <i class="fa fa-user"></i>
                    </a>                 
                    
                </div>
            </div>
        </div>

    </div>
</div>


<?php 
get_footer();
?>