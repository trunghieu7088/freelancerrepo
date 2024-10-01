<div class="modal fade" id="checkout_secure_code" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $credit = ae_get_option('credit');
            $secure_code_enable = isset($credit['secure_code_enable']) ? $credit['secure_code_enable'] : '';
            $is_disable = false;
            $label = __('Confirm your payment', 'enginethemes');
            if ($secure_code_enable == '0') {
                $is_disable = true;
                // $label = __('Confirm', 'enginethemes');
            }
            ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><img src="<?php echo get_template_directory_uri() ?>/assets/img/icon-close.png" alt=""></span></button>
                <h4 class="modal-title" id="myModalLabelForgot"><?php echo $label; ?></h4>
            </div>

            <div class="modal-body">
                <form class="checkout_secure_code_form secure-code et-form">
                    <?php
                    if (!$is_disable) { ?>
                        <div class="form-group clearfix value-payment">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-shield"></i></div>
                                <input type="password" name="secure_code" id="secure_code" placeholder="<?php _e('Secure code', 'enginethemes'); ?>">
                            </div>
                            <div class="ml-35" style="margin-top:20px; font-size:1.3rem;">
                                <span><?php _e("Don't have a secure code or forgot it? ", 'enginethemes'); ?><a href="#" class="request-secure-code"><?php _e('Request here', 'enginethemes'); ?></a></span>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group ml-35" style="margin-bottom: 40px;">
                        <p>Your current credit balance is <span id="user_balance"></span>. This transaction will use <span id="checkout_total"></span> credits.
                        </p>
                        <p>Please confirm all details are accurate before clicking <strong>'Pay Now'</strong> to proceed.</p>
                    </div>
                    <input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo de_create_nonce('withdraw_action') ?>">
                    <div class="form-group ml-35">
                        <button class="<?php mje_button_classes(array('submit', 'waves-effect', 'waves-light')); ?>"><?php _e('Pay now', 'enginethemes'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>