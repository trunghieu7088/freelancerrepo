<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/2/2016
 * Time: 13:06
 */
if (!function_exists('mjob_setting_user')) {
    function mjob_setting_user()
    {
        return array(
            'args' => array(
                'title' => __("Users", 'enginethemes'),
                'id' => 'users-setting',
                'icon' => 'y',
                'class' => ''
            ),
            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("User default avatar", 'enginethemes'),
                        'id' => '',
                        'class' => '',
                        'name' => '',
                    ),
                    'fields' => array(
                        array(
                            'id' => 'opt-ace-editor-js',
                            'type' => 'image',
                            'title' => __("Avatar setting", 'enginethemes'),
                            'desc' => __("Your logo should be in PNG, GIF or JPG format, within 87x87px and less than 1500Kb.", 'enginethemes'),
                            'name' => 'default_avatar',
                            'class' => '',
                            'default' => get_template_directory_uri() . '/assets/img/avatar.png',
                            'size' => array(
                                '78',
                                '78'
                            )
                        ),
                    )
                ),
                /* Authentication */
                array(
                    'args' => array(
                        'title' => __("Authentication", 'enginethemes'),
                        'id' => '',
                        'class' => '',
                        'name' => '',
                    ),
                    'fields' => array(
                        array(
                            'id' => 'sign_up_intro_text',
                            'type' => 'editor',
                            'title' => __("Sign up introduction text", 'enginethemes'),
                            'desc' => __("Write a brief description to promote your signup process.", 'enginethemes'),
                            'name' => 'sign_up_intro_text',
                            'class' => '',
                            'reset' => 1
                        ),
                        array(
                            'id' => 'user_confirm',
                            'type' => 'switch',
                            'title' => __("Email confirmation", 'enginethemes'),
                            'desc' => __("Enabling this will require users to confirm their email addresses after registration.", 'enginethemes'),
                            'name' => 'user_confirm',
                            'class' => ''
                        ),
                    )
                ),
                /*User profile settings*/
                array(
                    'args' => array(
                        'title' => __("User profile", "enginethemes"),
                        'id' => '',
                        'class' => '',
                        'name' => '',
                    ),
                    'fields' => array(
                        array(
                            'id' => 'user_local_timezone',
                            'type' => 'switch',
                            'title' => __("Local timezone", 'enginethemes'),
                            'desc' => __("Enabling this will allow users to select their local timezone.", 'enginethemes'),
                            'name' => 'user_local_timezone',
                            'class' => '',
                            'default' => 'enable'
                        ),
                    )
                ),
                /* Config Social Login API*/
                array(
                    'args' => array(
                        'title' => __("Config Social Login API", 'enginethemes'),
                        'desc' => __('Setup a way for users to login via their social network accounts', 'enginethemes'),
                        'id' => '',
                        'class' => '',
                        'name' => '',
                    ),
                    'fields' => array(
                        /* Facebook login api */
                        array(
                            'id' => 'facebook_api',
                            'name' => '',
                            'title' => __("Facebook API ", 'enginethemes'),
                            'desc' => __('This allows users to log into your site via their Facebook account. Visit <a href="https://developers.facebook.com/" target="_blank">here</a> to upgrade your personal account to a Facebook Developer account and create a new Facebook app.', 'enginethemes'),
                            'type' => 'combine',
                            'class' => 'field-social-api',
                            'children' => array(
                                array(
                                    'id' => 'facebook_login',
                                    'type' => 'switch',
                                    'title' => __("Enable Facebook API", 'enginethemes'),
                                    'desc' => __("Enabling this will allow users to log in via Facebook", 'enginethemes'),
                                    'name' => 'facebook_login',
                                    'class' => ''
                                ),
                                array(
                                    'id' => 'et_facebook_key',
                                    'type' => 'text',
                                    'title' => __("Facebook key", 'enginethemes'),
                                    'name' => 'et_facebook_key',
                                    'placeholder' => __("Facebook Application ID", 'enginethemes'),
                                    'class' => '',
                                ),
                                array(
                                    'id' => 'et_facebook_secret_key',
                                    'type' => 'text',
                                    'title' => __("Facebook secret key", 'enginethemes'),
                                    'name' => 'et_facebook_secret_key',
                                    'placeholder' => __("Facebook Secret Key", 'enginethemes'),
                                    'class' => '',
                                )
                            )
                        ),
                        /* Google login api */
                        array(
                            'id' => 'google_api',
                            'name' => '',
                            'title' => __("Google API", 'enginethemes'),
                            'desc' => __('Allows users to log into your site via their Google account. Read <a href="https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid" target="_blank">this article</a> to set up your Google client. <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Get your keys here</a>', 'enginethemes'),
                            'type' => 'combine',
                            'class' => 'field-social-api',
                            'children' => array(
                                array(
                                    'id' => 'gplus_login',
                                    'type' => 'switch',
                                    'title' => __("Enable Google API", 'enginethemes'),
                                    'desc' => __("Enabling this will allow users to log in via Google", 'enginethemes'),
                                    'name' => 'gplus_login',
                                    'class' => ''
                                ),
                                array(
                                    'id' => 'gplus_client_id',
                                    'type' => 'text',
                                    'title' => __("Google Client ID", 'enginethemes'),
                                    'name' => 'gplus_client_id',
                                    'placeholder' => "1234567890-abc123def456.apps.googleusercontent.com",
                                    'class' => '',
                                ),
                                array(
                                    'id' => 'gplus_secret_id',
                                    'type' => 'text',
                                    'title' => __("Google Secret Key", 'enginethemes'),
                                    'name' => 'gplus_secret_id',
                                    'placeholder' => "ABCDEF-abcdefghijklmnopqrstuvwxyz123456",
                                    'class' => '',
                                )
                            )
                        )
                    )
                ),
                /* User mail template */
                array(
                    'args' => array(
                        'title' => __("Authentication Mail Template", 'engienthemes'),
                        'desc' => __("Email templates for the authentication process. You can use placeholders to include some specific content.", 'enginethemes') . '<a class="icon btn-toggle-help payment" href="#" title="View more details"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></a>' . '<div class="cont-template-help payment-setting">
                                                    <p><span>[user_login],[display_name],[user_email] :</span>' . __("user's details you want to send mail", 'enginethemes') . '<br />
                                                    <span>[dashboard] : </span>' . __("member dashboard url ", 'enginethemes') . '<br />
                                                    <span>[title], [link], [excerpt],[desc], [author] : </span>' . __("mJob title, link, details, author", 'enginethemes') . ' <br />
                                                    <span>[activate_url] : </span>' . __("activate link is require for user to renew password", 'enginethemes') . ' <br />
                                                    <span>[site_url],[blogname],[admin_email] : </span>' . __(" site info, admin email", 'enginethemes') . '
                                                    <span>[project_list] : </span>' . __("list of mJobs a buyer sends to a seller when inviting him to join", 'enginethemes') . '
                                                    </p>
                                                </div>',
                        'id' => 'user-mail-group',
                        'class' => '',
                        'name' => ''
                    ),
                    'fields' => array(
                        array(
                            'id' => 'register_mail_template',
                            'type' => 'editor',
                            'title' => __("Register mail template", 'enginethemes'),
                            'desc' => __("Send to user when he registers successfully.", 'enginethemes'),
                            'name' => 'register_mail_template',
                            'class' => '',
                            'reset' => 1,
                            'toggle' => true
                        ),
                        array(
                            'id' => 'confirm_mail_template',
                            'type' => 'editor',
                            'title' => __("Confirm mail template", 'enginethemes'),
                            'desc' => __("Send to user after he successfully registered when the option of confirming email is on.", 'enginethemes'),
                            'name' => 'confirm_mail_template',
                            'class' => '',
                            'reset' => 1,
                            'toggle' => true
                        ),
                        array(
                            'id' => 'confirmed_mail_template',
                            'type' => 'editor',
                            'title' => __("Confirmed mail template", 'enginethemes'),
                            'desc' => __("Send to user to notify that he has successfully confirmed the email.", 'enginethemes'),
                            'name' => 'confirmed_mail_template',
                            'class' => '',
                            'reset' => 1,
                            'toggle' => true
                        ),
                        array(
                            'id' => 'forgotpass_mail_template',
                            'type' => 'editor',
                            'title' => __("Forgot password mail template", 'enginethemes'),
                            'desc' => __("Send to user when he requests password reset.", 'enginethemes'),
                            'name' => 'forgotpass_mail_template',
                            'class' => '',
                            'reset' => 1,
                            'toggle' => true
                        ),
                        array(
                            'id' => 'resetpass_mail_template',
                            'type' => 'editor',
                            'title' => __("Reset password mail template", 'enginethemes'),
                            'desc' => __("Send to user to notify him of successful password reset.", 'enginethemes'),
                            'name' => 'resetpass_mail_template',
                            'class' => '',
                            'reset' => 1,
                            'toggle' => true
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Other Mail Templates", 'engienthemes'),
                        'desc' => __("Other email templates for specific occasions. 
                        You can use placeholders to include some specific content.", 'enginethemes') . '<a class="icon btn-toggle-help payment" href="#" title="View more details"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></a>' . '<div class="cont-template-help payment-setting">
                                                            <p><span>[user_login],[display_name],[user_email] :</span>' . __("user's details you want to send mail", 'enginethemes') . '<br />
                                                            <span>[dashboard] : </span>' . __("member dashboard url ", 'enginethemes') . '<br />
                                                            <span>[title], [link], [excerpt],[desc], [author] : </span>' . __("mJob title, link, details, author", 'enginethemes') . ' <br />
                                                            <span>[activate_url] : </span>' . __("activate link is require for user to renew password", 'enginethemes') . ' <br />
                                                            <span>[site_url],[blogname],[admin_email] : </span>' . __(" site info, admin email", 'enginethemes') . '
                                                            <span>[project_list] : </span>' . __("list of mJobs a buyer sends to a seller when inviting him to join", 'enginethemes') . '
                                                            </p>
                                                        </div>',
                        'id' => 'other-mail-group',
                        'class' => '',
                        'name' => ''
                    ),
                    'fields' => array(
                        array(
                            'id' => 'new_msg_notify_delayed_minutes',
                            'type' => 'number',
                            'title' => __("Set Delay for New Message Notifications", 'enginethemes'),
                            'desc' => __("Before sending this new message notification, you can set a delay (in minutes). If the recipient has already read the original message within this delay period, the notification will be automatically cancelled. <strong>Minimum: 10 minutes.</strong>", 'enginethemes'),
                            'name' => 'new_msg_notify_delayed_minutes',
                            'placeholder' => "",
                            'class' => 'option-item bg-grey-input positive_int',
                            'default' => 15
                        ),
                        array(
                            'id' => 'inbox_mail_template',
                            'type' => 'editor',
                            'title' => __('New Message Notifications', 'enginethemes'),
                            'desc' => __("Send a notification email to users after a delay if they haven't read the new message.", 'enginethemes'),
                            'class' => '',
                            'name' => 'inbox_mail_template',
                            'reset' => 1,
                            'toggle' => true
                        )
                    )
                )
            )
        );
    }
}
