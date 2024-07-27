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

//get video code
$portfolio_video_items=get_portfolio_videos($author_id,$chosen_port);


if(!empty($portfolio_video_items)) 
{
    $counted_videos=count($portfolio_video_items);
}
else
{
    $counted_videos=0;
}
//end

//get audio code and count audio
$portfolio_audio_items=get_portfolio_videos($author_id,$chosen_port,'portfolio_audio');

$counted_audios=(!empty($portfolio_audio_items)) ? count($portfolio_audio_items) : 0;

//end 

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
                    <?php echo $portfolio_title; ?> <span class="counted-images"><?php echo $counted_items; ?> images | <?php echo $counted_videos; ?> videos | <?php echo $counted_audios; ?> audios</span>
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

            <div class="portfolio-asset-label">
                Images <i class="fa fa-image"></i>
            </div>

            <div class="port-folio-collection">
                <?php if(!empty($porfolio_items)): ?>
                    <?php foreach($porfolio_items as $porfolio_item) : ?>
                       <?php 
                            $text_title="<h3>".$porfolio_item->custom_meta_title."</h3>";
                            $text_description="<p>".$porfolio_item->custom_meta_description."</p>";
                            $social_buttons_img=display_sharing_social_buttons($porfolio_item->ID);
                            $data_caption=esc_html($text_title.$text_description.$social_buttons_img);                            
                        ?>
                        <div class="port-item-wrapper">
                            <a data-port-item-id="<?php echo $porfolio_item->ID; ?>" data-fancybox="gallery" data-caption="<?php echo $data_caption; ?>" href="<?php echo wp_get_attachment_image_url( $porfolio_item->ID, 'medium_large'); ?>">
                                <img src="<?php echo wp_get_attachment_image_url( $porfolio_item->ID, 'thumbnail'); ?>">                                                                                                                            
                            </a>                                                      
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
        
               
            </div>

            <div class="portfolio-asset-label">
                Videos <i class="fa fa-camera"></i>
            </div>

            <div class="port-video-collection">                
             <?php if(!empty($portfolio_video_items)): ?>
                    <?php foreach($portfolio_video_items as $porfolio_video_item) : ?>
                        <?php                         
                        $video_url=wp_get_attachment_url($porfolio_video_item->ID);                                                                       
                        ?>
                        <?php if($video_url): ?>                       
                            <div class="port-video-wrapper">                                                                
                                 <?php 
                                 //init video instance as esc html string for fancy box.
                                 $video_part='<div class="custom-fancy-video-port-container">';
                                 $video_part.='<video disablepictureinpicture class="fancy-video-port" playsinline controls>';
                                 $video_part.='<source src="'.$video_url.'" type="'.$porfolio_video_item->post_mime_type.'"/>';
                                 $video_part.='</video>';
                                 $video_part.='<p class="custom_fancy_video_title">'.$porfolio_video_item->custom_meta_title.'</p>';
                                 $video_part.='<p class="custom_fancy_video_description">'.$porfolio_video_item->custom_meta_description.'</p>';
                                 $video_part.=display_sharing_social_buttons($porfolio_video_item->ID);
                                 $video_part.='</div>';
                                 $video_init=esc_html($video_part);
                                 ?>
                                 <a data-fancybox="video-group" data-type="html" href="<?php echo $video_init; ?>" class="show_central_video_player">
                                    <i class="fa fa-play"></i>
                                 </a>
                                <video class="portfolio-item-video-player" disablepictureinpicture playsinline controls>                                                                        
                                    <source src="<?php echo $video_url; ?>" type="<?php echo $porfolio_video_item->post_mime_type;  ?>" />    
                                </video>                                                                
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

             <!-- portfolio audio collection -->

             <div class="portfolio-asset-label">
                Audios <i class="fa fa-music"></i>
            </div>

            <div class="port-audio-collection">   
                <div class="container-fluid">
                    <div class="row port-audio-container">
                        <?php if(!empty($portfolio_audio_items)): ?>
                            <?php foreach($portfolio_audio_items as $port_audio_item ): ?>
                                <div class="port-audio-item col-md-1 col-sm-1 col-xs-6">
                                    <?php 
                                    //init audio as html string for fancybox
                                    $audio_url=wp_get_attachment_url($port_audio_item->ID);  
                                    $audio_type=$port_audio_item->post_mime_type;
                                    
                                    $audio_part='<div class="audio-player-fancybox-area">';
                                    
                                    $audio_part.='<audio class="custom-single-audio-player-port" controls>';
                                    $audio_part.='<source src="'.$audio_url.'" type="'.$audio_type.'">';
                                    $audio_part.='Your browser does not support the audio element.';
                                    $audio_part.='</audio>';

                                    $audio_part.='<p class="custom_meta_title_fancy">'.$port_audio_item->custom_meta_title.'</p>';
                                    $audio_part.='<p class="custom_meta_description_fancy">'.$port_audio_item->custom_meta_description.'</p>';
                                    $audio_part.=display_sharing_social_buttons($port_audio_item->ID);
                                    $audio_part.='</div>';
                                    $audio_init=esc_html($audio_part);
                                    ?>
                                    <a data-fancybox='audio-group' data-type="html" href="<?php echo $audio_init; ?>" class="port-audio-a-tag">
                                        <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/music.png'; ?>">
                                        <p class="text-audio-caption"><?php echo $port_audio_item->custom_meta_title; ?></p>
                                    </a>                                                                        
                                </div>        
                            <?php endforeach; ?>
                        <?php endif;?>
                        
                    </div>
                </div>  
            </div>
                       
            <!--  end portfolio audio collection -->


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

<!-- central video top overlay -->
<div class="video_player_central_topoverlay">
    <div class="close_modal_video_player_button">
        <button id="close_modal_video_player_btn"><i class="fa fa-close"></i></button>
    </div>
    <div class="full-central-video-container">
        <video disablepictureinpicture id="central_video_player" class="full-central-video" playsinline controls>                                                                        
            <source id="central_video_player_src" />    
        </video>
    </div>
</div>
<!-- end -->

<?php 
get_footer();
?>