<?php
//    global $current_user;
?>
<div class="modal fade modal-edit-profile" id="edit_profile" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="myModalLabel"><?php _e("Edit Profile", ET_DOMAIN) ?></h4>
            </div>
            <div class="modal-body">
                <div class="author-edit" id="user_avatar_container">
                    <span class="author-avatar image" id="user_avatar_thumbnail">
                        <?php
//                            if( ! empty($current_user->ID))
//                                echo et_get_avatar($current_user->ID, 80);
                        ?>
                    </span>
                    <div class="edit-info-avatar">
                        <a href="javascript:void(0);" class="upload-avatar-btn" id="user_avatar_browse_button">
                            <?php _e("Upload New Avatar", ET_DOMAIN) ?>
                        </a>
                        <a href="javascript:void(0);" class="link_change_password"><?php _e("Change Password", ET_DOMAIN) ?></a>
                        <a href="javascript:void(0);" class="link_change_profile"><?php _e("Change Profile", ET_DOMAIN) ?></a>
                    </div>
                    <span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'user_avatar_et_uploader' ); ?>"></span>
                </div>

                    <form id="submit_edit_profile" class="form_modal_style edit_profile_form">
                        <label><?php _e("Full name", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" maxlength="40" id="display_name" name="display_name">

                        <label><?php _e("University", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" maxlength="60" id="university" name="university">

                        <label><?php _e("Level of Education", ET_DOMAIN) ?></label>
                        <?php 
                        //custom code here
                        $level_education=get_user_meta(get_current_user_id(),'level_education',true);
                        ?>
                        <select id="level_education" name="level_education" class="categories-select chosen-select">
                            <option <?php if ($level_education=='.Undergraduate') echo 'selected'; ?> value=".Undergraduate">.Undergraduate</option>
                            <option <?php if ($level_education=='.Associate Degree') echo 'selected'; ?> value=".Associate Degree">.Associate Degree</option>
                            <option <?php if ($level_education=='.Bachelors Degree') echo 'selected'; ?> value=".Bachelors Degree">.Bachelors Degree</option>
                            <option <?php if ($level_education=='.Masters Degree') echo 'selected'; ?> value=".Masters Degree">.Masters Degree</option>
                            <option <?php if ($level_education=='.Doctorate Degree') echo 'selected'; ?>  value=".Doctorate Degree">.Doctorate Degree</option>
                            <option <?php if ($level_education=='.PhD') echo 'selected'; ?> value=".PhD">.PhD</option>
                        </select>

                         <label style="margin-top:20px;"><?php _e("Registering As", ET_DOMAIN) ?></label>
                        <?php 
                        //custom code here
                        $job_role=get_user_meta(get_current_user_id(),'job_role',true);
                        ?>
                        <select id="job_role" name="job_role" class="categories-select chosen-select">
                            <option <?php if($job_role=='Student') echo 'selected'; ?> value="Student">Student</option>
                            <option <?php if($job_role=='Lecturer') echo 'selected'; ?> value="Lecturer">Lecturer</option>
                            <option <?php if($job_role=='Alumni') echo 'selected'; ?> value="Alumni">Alumni</option>
                            <option <?php if($job_role=='Librarian') echo 'selected'; ?> value="Librarian">Librarian</option>
                            <option <?php if($job_role=='Rather not say') echo 'selected'; ?> value="rather not say">Rather not say</option>  
                        </select>


                        <label style="margin-top:20px;"><?php _e("Location", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" maxlength="40" id="user_location" name="user_location">

                        <label><?php _e("Facebook", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" maxlength="80" id="user_facebook" name="user_facebook">

                        <label><?php _e("Twitter", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" maxlength="80" id="user_twitter" name="user_twitter">

                        <label><?php _e("Google+", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" maxlength="80" id="user_gplus" name="user_gplus">

                        <label><?php _e("Email", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input" id="user_email" name="user_email">

                        <div class="clearfix"></div>

                        <label><?php _e("About me", ET_DOMAIN) ?></label>
                        <textarea  maxlength="350" class="submit-textarea" id="description" name="description"></textarea>
                        <input type="checkbox" name="show_email"  id="show_email" />
                        <label for="show_email" class="checkbox-email">
                            <?php _e("Make this email public.", ET_DOMAIN) ?>
                        </label>
                        <div class="clearfix"></div>

                        <input type="submit" name="submit" value="<?php _e("Update Profile", ET_DOMAIN) ?>" class="btn-submit update_profile">
                    </form>

                    <form id="submit_edit_password" class="form_modal_style edit_password_form">
                        <label><?php _e("Old Password", ET_DOMAIN) ?></label>
                        <input type="password" class="submit-input" id="old_password" name="old_password">
                        <label><?php _e("New Password", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input input-password" id="new_password1" name="new_password">
                        <label><?php _e("Repeat New Password", ET_DOMAIN) ?></label>
                        <input type="text" class="submit-input input-password" id="re_password" name="re_password">
                        <input type="submit" name="submit" value="<?php _e("Change Password", ET_DOMAIN) ?>" class="btn-submit update_profile">
                    </form>

            </div>
        </div>
    </div>
</div>