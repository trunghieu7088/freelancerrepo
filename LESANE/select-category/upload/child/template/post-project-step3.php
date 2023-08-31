<?php
    global $user_ID;
    $step = 3;
    $class_active = '';
    $is_post_free = is_post_project_free();
    if( $is_post_free ) {
        $step--;
        $class_active = 'active';
    }
    if($user_ID) $step--;
    $post = '';
    $current_skills = '';
//custom code here
$parent_categories_block=apply_filters('return_parent_categories','none');
//end
?>
<style>
#custom_media_image_category:hover
{
    cursor:pointer;
}

.selected_image_item
{
    background-color:#D4FAFA;
    border-radius:10px;
    border-color:#AFD5F0;
    border-style:solid;    

}
</style>
<div id="fre-post-project-2 step-post" class="fre-post-project-step step-wrapper step-post <?php echo $class_active;?> template\post-project-step3.php">
    <?php
    	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        if( $id ) {
            $post = get_post($id);
            if($post) {
                global $ae_post_factory;
                $post_object = $ae_post_factory->get($post->post_type);
                $post_convert = $post_object->convert($post);
                echo '<script type="data/json"  id="edit_postdata">'. json_encode($post_convert) .'</script>';
            }
            //get skills
            $current_skills = get_the_terms( $_REQUEST['id'], 'skill' );
        }
        if( ! is_acti_fre_membership() ){
            if( !$is_post_free ) {
                $total_package = ae_user_get_total_package($user_ID); ?>
                <div class="fre-post-project-box">
                    <div class="step-change-package show_select_package">
                        <p class="package_title"><i class="fa fa-plus primary-color" aria-hidden="true"></i>&nbsp;<?php _e('You are selecting the package:', ET_DOMAIN);?> <strong></strong></p>
                        <p class="package_description pdl-10"></p>
                        <p class="pdl-10"><?php _e('The number of posts included in this package will be added to your total posts after this project is posted.',ET_DOMAIN) ?></p>
                        <br>

                            <?php // printf(__('1The premium package you purchased has <span class="post-number">%s</span> post(s) left', ET_DOMAIN), $total_package); ?>
                        </p>
    	                <?php
    	                ob_start();
    	                ae_user_package_info($user_ID);
    	                $package = ob_get_clean();

    	                if($package != '') { ?>
                        <p><i class="fa fa-check primary-color" aria-hidden="true"></i>&nbsp;<?php _e('Your purchased package details.',ET_DOMAIN);?></p>
                        <p><?php
    		                echo $package;
    	                }
    	                ?>
                        <a class="fre-btn-o fre-post-project-previous-btn fre-btn-previous primary-color" href="#"><?php _e('Change package', ET_DOMAIN);?></a>
                    </div>
                    <div class="step-change-package show_had_package" style="display:none;">

    	                    <?php //printf(__('2The premium package you purchased has <span class="post-number">%s</span> post(s) left.', ET_DOMAIN), $total_package); ?>
                        </p>
                        <?php

                            if($package != '') { ?>
                              <p><i class="fa fa-check primary-color" aria-hidden="true"></i>&nbsp;<?php _e('Your purchased package details.',ET_DOMAIN);?></p>
                                <p>
                            <?php
                                echo $package;
                            }
                        ?>
                        <p><em><?php _e('You are choosing a package that still available to post or pending so can not buy again. If you want to get more posts, you can directly move on the posting project plan by clicking the next "Add more" button.', ET_DOMAIN);?></em></p>
                        <a class="fre-btn-o fre-post-project-previous-btn fre-btn-previous" href="#"><?php _e('Add more', ET_DOMAIN);?></a>
                    </div>
                </div>
        <?php } } else { do_action('fre_above_post_project'); } ?>
    <div class="fre-post-project-box">
        <form class="post" role="form">
            
                               
            <div class="step-post-project" id="fre-post-project" style="padding-right: 0px !important;">
                <h2><?php _e('Your Project Details', ET_DOMAIN);?></h2>
                  <!-- custom code here -->
                <div id="category-selector-id" class="container-fluid text-center" style="margin-bottom:25px;">
                    <h2 class="text-center" style="margin-bottom: 10px;"> <strong>Project Categories</strong></h2>
                     <h4 id="text-screen1" style="margin-bottom: 25px;">Choisissez les catégories de métier liées à votre mission</h4>
                     <div class="row text-center" style="">
                       <?php 
                    
                        foreach($parent_categories_block as $item)
                        {
                            $args_img=array('name'=>'custom_media_image_category','id'=>'custom_media_image_category','class'=>'img-responsive center-block custom-image-hover','data-category-id'=>$item->term_id);
                            
                            
                            echo ' <div class="col-sm-3 custom-image-wrapper" style="margin:15px 0px;max-height:200px !important;">';
                            echo '<div class="image-item" style="padding-bottom:10px;padding-top:10px;">';
                            $image_category_id=get_term_meta($item->term_id,'category_image_id',true);
                            $image_category=wp_get_attachment_image($image_category_id,'thumbnail',false, $args_img);
                         //   echo ' <img src="'.$image_category_link.'" class="img-responsive center-block">';
                            if(!empty($image_category))
                            {
                                 echo $image_category;
                            }
                            else
                            {
                                $default_img_cat='<img src="'.get_stylesheet_directory_uri().'/assets/img/default-img.png" loading="lazy" id="custom_media_image_category" name="custom_media_image_category" class="img-responsive center-block custom-image-hover"  sizes="(max-width: 150px) 100vw, 150px" width="150" height="150" data-category-id="'.$item->term_id.'">';
                              echo  $default_img_cat;
                            }
                           
                            echo  '<h5 class="custom-image-hover" data-check-category-id="'.$item->term_id.'" data-category-id="'.$item->term_id.'">';
                            // echo '<img style="height:25px;width:25px;" src="'.get_stylesheet_directory_uri().'/assets/img/check-symbol.png">';
                             echo $item->name;
                            echo '</h5>';
                            
                            echo '</div>';

                            echo '</div>';
   
                        }
                       ?>
              
                   
                    
                </div>
               <input type="button" class="fre-btn" style="margin-top:15px;margin-bottom: 10px;" value="Next" id="next-to-subcategories">
            </div>
               <!-- end -->
               

            <div class="fre-input-field" id="sub-categories-selector-area" style="display:none;">
                    <h3 id="text-screen2">Please choose the sub-categories</h3>
                    <label class="fre-field-title" for="sub-categories-selector">Sub-Categories</label>
                    <select class="sub-categories-selector" id="sub-categories-selector" multiple="true">
                     

                    </select>
                       <input type="button" id="back-to-parent" class="fre-btn" value="Previous" style="margin-top:10px;">
                    <input type="button" id="next-form-btn" class="fre-btn" value="Next" style="margin-top:10px;">

            </div>

            <div class="custom-step" style="display:none;">
                <input type="button" id="back-to-sub" value="Previous" class="fre-btn" style="margin-bottom: 15px;">
                <div class="fre-input-field" style="display:none;">
                    <label class="fre-field-title" for="project_category"><?php _e('What categories do your project work in?', ET_DOMAIN);?></label>
                    <?php
                        $cate_arr = array();
                        if(!empty($post_convert->tax_input['project_category'])){
                            foreach ($post_convert->tax_input['project_category'] as $key => $value) {
                                $cate_arr[] = $value->term_id;
                            };
                        }
                        ae_tax_dropdown( 'project_category' ,
                          array(  'attr' => 'data-chosen-width="100%" data-chosen-disable-search="" multiple data-placeholder="'.sprintf(__("Choose maximum %s categories", ET_DOMAIN), ae_get_option('max_cat', 5)).'"',
                                  'class' => 'fre-chosen-category',
                                  //'class' => 'fre-chosen-multi',
                                  'hide_empty' => false,
                                  'hierarchical' => true ,
                                  'id' => 'project_category' ,
                                  'show_option_all' => false,
                                  'selected'        => $cate_arr,
                              )
                        );
                    ?>
                </div>
                <div class="fre-input-field">
                    <label class="fre-field-title" for="fre-project-title"><?php _e('Your project title', ET_DOMAIN);?></label>
                    <input class="input-item text-field" id="fre-project-title" type="text" name="post_title">
                </div>
                <div class="fre-input-field">
                    <label class="fre-field-title" for="fre-project-describe"><?php _e('Describe what you need done', ET_DOMAIN);?></label>
                    <?php wp_editor( '', 'post_content', ae_editor_settings() );  ?>
                    <?php if(MINLENGTH_CONTENT){?>
                        <textarea  name="minlength_content" id="minlength_content"></textarea>
                        <!-- <div class="message errorMinLeng"></div> -->
                    <?php } ?>
                </div>
                <div class="fre-input-field" id="gallery_place">
                    <label class="fre-field-title" for=""><?php _e('Attachments (optional)', ET_DOMAIN);?></label>
                    <div class="edit-gallery-image" id="gallery_container">
                        <ul class="fre-attached-list gallery-image carousel-list" id="image-list"></ul>
                        <div  id="carousel_container">
                            <a href="javascript:void(0)" style="display: block"
                               class="img-gallery fre-project-upload-file secondary-color" id="carousel_browse_button">
                                <?php _e("Upload Files", ET_DOMAIN); ?>
                            </a>
                            <span class="et_ajaxnonce hidden" id="<?php echo wp_create_nonce( 'ad_carousels_et_uploader' ); ?>"></span>
                        </div>
                        <p class="fre-allow-upload"><?php _e('(Upload maximum 5 files with extensions including png, jpg, pdf, xls, and doc format)', ET_DOMAIN);?></p>
                    </div>
                </div>
                <div class="fre-input-field">
                    <label class="fre-field-title" for="skill"><?php _e('What skills do you require?', ET_DOMAIN);?></label>
                    <?php
                        $c_skills = array();
                        if(!empty($post_convert->tax_input['skill'])){
                            foreach ($post_convert->tax_input['skill'] as $key => $value) {
                                $c_skills[] = $value->term_id;
                            };
                        }
                        ae_tax_dropdown( 'skill' , array(  'attr' => 'data-chosen-width="100%" data-chosen-disable-search="" multiple data-placeholder="'.sprintf(__("Choose maximum %s skills", ET_DOMAIN), ae_get_option('fre_max_skill', 5)).'"',
                                            'class' => ' fre-chosen-skill required',
                                            //'class' => ' fre-chosen-multi required',
                                            'hide_empty' => false,
                                            'hierarchical' => true ,
                                            'id' => 'skill' ,
                                            'show_option_all' => false,
                                            'selected' => $c_skills
                                    )
                        );
                    ?>
                </div>
                <div class="fre-input-field">
                    <label class="fre-field-title" for="project-budget"><?php _e('Your project budget', ET_DOMAIN);?></label>
                    <div class="fre-project-budget">
                        <input id="project-budget" step="5" required type="number" class="input-item text-field is_number numberVal" name="et_budget" min="1">
                        <span><?php echo fre_currency_sign(false);?></span>
                    </div>
                </div>
                <div class="fre-input-field">
                    <label class="fre-field-title" for="project-location"><?php _e('Location (optional)', ET_DOMAIN);?></label>
                    <?php
                        ae_tax_dropdown( 'country' ,array(
                                'attr'            => 'data-chosen-width="100%" data-chosen-disable-search="" data-placeholder="'.__("Choose country", ET_DOMAIN).'"',
                                'class'           => 'fre-chosen-single',
                                'hide_empty'      => false,
                                'hierarchical'    => true ,
                                'id'              => 'country',
                                'show_option_all' => __("Choose country", ET_DOMAIN)
                            )
                        );
                    ?>
                </div>
                <?php
                    // Add hook: add more field
                    echo '<ul class="fre-custom-field">';
                    do_action( 'ae_submit_post_form', PROJECT, $post );
                    echo '</ul>';
                ?>
                <div class="fre-post-project-btn">
                    <button class="fre-btn fre-post-project-next-btn primary-bg-color" type="submit"><?php _e("Submit Project", ET_DOMAIN); ?></button>
                </div>
            </div>
        </div>

        </form>
    </div>
