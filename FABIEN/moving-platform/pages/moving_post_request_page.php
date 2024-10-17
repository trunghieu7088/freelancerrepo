<?php
/*
Template Name: Moving Post Request page
*/
?>
<?php
get_header();
$admin_data=AdminData::get_instance();
?>
<div class="moving_post_request_wrapper">
    <div class="container">                
        <div class="post-request-section">
     
            <p class="post-request-title"><?php _e('Post a request', 'moving_platform'); ?></p>            

                <form id="post-request-form" action="" method="POST">                    
                    <input type="hidden" name="action" id="action" value="submit_moving_request">
                    <input type="hidden" name="submit_moving_request_nonce" id="submit_moving_request_nonce" value="<?php echo wp_create_nonce('submit_moving_request_nonce'); ?>">
                    <div class="post-request-form-group">                        
                        <div class="row">        
                            <div class="col-md-12 col-lg-12 col-sm-12 post-request-fields">    
                                <input type="text" name="request_title" id="request_title" value="" placeholder="<?php _e('Request Title', 'moving_platform'); ?>">
                            </div>
    
                        </div>
                    </div>

                    <div class="post-request-form-group">                        
                        <div class="row">           
                            <div class="col-md-6 col-lg-6 col-sm-12 post-request-fields">                         
                                <input type="text" name="last_name" id="last_name" value="" placeholder="<?php _e('Last Name', 'moving_platform'); ?>">
                            </div>                            
                            <div class="col-md-6 col-lg-6 col-sm-12 post-request-fields">                          
                                <input type="text" class="second_item_form" name="first_name" id="first_name" value="" placeholder="<?php _e('First Name', 'moving_platform'); ?>">
                            </div>
                        </div>                                            
                    </div>

                    <div class="post-request-form-group">
                        <div class="row">     
                            <div class="col-md-6 col-lg-6 col-sm-12 post-request-fields">
                                <input type="text" class="custom_date_picker" name="departure_date" id="departure_date" value="" placeholder="<?php _e('Departure Date', 'moving_platform'); ?>">
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-12 post-request-fields">
                                <input type="text" class="custom_date_picker second_item_form" name="arrival_date" id="arrival_date" value="" placeholder="<?php _e('Arrival Date', 'moving_platform'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="post-request-form-group">                        
                        <div class="row">           
                            <div class="col-md-6 col-lg-6 col-sm-12 post-request-fields">
                                <input type="text" name="departure_address" id="departure_address" value="" placeholder="<?php _e('Departure Address', 'moving_platform'); ?>">
                            </div>                     
                            <div class="col-md-4 col-lg-4 col-sm-12 post-request-fields second_item_form">                               
                                <?php echo generate_drop_down(array('term_name'=>'city',
                                                                'name'=>'city_selector_depart',
                                                                'id'=>'city_selector_depart',
                                                                'class'=>'city_selector',
                                                                'placeholder'=> __('Type to search cities','moving_platform'))
                                                            ); 
                                ?>
                            </div>
                            <div class="col-md-2 col-lg-2 col-sm-12 post-request-fields second_item_form">
                                <input type="text" name="postal_code_depart" id="postal_code_depart" value="" placeholder="<?php _e('Departure Postal code', 'moving_platform'); ?>">
                            </div>
                           
                        </div>
                    </div>

                    <div class="post-request-form-group">
                        <div class="row">  
                              
                            <div class="col-md-6 col-lg-6 col-sm-12 post-request-fields">
                                <input type="text" name="arrival_address" id="arrival_address" value="" placeholder="<?php _e('Arrival Address', 'moving_platform'); ?>">
                            </div>
                            <div class="col-md-4 col-lg-4 col-sm-12 post-request-fields second_item_form">
                                <?php echo generate_drop_down(array('term_name'=>'city',
                                                                'name'=>'city_selector_arrival',
                                                                'id'=>'city_selector_arrival',
                                                                'class'=>'city_selector',
                                                                'placeholder'=> __('Type to search cities','moving_platform'))
                                                            ); 
                                ?>
                            </div>
                            <div class="col-md-2 col-lg-2 col-sm-12 post-request-fields second_item_form">
                                <input type="text" name="postal_code_arrival" id="postal_code_arrival" value="" placeholder="<?php _e('Arrival Postal code', 'moving_platform'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="post-request-form-group">
                        <div class="row">     
                            <div class="col-md-2 col-lg-2 col-sm-12 post-request-fields">
                                <input type="text" name="budget" id="budget" value="" placeholder="<?php _e('Budget (€)', 'moving_platform'); ?>">
                            </div>
                            <div class="col-md-5 col-lg-5 col-sm-12 post-request-fields">
                                <input type="text" name="contact_method" id="contact_method" value="" placeholder="<?php _e('Phone number', 'moving_platform'); ?>">
                            </div>
                            <div class="col-md-5 col-lg-5 col-sm-12 post-request-fields">
                                <input type="text" class="second_item_form" name="email_notification" id="email_notification" value="" placeholder="<?php _e('Email Address', 'moving_platform'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="post-request-form-group">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                
                                <?php 
                                    $wp_editor_settings = array(
                                        'textarea_name'=>'request_description',
                                        'media_buttons' => false,
                                        'quicktags' => false,
                                        'teeny'         => true,
                                        'textarea_rows' =>10,
                                        'tinymce'       => array(
                                            'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright',
                                            'toolbar2'      => '',
                                            'toolbar3'      => '',
                                        ),
                                    );
                                    wp_editor( '', 'request_description', $wp_editor_settings );
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="post-request-form-group">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-sm-12">                                
                                <div class="request-image-upload-container" id="request-image-upload-container">
                                    <a type="button" class="request-btn-upload-image" id="request-image-upload-btn"><i class="fa fa-plus"></i> <i class="fa fa-image"></i></a>
                                    <input type="hidden" name="request_image_uploader_nonce" id="request_image_uploader_nonce" value="<?php echo wp_create_nonce('request_image_uploader_nonce'); ?>">
                                </div>
                                <div class="uploaded-image-section" id="uploaded-image-section">
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="post-request-form-group">
                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-sm-12 post-request-fields">
                                <input type="checkbox" id="accept_tos" name="accept_tos" value="accept" checked>
                                <label for="accept_tos" class="accept_service_text"><?php _e('I accept the', 'moving_platform'); ?> <a class="tos_link" href="<?php echo $admin_data->getValue('tos_link'); ?>" target="_blank"><?php _e('term of service', 'moving_platform'); ?></a></label>
                                
                            </div>
                        </div>
                    </div>

                    <div class="post-request-form-group">
                        <button type="submit" class="request-btn-submit-form"><?php  _e('Submit','moving_platform');?></button>
                    </div>                    
                </form>                
        </div>
        
    </div>
</div>


<?php
get_footer();
?>