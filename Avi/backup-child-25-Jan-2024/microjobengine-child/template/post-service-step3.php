<?php
if (isset($_REQUEST['id'])) {
	$post = get_post($_REQUEST['id']);
	if ($post) {
		global $ae_post_factory;
		$post_object = $ae_post_factory->get($post->post_type);
		echo '<script type="data/json"  id="edit_postdata">' . json_encode($post_object->convert($post)) . '</script>';
	}

}
if (isset($_GET['return_url'])) {
	$return = $_GET['return_url'];
} else {
	$return = home_url();
}
$post_title = $auto_price = $time_delivery = $post_content = '';
if(DEVELOP_MODE){
    $post_title = 'I can do generator title at '. date('l jS \of F Y h:i:s A');
    $auto_price = 10;
    $time_delivery = 3;
    $post_content = 'Generator random post content at '.date('l jS \of F Y h:i:s A');
}
?>
<div class="step-wrapper step-post" id="step-post">
    <form class="post-job  post et-form" id="" >
        <div class="form-group clearfix mje-mjob-title-field">
            <div class="input-group">
                <label for="post_title" class="input-label"><?php _e('Job name', 'enginethemes');?></label>
                <input type="text" class="input-item input-full" name="post_title" value="<?php echo $post_title;?>" required>
            </div>
        </div>

        <div class="form-group row clearfix <?php echo ('1' == ae_get_option('custom_price_mode') || is_super_admin($user_ID)) ? 'has-price-field' : ''; ?>">
            <?php if ('1' == ae_get_option('custom_price_mode') || is_super_admin($user_ID)): ?>
                <?php
                $min_price = ae_get_option('mjob_min_price') ? absint(ae_get_option('mjob_min_price')) : 5;
                $max_price = ae_get_option('mjob_max_price') ? absint(ae_get_option('mjob_max_price')) : 15;
                $currency_code = ae_currency_code(false);
            ?>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 clearfix mje-mjob-budget-field">
                <div class="input-group">
                <?php
                if ( is_super_admin($user_ID) && '1' != ae_get_option('custom_price_mode') ) { ?>
                    <label for="et_budget"><?php printf(__('Price (%s)', 'enginethemes'), $currency_code);?></label>
                    <input type="number" name="et_budget" class="input-item et_budget field-positive-int time-delivery" min="1" pattern="[-+]?[0-9]*[.,]?[0-9]+" value="<?php echo $auto_price;?>" required>
                <?php } else { ?>
                    <label for="et_budget"><?php printf(__('Price (%s)', 'enginethemes'), $currency_code);?></label>
                    <input type="number" name="et_budget" placeholder="<?php printf(__('%s - %s', 'enginethemes'), mje_format_price($min_price, "", true, false), mje_format_price($max_price, "", true, false));?>" class="input-item et_budget field-positive-int time-delivery" min="<?php echo $min_price ?>" max="<?php echo $max_price ?>" pattern="[-+]?[0-9]*[.,]?[0-9]+" required>
                <?php } ?>
                </div>
            </div>
            <?php endif ?>

            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 delivery-area mje-mjob-delivery-field">
                <div class="input-group delivery-time">
                    <label for="time_delivery"><?php _e('Time of delivery (Day)', 'enginethemes');?></label>
                    <input type="number" name="time_delivery" value="<?php echo $time_delivery;?>" class="input-item time-delivery" min="0">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 category-area mje-mjob-category-field">
                <div class="input-group">
                    <label for="mjob_category"><?php _e('Category', 'enginethemes');?></label>
                    <?php
                    ae_tax_dropdown('mjob_category',
                    	array('attr' => 'data-chosen-width="100%" data-chosen-disable-search=""  data-placeholder="' . __("Choose categories", 'enginethemes') . '"',
                    		'class' => 'chosen chosen-single tax-item required',
                    		'hide_empty' => false,
                    		'hierarchical' => true,
                    		'id' => 'mjob_category',
                    		'show_option_all' => false,
                    	)
                    );?>
                </div>
            </div>
        </div>

        <div class="form-group mje-mjob-des-field">
            <div class="input-group">
                <label class="mb-20"><?php _e('Description', 'enginethemes')?></label>
                <?php wp_editor($post_content, 'post_content', ae_editor_settings());?>
            </div>
        </div>

        <div class="form-group group-attachment gallery_container mje-mjob-gallery-field" id="gallery_container">
            <div class="input-group">
                <label class="mb-20"><?php _e('Gallery', 'enginethemes');?></label>
                <div class="outer-carousel-gallery">
                    <div class="img-avatar carousel-gallery">
                        <img width="100%" src="<?php echo TEMPLATEURL ?>/assets/img/image-avatar.jpg" alt="">
                        <div class="upload-description">
                            <i class="fa fa-picture-o"></i>
                            <p><?php _e(' Up to 5 pictures whose each minimum size is 768 x 435px', 'enginethemes');?></p>
                            <p><?php _e('Select one picture for your featured image', 'enginethemes');?></p>
                        </div>
                        <input type="hidden" class="input-item show" name="et_carousels" value="" />
                    </div>
                </div>
                <div class="attachment-image clearfix">
                    <ul class="carousel-image-list image-list" id="image-list">

                    </ul>
                    <span class="image-upload carousel_container" id="carousel_container">
                        <span for="file-input" class="carousel_browse_button" id="carousel_browse_button">
                            <a class="add-img"><img src="<?php echo get_template_directory_uri() ?>/assets/img/icon-plus.png" alt=""></a>
                        </span>
                    </span>

                    <span class="et_ajaxnonce" id="<?php echo wp_create_nonce('ad_carousels_et_uploader'); ?>"></span>
                </div>
            </div>
        </div>
        <?php do_action('input_address_field');?>
		<div class="form-group clearfix mje-mjob-video-field">
            <label><?php _e('Video', 'enginethemes');?></label>
            <input type="text" class="input-item form-control text-field" id="video_meta" placeholder="<?php _e("Add link from Youtube, Vimeo or .MP4 ", 'enginethemes');?>" name="video_meta"  autocomplete="off" spellcheck="false" >
            <ul class="skills-list" id="skills_list"></ul>
        </div>

        <!-- tiktok custom code -->

        <div class="form-group clearfix mje-mjob-video-field">
            <label><?php _e('Tiktok', 'enginethemes');?></label>
          <!--  <span data-toggle="modal" data-target="#TiktokvideoModal"><i style="font-size:18px;margin-left:5px;" class="fa fa-info-circle"></i></span> -->
            <span data-toggle="tooltip" data-placement="top" title="Please note that while you can upload a TikTok video link to your gig, it won't serve as the main gig image. It will be visible in the gig's images section,  However, the main gig image or video should come from YouTube link or be a static image."><i style="font-size:18px;margin-left:5px;" class="fa fa-info-circle"></i></span>
            <input type="text" class="input-item form-control text-field" id="tiktok_link" placeholder="<?php _e("Add Tiktok video link", 'enginethemes');?>" name="tiktok_link"  autocomplete="off" spellcheck="false" >            
        </div>

        <!-- end tiktok custom code -->
                
        <div class="form-group clearfix mje-mjob-extra-field">
            <label class="mb-20"><?php _e('Extra services', 'enginethemes');?></label>
            <div class="mjob-extras-wrapper">
            </div>
            <div class="add-more">
                <a href="#" class="mjob-add-extra-btn"><?php _e('Add extra', 'enginethemes');?><span class="icon-plus"><i class="fa fa-plus"></i></span></a>
            </div>
        </div>
        <div class="form-group skill-control mje-mjob-skill-field">
            <label><?php _e('Tags', 'enginethemes');?></label>
            <?php
            if( wp_is_mobile () ){ ?>
                <textarea class="form-control text-field  skill et-suggest-skill" id="skill"  placeholder ="<?php _e('Enter microjob tags','enginethemes');?>"></textarea>
                <span class="add-skill"><i class="fa fa-sign-in"></i></span>
            <?php } else { ?>
                <input type="text" class="form-control text-field skill et-suggest-skill" id="skill" placeholder="<?php _e("Enter microjob tags", 'enginethemes');?>" name=""  autocomplete="off" spellcheck="false" >
            <?php } ?>
            <ul class="skills-list" id="skills_list"></ul>


        </div>
        <div class="form-group skill-control">
            <label for="opening_message"><?php _e('Opening message', 'enginethemes')?> <i class="fa fa-question-circle popover-opening-message" aria-hidden="true"></i></label>
            <p class="note-message">
                <?php _e('Opening message is automatically displayed as your first message in the order detail page.', 'enginethemes');?>
            </p>
            <textarea name="opening_message" class="input-item"></textarea>
        </div>
        <?php  //do_action( 'list_featured_package'  ); // @since 1.8.3 ?>
        <div class="form-group">
            <button class="<?php mje_button_classes(array('btn-save', 'waves-effect', 'waves-light'))?>" type="submit"><?php _e('SAVE', 'enginethemes');?></button>
            <a href="<?php echo $return; ?>" class="btn-discard"><?php _e('DISCARD', 'enginethemes');?></a>
            <input type="hidden" class="input-item post-service_nonce" name="_wpnonce" value="<?php echo de_create_nonce('ae-mjob_post-sync'); ?>" />
        </div>
    </form>
</div>

<!-- Modal -->
<div id="TiktokvideoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body text-center">
        <h4 style="margin-top:10px;">Please right click on the Tiktok video and click to copy link</h4>
    
        <div class="mx-auto" style="margin-top:10px;margin-bottom:10px;">
            <img style="border:1px solid #EEECF1;border-radius:5px;" src="<?php  echo get_stylesheet_directory_uri().'/assets/img/ttguide.png'; ?>">
        </div>     
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- end Modal -->