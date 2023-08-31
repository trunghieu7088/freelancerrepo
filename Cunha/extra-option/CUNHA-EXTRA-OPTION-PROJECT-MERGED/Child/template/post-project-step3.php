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

      $available_credit_custom = FRE_Credit_Users()->getUserWallet($user_ID);
        $urgentlabel=get_option('urgentlabel') ? get_option('urgentlabel') : 'Ugrent';
        $urgentdescription=get_option('urgentdescription') ? get_option('urgentdescription') : 'Make your project stand out and let the users know that your job is time sensitive';
        $urgentprice=get_option('urgentprice') ? get_option('urgentprice') : 10;

        $privatelabel=get_option('privatelabel') ? get_option('privatelabel') : 'Private';
        $privatedescription=get_option('privatedescription') ? get_option('privatedescription') : 'Hide project details from search engines and users that are not logged in, for projects that you need to keep confidential.';
        $privateprice=get_option('privateprice') ? get_option('privateprice') : 10;

        $avaiblecredittext=get_option('avaiblecredit') ? get_option('avaiblecredit') : 'Available Credit';
        $totalcost=get_option('totalcost') ? get_option('totalcost') : 'Total Cost';
        $addcredits=get_option('addcredits')  ? get_option('addcredits') : 'Add credits';
        $yourbalance=get_option('yourbalance') ? get_option('yourbalance') : 'Your balance has not enough credit';

        if( $available_credit_custom->balance < $urgentprice )
        {
            $not_enough_urgent=true;      
        }
        if($available_credit_custom->balance < $privateprice)
        {
            $not_enough_private=true;     
        }
    //end custom 

?>
<style>
.extra-option-project::after
{
width:90%;
border:1px solid #d3d3d3;
margin-top:60px;
margin-left:20px;
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
            <div class="step-post-project" id="fre-post-project">
                <h2><?php _e('Your Project Details', ET_DOMAIN);?></h2>
                <div class="fre-input-field">
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

                    <div class="fre-input-field">
                    <div class="row extra-option-project" style="padding-left:20px;<?php if($not_enough_urgent==true) echo 'opacity: 0.5;' ?>">
                        <input type="hidden" class="input-item" value="" name="realcost" id="realcost">
                        <input type="hidden" class="input-item" value="<?php echo $privateprice; ?>" name="realprivateprice" id="realprivateprice">
                        <input type="hidden" class="input-item" value="<?php echo $urgentprice; ?>" name="realurgentprice" id="realurgentprice">
                    <div class="col-md-4" style="border-left:5px solid #74B72E !important;min-height:50px;">
                     <input <?php if($not_enough_urgent==true) echo 'disabled="disabled"'; ?> class="input-item" type="checkbox" id="urgent_extra_option" name="urgent_extra_option" value="urgent">
                     <label for="urgent_extra_option" class="text-uppercase" style="margin-left:20px;padding:5px 50px 5px 50px;background-color: #ff0000 !important;color:#fff;border-radius: 20px;"><?php echo $urgentlabel; ?></label>
                    </div>

                    <div class="col-md-6" style="font-size:16px !important;font-weight: 700;text-align:left;">
                        <span> <?php echo $urgentdescription; ?></span>
                    </div>

                   <div class="col-md-2" style="font-size: 18px;font-weight: 800;">
                       <p id="urgentpriceshow"> <?php echo fre_price_format($urgentprice); ?></p>

                    </div>

                    </div>

                    <div class="row" style="margin-top:20px !important;padding-left:20px;<?php if($not_enough_private==true) echo 'opacity: 0.5;' ?>">
                    <div class="col-md-4" style="border-left:5px solid #74B72E !important;min-height:50px;">
                     <input <?php if($not_enough_private==true) echo 'disabled="disabled"'; ?> class="input-item" type="checkbox" id="private_extra_option" name="private_extra_option" value="private">
                     <label for="private_extra_option" class="text-uppercase" style="margin-left:20px;padding:5px 50px 5px 50px;background-color: #FFBF00 !important;color:#fff;border-radius: 20px;"> <?php echo $privatelabel; ?> </label>
                    </div>

                    <div class="col-md-6" style="font-size:16px !important;font-weight: 700;">
                       <span> <?php echo $privatedescription; ?></span>
                    </div>

                    <div class="col-md-2" style="font-size: 18px;font-weight: 800;">
                        <p id="privatepriceshow"><?php echo fre_price_format($privateprice); ?> </p>

                    </div>
                    
                    </div>

                    <div class="fre-input-field" style="text-align:right;font-size:18px;margin-top:50px;font-weight: 800;">                       
                        <p style="font-size:18px;font-weight: 800;">
                            <?php 
                            echo $avaiblecredittext.' : ';
                        echo fre_price_format($available_credit_custom->balance) ; ?></p>
                        <p style="font-size:18px;font-weight: 800;"><?php echo $totalcost.' : ' ;
                        echo '<span id="totalcostshow">'. fre_price_format(0).'</span>'; ?></p>
                        <?php if($not_enough_urgent ==true || $not_enough_private==true) : ?>
                        <p class="text-danger" style="font-size:18px;font-weight: 800;"><?php echo $yourbalance; ?> <a href="<?php echo site_url('deposit-credit'); ?>">( <?php echo $addcredits; ?> )</a></p>
                    <?php endif; ?>
                    </div>
                <?php 
                //custom code extra here

                //end
                ?>
                </div>

                <div class="fre-post-project-btn">
                    <button class="fre-btn fre-post-project-next-btn primary-bg-color" type="submit"><?php _e("Submit Project", ET_DOMAIN); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Step 3 / End -->

<script type="text/javascript">
    (function ($) {
  $(document).ready(function () {
        
        $('#urgent_extra_option').click(function(){
            $('#private_extra_option').prop('checked',false);   
            
            if(this.checked)
            {
                $('#totalcostshow').text($('#urgentpriceshow').text());
                //$('#realcost').val($('#realurgentprice').val());
                $('#realcost').val('urgent');
            }
            else
            {
                $('#totalcostshow').text('0');
                $('#realcost').val('');
            }
        });

         $('#private_extra_option').click(function(){
            $('#urgent_extra_option').prop('checked',false); 
              if(this.checked)
            {         
                $('#totalcostshow').text($('#privatepriceshow').text());
                 //$('#realcost').val($('#realprivateprice').val());
                $('#realcost').val('private');
            }
             else
            {
                $('#totalcostshow').text('0');
                $('#realcost').val('');
            }
        });



      });
})(jQuery);
</script>