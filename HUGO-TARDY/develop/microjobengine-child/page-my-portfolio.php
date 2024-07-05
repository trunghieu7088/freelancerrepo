<?php
/**
 * Template Name: Page My Portfolio
 */
global $wp_query, $ae_post_factory, $post, $current_user, $user_ID;
get_header();
$user = mJobUser::getInstance();
$user_data = $user->convert($current_user->data);
$user_role = ae_user_role($current_user->ID);

// Convert profile
$profile_obj = $ae_post_factory->get('mjob_profile');
// Get user profile id
$profile_id = get_user_meta($user_ID, 'user_profile_id', true);

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
                'post_title' => $user_data->display_name,
                'post_author' => $user_data->ID
            ));
            update_user_meta($user_data->ID, 'user_profile_id', $profile_id);
            $profile = $profile_obj->convert(get_post($profile_id));
        }
    }
    echo '<script type="text/json" id="mjob_profile_data" >'.json_encode($profile).'</script>';
}
// Get profile infomation
$description = !empty($profile->profile_description) ? $profile->profile_description : __('There is no content', 'enginethemes');
$payment_info = !empty($profile->payment_info) ? $profile->payment_info : __('There is no content', 'enginethemes');
$billing_full_name = !empty($profile->billing_full_name) ? $profile->billing_full_name : __('There is no content', 'enginethemes');
$billing_full_address = !empty($profile->billing_full_address) ? $profile->billing_full_address : __('There is no content', 'enginethemes');
$billing_country = !empty($profile->billing_country) ? $profile->billing_country : '';
$billing_vat = !empty($profile->billing_vat) ? $profile->billing_vat : __('There is no content', 'enginethemes');

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

$my_portfolios=get_all_portfolio(get_current_user_id());

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

$porfolio_items=get_portfolio_images(get_current_user_id(),$chosen_port);

$portfolio_video_items=get_portfolio_videos(get_current_user_id(),$chosen_port);


if(!empty($portfolio_video_items)) 
{
    $counted_videos=count($portfolio_video_items);
}
else
{
    $counted_videos=0;
}

if(!empty($porfolio_items)) 
{
    $counted_items=count($porfolio_items);
}
else
{
    $counted_items=0;
}

$banner_url_image=get_post_meta($profile->ID,'banner_image_url',true);
if(!$banner_url_image)
{
    $banner_url_image=DEFAULT_BANNER_URL_PORTFOLIO;
}


