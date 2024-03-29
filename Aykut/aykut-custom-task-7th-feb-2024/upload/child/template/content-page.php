<?php
$absolute_url = mje_get_full_url( $_SERVER );
$title = get_theme_mod('post_job_title') ? get_theme_mod('post_job_title') : __('Get your stuffs done from $5', 'enginethemes');
//custom code here
if(is_post_type_archive('mjob_post') || is_tax('mjob_category') || (is_single() && 'mjob_post'==get_post_type()) )
$title='Ghostwriter werden?';
//end custom

        //custom code here 7th Feb 2024
        $profile_id=get_user_meta(get_current_user_id(),'user_profile_id',true);
        $registered_seller=get_post_meta($profile_id,'registered_seller',true);
        if($registered_seller)
        {
            $is_registered_seller=true;
        }
        else
        {
            $is_registered_seller=false;
        }
        //end

if( is_mje_submit_page() ){
    $post_link = '#';
}
else {
    $post_link = et_get_page_link('post-service') . '?return_url=' . $absolute_url;
}
?>

<?php if( ! get_theme_mod( 'mje_disable_banner') ) : ?>
<div class="banner">
    <div class="container">
        <div class="search-slider float-center job-items-title">
            <h2 class="banner-title"><?php echo $title; ?></h2>
            <a href="<?php echo $post_link; ?>" class="btn-post hvr-sweep-to-left waves-effect waves-light"><p class="name-button-post">
                <?php 
                    if($is_registered_seller==true)
                        {
                            echo 'Service posten';
                        }
                        else
                        {
                            echo 'Ghostwriter werden';
                        }
                 ?>
                    
                </p> <span class="cirlce-plus"><i class="fa fa-plus"></i></span></a>
        </div>
    </div>
    <div class="header-images">
        <?php
        $img_url = ae_get_option('post_job_banner');
        $img_theme_mod = get_theme_mod('post_job_banner');
        if(!empty($img_url)) {
            $img_url = $img_url['full']['0'];
            ?>
            <img src="<?php echo $img_url; ?>" alt="<?php _e('Post a mJob banner', 'enginethemes'); ?>">
            <?php
        } elseif(false === $img_theme_mod) {
            $img_url = get_template_directory_uri() . '/assets/img/banner.png';
            ?>
            <img src="<?php echo $img_url; ?>" alt="<?php _e('Post a mJob banner', 'enginethemes'); ?>">
            <?php
        } else {
            $img_url = "";
        }

        ?>
    </div>
</div>
<?php endif; ?>