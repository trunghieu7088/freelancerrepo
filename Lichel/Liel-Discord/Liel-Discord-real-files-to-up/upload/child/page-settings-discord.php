<?php
/**
 * Template Name: Discord Settings
 */
global $current_user;
get_header();
?>
    <div id="content">
        <div class="container dashboard withdraw">
            <div class="row title-top-pages">
                <p class="block-title"><?php _e('Discord Notification Settings', 'enginethemes'); ?></p>
                <p><a href="<?php echo et_get_page_link('dashboard'); ?>" class="btn-back"><i class="fa fa-angle-left"></i><?php _e('Back to dashboard', 'enginethemes'); ?></a></p>
            </div>
            <div class="row profile">
                <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12 profile">
                    <?php get_sidebar('my-profile'); ?>
                </div>

                <div class="col-lg-9 col-md-9 col-sm-12 col-sx-12 box-shadow ">
                    <div id="settings" style="padding:25px;">
                        <?php $DiscordID=get_user_meta($current_user->ID,'DiscordID',true);
                            if($DiscordID) : ?>
                           <h4>You have linked your Discord</h4> 
                             <form class="et-form" style="padding: 30px 10px 0px 10px;">

                            <?php

                            $et_discord_subscriber = et_get_discord_subscribe_settings();
                            $checked = 'checked';
                            if( ! $et_discord_subscriber )
                                $checked = '';
                            ?>

                          
                            <div class="form-group clearfix row">
                                <div class="input-group">
                                    <div class="col-md-7"> <label class="field-label"><?php _e('Send notifications to Discord','enginethemes');?></label> </div>
                                    <div class="col-md-4">
                                        <label class="switch">
                                          <input type="checkbox" class="discord_noti_toggle" name="discord_noti_subscribe"  <?php echo $checked;?>>
                                          <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </form>    
                          <?php else: ?>
                        <h4>You need to link your Discord account to get notifications</h4>
                        <a href="<?php echo get_option('loginDiscordLink'); ?>">Link Discord</a>
                        <?php endif; ?>

                       

                    </div>
            </div>
        </div>
    </div>
</div>

   
    <style type="text/css">
        .switch {
            position: relative;
            display: inline-block;
            width: 90px;
            height: 34px;
        }

        /* Hide default HTML checkbox */
        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }

        /* The slider */
         span.slider {
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
        }
        .form-group label span.slider {
          position: absolute;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 29px;
            width: 29px;
            left: 3px;
            top: 3px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
          background-color: #2196F3;
        }

        input:focus + .slider {
          box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
            right: 32px;
            left: auto;
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
            height: 36px !important;
        }

        .slider.round:before {
          border-radius: 50%;
        }

    </style>

    <script type="text/javascript">
            (function($) 
            {
                 $(document).ready(function() {

                    $(".discord_noti_toggle").change(function(event){
                    event.preventDefault();
                    var target = $(event.currentTarget),
                        name = target.attr('name'),
                        value = target.is(':checked');

                         $.ajax({
                        type: "post",
                        url: ae_globals.ajaxURL,
                        dataType: 'json',
                        data: {name:name,value:value,action: 'et_discord_subscribe'},

                        beforeSend: function () {
                            //view.blockUi.block(button);

                        },
                        success: function (data, status, xhr) {

                            //view.blockUi.unblock();

                            if (data.success) {
                                AE.pubsub.trigger('ae:notification', {
                                    msg: data.msg,
                                    notice_type: 'success'
                                });
                            } else{
                                AE.pubsub.trigger('ae:notification', {
                                    msg: 'something went wrong',
                                    notice_type: 'error'
                                });
                            }
                        }
                    });
                    return false;


                    });

                   
                 });
            })(jQuery)
    </script>
<?php
get_footer();
?>