?>
<div class="topoverlay">
    <h1 class="uploadingText" id="uploadingProgressDisplay"></h1>
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

                    <a id="port-update-banner-btn" class="port-update-banner-btn" href="javascript:void(0)">Edit Banner <i class="fa fa-camera"></i></a>
                </div>
                <img id="port-banner-image" src="<?php echo $banner_url_image; ?>">
            </div>

            <div class="port-head-title-info">
                <p>
                    <?php echo $portfolio_title; ?> <span class="counted-images"><?php echo $counted_items; ?> images | <?php echo $counted_videos; ?> videos</span>
                    <?php if($chosen_port_info): ?>
                        <span class="portfolio-description">Description: <?php echo $chosen_port_info->post_content; ?></span>
                       
                    <?php endif; ?>
                </p>
            </div>

            <div class="action-buttons-area">
            <button id="creat-portfolio" class="btn btn-default edit-portfolio-btn open-add-port-modal">Create Portfolio <i class="fa fa-plus"></i></button>
            <?php if($chosen_port_info): ?>
           
                        <button id="edit-portfolio" class="btn btn-default edit-portfolio-btn">Edit Portfolio <i class="fa fa-pencil"></i></button>
                        <button id="add-images-portfolio-area" class="btn btn-default edit-portfolio-btn"><span id="add-images-portfolio">Add Images <i class="fa fa-image"></i></span></button>
                        <button id="add-video-portfolio-area" class="btn btn-default edit-portfolio-btn"><span id="add-videos-portfolio">Add Videos <i class="fa fa-video-camera"></i></span></button>
                        <input type="hidden" id="video_upload_single" name="video_upload_single" value="<?php echo wp_create_nonce('custom_video_upload_nonce'); ?>">                 
                        <!-- info for uploading single images -->
                        <input type="hidden" name="single_upload_images_nonce" id="single_upload_images_nonce" value="<?php echo wp_create_nonce('single_upload_images_nonce'); ?>">
                        <input type="hidden" name="port_id_single_upload" id="port_id_single_upload" value="<?php echo $chosen_port_info->ID; ?>">
                        <!--end info -->

                        <button id="bulk-select-images" data-bulk-select-status="false" class="btn btn-default edit-portfolio-btn">Bulk Select <i class="fa fa-check-square"></i></button>

                        <button data_portfolio_id="<?php echo $chosen_port_info->ID; ?>" data_delete_images_nonce="<?php echo wp_create_nonce('data_delete_images_nonce'); ?>" id="btn-delete-images-port" class="btn btn-danger edit-portfolio-btn">Delete media <i class="fa fa-trash-o"></i></button>

                        <button  id="btn-delete-portfolio" class="btn btn-danger edit-portfolio-btn">Delete Portfolio <i class="fa fa-trash-o"></i></button>
           
            <?php endif; ?>
            </div>
           
            <div class="port-title-list">
                <a class="<?php if($chosen_port=='all') echo 'port-active'; ?>" href="<?php echo site_url('my-portfolio'); ?>">All</a>
                <?php if(!empty($my_portfolios)): ?>
                    <?php foreach($my_portfolios as $my_portfolio) : ?>
                        <?php 
                        if($chosen_port==$my_portfolio->post_name)    
                            $active_class='port-active';
                        else
                            $active_class='';
                        ?>
                        <a class="<?php echo $active_class; ?>"  href="<?php echo site_url('my-portfolio').'?port='.$my_portfolio->post_name; ?>"><?php echo $my_portfolio->post_title; ?></a>
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
                            <p class="chosen-image-select"><a data-item-type="image" class="image-selector-port" data_select_status="false" data_attachment_id_select="<?php echo $porfolio_item->ID; ?>" href="javascript:void(0)">Select</a></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
               <!-- <a href="#">
                    <img src="<?php echo get_stylesheet_directory_uri().'/assets/img/view1.png'; ?>">
                </a> -->
               
            </div>

            <div class="port-video-collection">                
             <?php if(!empty($portfolio_video_items)): ?>
                    <?php foreach($portfolio_video_items as $porfolio_video_item) : ?>
                        <?php                         
                        $video_url=wp_get_attachment_url($porfolio_video_item->ID);                                                                       
                        ?>
                        <?php if($video_url): ?>                       
                            <div class="port-video-wrapper">
                            <button data-mime-type="<?php echo $porfolio_video_item->post_mime_type;  ?>" data-video-url="<?php echo $video_url; ?>" class="show_central_video_player"><i class="fa fa-play"></i></button>
                                <video disablepictureinpicture class="portfolio-item-video-player" playsinline controls>                                                                        
                                    <source src="<?php echo $video_url; ?>" type="<?php echo $porfolio_video_item->post_mime_type;  ?>" />    
                                </video>
                                <p class="chosen-image-select"><a class="image-selector-port" data-item-type="video" data_select_status="false" data_attachment_id_select="<?php echo $porfolio_video_item->ID; ?>" href="javascript:void(0)">Select</a></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                    <?php echo $user_data->display_name ; ?>
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
                    <a href="<?php echo get_author_posts_url(get_current_user_id()); ?>" class="port-view-pf-btn">
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
                        
                    <a class="port-add-portfolio-btn" href="<?php echo get_author_posts_url(get_current_user_id()); ?>">
                            View Profile <i class="fa fa-user"></i>
                    </a>                 
                    
                </div>
            </div>
        </div>

    </div>
