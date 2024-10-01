<?php

/**
 *	Template Name: Social ID Connect
 */

if (
    !ae_is_social_enabled() ||
    (!isset($_REQUEST['credential']) && !isset($_REQUEST['type']))
) {
    // shouldn't access this page without things to do --> redirect to home
    wp_redirect(home_url());
    exit;
}


global $wp_query, $wp_rewrite, $post, $et_data;


$error = isset($et_data['error']) ? $et_data['error'] : "";

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if (empty($error) && 'google' == $type && isset($_REQUEST['credential'])) {
    // this action handle the credential in the POST request that Google return to us
    // it set the SESSION that will be used in below forms
    do_action('handle_google_cred_after_login', $_REQUEST['credential']);

    $et_session = et_read_session();
    if (!isset($_SESSION)) {
        @session_start();
    }

    if (isset($et_session['et_auth']) && $et_session['et_auth'] != '') {
        $auth = unserialize($et_session['et_auth']);
    } elseif (isset($_SESSION['et_auth']) && $_SESSION['et_auth'] != '') {
        $auth = unserialize($_SESSION['et_auth']);
    } else {
        et_destroy_session();
        wp_redirect(home_url());
        exit;
    }
}

get_header();

if ($error) {
    et_destroy_session();
?>
    <div class="social-error">
        <?php _e("There has been an error during your authentication. You will be redirect to the sign in page to try again in 3s.", 'enginethemes'); ?>
    </div>
    <script type="text/javascript">
        setTimeout(function() {
            window.location.href = "<?php echo et_get_page_link("sign-in") ?>";
        }, 3000);
    </script>
<?php
} else {
?>
    <div id="content" class="container">
        <!-- block control  -->
        <div class="row block-posts post-detail" id="post-control">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 posts-container">
                <div class="blog-wrapper list-page">
                    <div class="row">
                        <div class="blog-content">
                            <?php
                            if (isset($et_data['auth_labels']['title'])) {
                            ?>
                                <h2 class="title-blog">
                                    <a href="<?php the_permalink(); ?>"><?php echo $et_data['auth_labels']['title']; ?></a>
                                </h2><!-- end title -->
                            <?php } ?>
                            <div class="post-content">

                                <div class="twitter-auth social-auth social-auth-step1">
                                    <div class="social-welcome">
                                        <?php printf(__("Welcome! This seems to be your first time signing in using your %s account.", 'enginethemes'), ucwords(strtolower($type))); ?>
                                    </div>
                                    <div class="social-instruction">
                                        <p><?php printf(__("If you already have an account with %s, use the form below to link it.", 'enginethemes'), get_bloginfo("name")); ?></p>
                                        <p><?php printf(__("New user? Enter your email, password, and choose a username on the next page to create your account (one-time setup!). Next time, you'll be logged in with %s in a flash!", 'enginethemes'), ucwords(strtolower($type))); ?></p>
                                    </div>

                                    <form id="form_auth" method="post" action="">
                                        <div class="social-form">
                                            <input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce('authentication') ?>">
                                            <input type="text" name="user_email" value="<?php if (isset($auth['user_email'])) echo $auth['user_email']; ?>" placeholder="<?php _e('Email', 'enginethemes') ?>">
                                            <input type="password" name="user_pass" placeholder="<?php _e('Password', 'enginethemes') ?>">
                                            <input type="submit" value="<?php _e('Submit', 'enginethemes'); ?>">
                                        </div>
                                    </form>
                                </div>
                                <div class="social-auth social-auth-step2">
                                    <div class="social-welcome"><?php _e('Please provide a username to continue', 'enginethemes'); ?></div>
                                    <form id="form_username" method="post" action="">
                                        <div class="social-form">
                                            <input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce('authentication') ?>">
                                            <input type="text" name="user_login" value="<?php echo isset($auth['user_login']) ? $auth['user_login'] : "" ?>" placeholder="<?php _e('Username', 'enginethemes') ?>">
                                            <?php $social_user_roles = ae_get_option('social_user_role', false);
                                            if (!$social_user_roles) {
                                                $social_user_roles = ae_get_social_login_user_roles_default();
                                            }
                                            if ($social_user_roles && count($social_user_roles) >= 1) { ?>
                                                <select name="user_role" class="sc_user_role">
                                                    <?php foreach ($social_user_roles as $key => $value) { ?>
                                                        <option value="<?php echo $value ?>"><?php echo $value; ?></option>
                                                    <?php } ?>
                                                </select>
                                            <?php } ?>
                                            <input type="submit" value="<?php _e('Submit', 'enginethemes'); ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div><!-- RIGHT CONTENT -->
        </div>
        <!--// block control  -->
    </div>
<?php
}

get_footer();
