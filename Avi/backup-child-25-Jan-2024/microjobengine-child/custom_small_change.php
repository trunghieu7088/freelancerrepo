<?php
add_filter('teeny_mce_buttons', 'custom_ce_teeny_mce_buttons',99);
function custom_ce_teeny_mce_buttons($buttons)
{
    return array(
        'format',
        'bold',
        'italic',
        'underline',
        'bullist',
        'numlist',     
    );
}


function custom_archive_title($title) {
    if (is_post_type_archive('mjob_post')) {
      $title = 'Session Archive';
    }
    return $title;
  }
  add_filter('post_type_archive_title', 'custom_archive_title',9999,1);

  function mJobSignUpForm($email = '', $is_page = false, $redirect_url = '', $header_text = "") {
    ?>
    <div id="signUpForm">
        <?php
        if(!empty($header_text)) {
            echo '<p class="form-header-text">'. $header_text .'</p>';
        }
        ?>
        <form class="form-authentication et-form float-left">
            <?php
                // If self link in home redirect dashboard else reload page
                $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                $action_link = $_SERVER['REQUEST_URI'];
                if(is_home()) {
                    $redirect_url = et_get_page_link('dashboard');
                    echo '<input type="hidden" name="redirect_url" class="redirect_url" value="'. $redirect_url .'" />';
                } elseif(!empty($redirect_url)) {
                    echo '<input type="hidden" name="redirect_url" class="redirect_url" value="'. $redirect_url .'" />';
                } else {
                    echo '<input type="hidden" name="redirect_url" class="redirect_url" value="'. $protocol.$_SERVER['HTTP_HOST'].$action_link .'" />';
                }
            ?>
            <div class="btn-back-sign">
                <?php
                if($is_page == true) {
                    ?>
                    <a href="#" class="focus-signin-form"><i class="fa fa-angle-left"></i><?php _e('Back to sign in', 'enginethemes'); ?></a>
                    <?php
                }
                ?>
            </div>
            <div class="inner-form">
                <?php
                    if(empty($email)) {
                        ?>
                        <div class="form-group clearfix">
                            <div class="input-group">
                                <label for="user_email"><?php _e( 'Email', 'enginethemes' ); ?></label>
                                <input type="text" name="user_email" id="user_email" class="form-control">
                            </div>
                        </div>
                        <?php
                    }
                ?>
                <!-- Set timezone local -->
                <input type="hidden" name="timezone_local" class="timezone-local" value="">
                <script type="text/javascript">
                    var tz = jstz.determine();
                    var timeZoneLocal = tz.name();
                    jQuery('.timezone-local').attr('value',timeZoneLocal);
                </script>
                <!--End set timezone local-->

                <div class="form-group clearfix">
                    <div class="input-group">
                        <label for="user_login"><?php _e( 'Username', 'enginethemes' ); ?> <i class="fa fa-info-circle usernameInfoIcon"  title="Username Notification" data-content="Your username cannot be changed after registration." aria-hidden="true"></i> </label>
                        <input type="text" name="user_login" id="user_login" class="form-control">
                    </div>
                </div>
                <div class="form-group clearfix">
                    <div class="input-group">
                        <label for="user_pass"><?php _e( 'Password', 'enginethemes' ); ?></label>
                        <input type="password" name="user_pass" id="user_pass" class="form-control">
                    </div>
                </div>
                <div class="form-group margin-bot-15 clearfix">
                    <div class="input-group">
                        <label for="repeat_pass"><?php _e( 'Confirm your password', 'enginethemes' ); ?></label>
                        <input type="password" name="repeat_pass" id="repeat_pass" class="form-control repeat_pass">
                    </div>
                </div>
                <div class="form-group clearfix float-left">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="term_privacy" id="term_privacy"><span class="tos-text"><?php _e('I accept with the', 'enginethemes'); ?>
                                <a href="<?php echo et_get_page_link('tos'); ?>" target="_blank"><?php _e('terms and conditions', 'enginethemes'); ?></a></span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button class=" <?php mje_button_classes( array( 'btn-continue', 'waves-effect', 'waves-light' ) ); ?>"><?php _e('JOIN', 'enginethemes'); ?></button>
                </div>

                <?php if($is_page == true && !is_page_template('page-process-payment.php')) : ?>
                <div class="clearfix float-right social">
                    <?php
                    if( function_exists('ae_render_social_button')){
                        $before_string = __("Or sign up with:", 'enginethemes');
                        ae_render_social_button( array(), array(), $before_string );
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php
}



function loadUsernameIcon() 
{
	?>
	<script type="text/javascript">
		  (function($) {
                            $(document).ready(function() {                        
                              $(".usernameInfoIcon").popover();
                            });
      })(jQuery);
	</script>
	<?php
}

add_action( 'wp_footer', 'loadUsernameIcon');