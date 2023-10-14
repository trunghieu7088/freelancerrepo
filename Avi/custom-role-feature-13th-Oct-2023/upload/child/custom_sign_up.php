<?php
function add_custom_role()
{
    $role_info=get_role('author'); //get capabilities of author
    add_role('client','Client',$role_info->capabilities );
    add_role('expert','Expert',$role_info->capabilities );
}
add_action('init','add_custom_role');

function mJobModalSignUpStepOne($intro) {
    ?>
    <div class="modal fade" id="signUpStep1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><img
                                src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt=""></span></button>
                    <h4 class="modal-title" id="myModalLabel1"><?php _e('Join us', 'enginethemes'); ?></h4>
                </div>
                <div class="modal-body">
                    <?php
                    // Show form
                    mJobSignUpFormStepOne($intro);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function mJobSignUpFormStepOne($intro) {
    // Add filter to change the content of note
    //$intro = apply_filters('mjob_signup_form_note', $intro);
    ?>
    <form id="signUpFormStep1" class="form-authentication et-form">
        <div class="inner-form">
            <div class="note-paragraph"><?php echo $intro ?></div>
            <div class="form-group">
                <div class="custom-role-option">
                    <span>I'm a Client seeking guidance.</span>
                    <div class="option-button"><input type="radio" name="custom_role" id="custom_role_client" value="client"><span>Client</span></div>
                    
                </div>
                <div class="custom-role-option">
                    <span>I'm an Expert looking to share my knowledge.</span>
                    <div class="option-button"><input type="radio" name="custom_role" id="custom_role_expert" checked value="expert"><span>Expert</span></div>
                </div>
            </div>
            <div class="form-group clearfix insert-email">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                    <button class="<?php mje_button_classes( array( 'btn-continue', 'waves-effect', 'waves-light' ) ); ?>"><?php _e('CONTINUE', 'enginethemes'); ?><i class="fa fa-angle-right"></i></button>
                    <input type="text" name="user_email" id="user_email" class="form-control" placeholder="<?php _e('Enter your email here', 'enginethemes'); ?>">
                </div>
            </div>
            
            <div class="form-group no-margin social">
                <?php
                if( function_exists('ae_render_social_button')){
                    $before_string = __("Or join us with:", 'enginethemes');
                    ae_render_social_button(array(), array(), $before_string);
                }
                ?>
            </div>
        </div>
    </form>
    <?php
}

// custom code to handle
add_action('ae_insert_user','set_custom_role_new_user',99,2);

function set_custom_role_new_user($result, $user_data)
{
    $custom_user_info=new WP_User($result);
    $custom_user_info->remove_role('author');
    $custom_user_info->add_role($user_data['custom_role']);  
    update_user_meta($result,'custom_role',$user_data['custom_role']);
    $custom_user_profile_id=get_user_meta($result,'user_profile_id',true);
    update_post_meta($custom_user_profile_id,'custom_role',$user_data['custom_role']);
    
}