</div>
<!-- custom modal for add portfolio -->
<div class="custom-modal-add-portfolio" id="custom-modal-add-portfolio">
   <div class="custom-modal-add-portfolio-content">
         <div class="close-modal-add-portfolio">
            <p class="add-port-modal-title">Create Portfolio</p>
            <span class="close-modal-port-icon">&times;</span>
         </div>  
         <div class="custom-form-add-port">
            <form id="create-port-form" action="" enctype="multipart/form-data">
                <input type="hidden" id="create_port_nonce" name="create_port_nonce" value="<?php echo wp_create_nonce('create_port_nonce'); ?>">                             
                <input type="hidden" name="action" id="action" value="createPortfolio">
                <input type="hidden" id="port_upload_images_none" name="port_upload_images_none" value="<?php echo wp_create_nonce('port_upload_images_none'); ?>">                                 
                
                <input type="hidden" id="port_upload_video_nonce" name="port_upload_video_nonce" value="<?php echo wp_create_nonce('custom_video_upload_nonce'); ?>">                 

                <div class="form-group">
                    <label for="port_title">Portfolio Title</label>
                    <input required type="text" class="form-control" id="port_title" name="port_title">
                </div>

                <div class="form-group">
                    <label for="port_description">Short Description</label>
                    <input type="text" class="form-control" id="port_description" name="port_description">
                </div>

                <div class="radio">
                    <input class="custom-radio-port" type="radio" id="port_public" name="port_public" value="public" checked>
                    <span>Public</span>
                    <input class="custom-radio-port radio-private" type="radio"  id="port_public" name="port_public" value="private">
                    Private
                </div>  
                
                <div class="form-group upload-images-port-area">
                
                     <button type="button" id="upload-images-port-area" class="port-upload-images-btn">
                         <span id="port-upload-images-btn">Upload Images <i class="fa fa-upload"></i> </span> 
                     </button>
                    
                     <button type="button" id="upload-videos-port-area" class="port-upload-images-btn">
                         <span id="port-upload-videos-btn">Upload Videos <i class="fa fa-video-camera"></i> </span> 
                     </button>

                </div>

                <div class="uploadprogressBar">

                </div>

                <div class="port-uploaded-images-area">

                </div>

                <div class="video-upload-progress-port-modal">

                </div> 

                <div class="port-uploaded-videos-area">

                </div>

                <div class="form-group port-submit-area">
                        <button type="submit" class="btn btn-primary create-portfolio-btn">Create Portfolio</button>
                        <button type="button" class="btn btn-danger close-modal-port-icon">Close</button>
                </div>

    
            </form>
         </div>                         
   </div>                                 
</div>
<!-- end custom modal -->

<!-- edit modal portfolio -->
<?php if($chosen_port_info): ?>
<div class="custom-modal-add-portfolio" id="custom-modal-edit-portfolio">
   <div class="custom-modal-add-portfolio-content">
         <div class="close-modal-add-portfolio">
            <p class="add-port-modal-title">Edit Portfolio</p>
            <span id="close-modal-edit-port-iconx">&times;</span>
         </div>  
         <div class="custom-form-add-port">
            <form id="edit-port-form" action="">
                <input type="hidden" id="edit_port_nonce" name="edit_port_nonce" value="<?php echo wp_create_nonce('edit_port_nonce'); ?>">                             
                <input type="hidden" name="action" id="action" value="editPortfolio">                
                <input type="hidden" name="port_id" id="port_id" value="<?php echo $chosen_port_info->ID; ?>">     
                <div class="form-group">
                    <label for="port_title">Portfolio Title</label>
                    <input required type="text" class="form-control" id="edit_port_title" name="edit_port_title" value="<?php echo $chosen_port_info->post_title; ?>">
                </div>

                <div class="form-group">
                    <label for="port_description">Short Description</label>
                    <input type="text" class="form-control" id="edit_port_description" name="edit_port_description" value="<?php echo $chosen_port_info->post_content; ?>">
                </div>

                <div class="radio">
                    <input class="custom-radio-port" type="radio" id="edit_port_public" name="edit_port_public" value="public" <?php if(get_post_meta($chosen_port_info->ID,'public_option','true')=='public') echo 'checked'; ?>>
                    <span>Public</span>
                    <input class="custom-radio-port radio-private" type="radio"  id="edit_port_public" name="edit_port_public" value="private" <?php if(get_post_meta($chosen_port_info->ID,'public_option','true')=='private') echo 'checked'; ?>>
                    Private
                </div>  
                                 

                <div class="form-group port-submit-area">
                        <button type="submit" class="btn btn-primary create-portfolio-btn">Save Portfolio</button>
                        <button type="button" id="close-modal-edit-port-icon" class="btn btn-danger">Close</button>
                </div>

    
            </form>
         </div>                         
   </div>                                 
</div>
<?php endif; ?>
<!-- end edit modal portfolio -->

<!-- delete portfolio modal  -->
<?php if($chosen_port_info): ?>
<div class="modal-confirm-delete-portfolio">
    <div class="modal-confirm-delete-portfolio-content">
            <h3>Are you sure want to delete this portfolio ?</h3>
            <div class="delete-modal-action-buttons">
                <form id="delete-portfolio-modal" action="">
                    <input type="hidden" value="deletePortfolio" name="action" id="action">
                    <input type="hidden" value="<?php echo $chosen_port_info->ID; ?>" name="delete_portfolio_id" id="delete_portfolio_id">
                    <input type="hidden" value="<?php echo wp_create_nonce('delete_port_nonce'); ?>" name="delete_port_nonce" id="delete_port_nonce">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button id="cancel-delete-portfolio-btn" type="button" class="btn btn-default">Cancel</button>
                </form>
            </div>
    </div>
</div>
<?php endif; ?>
<!-- end  portfolio modal -->
<?php 
get_footer();
?>

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