</div>
<!-- Step 3 / End -->
<script type="text/javascript">
(function ($) {
  $(document).ready(function () {
    var selected_categories_id=[];
    var check_img_path='<?php echo '<img style="height:25px;width:25px;diplay:inline;" src="'.get_stylesheet_directory_uri().'/assets/img/check-new.png">'; ?>'; 
     $('#sub-categories-selector').chosen({width: "100%"}).change(function(event){
         
            let project_subcategory_list=$(this).val();
            //project_subcategory_list.push(selected_categories_id);
           let final_categories_list=project_subcategory_list.concat(selected_categories_id);
            $("#project_category").val(final_categories_list);
            $('#sub-categories-selector').trigger("chosen:updated");
           // console.log(final_categories_list);
        }); 

    $('.custom-image-hover').click(function(){
        //alert($(this).attr('data-category-id'));
        //parent_category_selected_id=$(this).attr('data-category-id');
        if(selected_categories_id.includes($(this).attr('data-category-id')) == false)
        {
            selected_categories_id.push($(this).attr('data-category-id'));
          
            // $("[data-check-category-id="+$(this).attr('data-category-id')+"]").prepend(check_img_path);
          /*  $(this).parent().css('background-color','#D4FAFA');
            $(this).parent().css('border-radius','10px');            
            $(this).parent().css('border-color','#AFD5F0');
            $(this).parent().css('border-style','solid');
            */
            $(this).parent().addClass('selected_image_item');
        }
        else
        {
            
            var index = selected_categories_id.indexOf($(this).attr('data-category-id'));
            if (index !== -1) 
            {
                selected_categories_id.splice(index, 1);
            }
           // $("[data-check-category-id="+$(this).attr('data-category-id')+"]").find('img:first').remove();
            $(this).parent().removeClass('selected_image_item');
        }
      
      
       // console.log(selected_categories_id);

    });

    $('#next-to-subcategories').click(function(){
            if(selected_categories_id.length <= 0)
            {
                alert('Please select the categories');
                 AE.pubsub.trigger('ae:notification', {
                        msg: 'Veuillez sélectionner les catégories',
                        notice_type: 'error',
                    });
            }
            else
            {
                $.ajax({

                type: "post",
                url: ae_globals.ajaxURL,
                dataType: 'json',
                data: {
                    action:'get_sub_categories',
                    parent_category_id: selected_categories_id,
                },
                success: function (response) { 
                   
                   $('#sub-categories-selector').html(response.data.toString());
                   // console.log(response.data);
                    $('#sub-categories-selector').trigger("chosen:updated");
                   
                    $('#sub-categories-selector-area').css('display','block');
                    
                    //set parent items vao selector neu user khong chon sub categories thi van work
                     $("#project_category").val(selected_categories_id);

                    //hide parent selector after choosing
                    $('#category-selector-id').css('display','none');
 
                    $('#text-screen1').css('display','none');
                    }
                });
            }
    });

    $('#next-form-btn').click(function(){
        $('.custom-step').css('display','block');
        $('#sub-categories-selector-area').css('display','none');
        $('.step-post-project').css('padding-right','310px');
    });

    $('#back-to-parent').click(function(){
          $('#sub-categories-selector-area').css('display','none');
            $('#category-selector-id').css('display','block');
    });

    $('#back-to-sub').click(function(){
        $('.custom-step').css('display','none');
        $('#sub-categories-selector-area').css('display','block');
        $('.step-post-project').css('padding-right','0px');
    });


  });
})(jQuery);
</script>