<?php

/**
 * Step 1 select pricing to post a service
 *
 * @param void
 * @return void
 * @since 1.0
 * @package MicrojobEngine
 * @category void
 * @author JACK BUI
 */
global $user_ID, $ae_post_factory;
$ae_pack = $ae_post_factory->get('pack');
$packs = $ae_pack->fetch('pack');
$package_data = AE_Package::get_package_data($user_ID);
$orders = AE_Payment::get_current_order($user_ID);
?>
<ul class="post-job step-wrapper step-plan" id="step1">
    <p class="note-plan"><?php _e('Choose your pricing plan', 'enginethemes'); ?></p>
    <?php
    if (empty($packs)) {
        $admin_email = get_option('admin_email');
        $admin_email = '<a href="mailto:' . $admin_email . '">' . $admin_email . '</a>';
        if (is_super_admin()) {
            $url = get_admin_url() . 'admin.php?page=et-settings#section/payment-type-settings';
            echo sprintf(__('Currently, this function is not available. You should setup the mJob package <a href="%s">here</a>', 'enginethemes'), $url);
        } else {
            echo sprintf(__('Currently, you cannot post a mJob. For more details, please contact %s', 'enginethemes'), $admin_email);
        }
        return;
    }
    foreach ($packs as $key => $package) {
        $number_of_post =   $package->et_number_posts;
        $sku = $package->sku;
        $text = '';
        $order = false;
        if ($number_of_post >= 1) {
            // get package current order
            if (isset($orders[$sku])) {
                $order = get_post($orders[$sku]);
            }

            if ($package->et_permanent == 1) {
                $text = sprintf(__("includes <strong> %d </strong> posts with no time limit", 'enginethemes'), $package->et_number_posts);
            } else {
                $text = sprintf(__("includes <strong> %d </strong> posts in <strong> %s </strong> days.", 'enginethemes'), $package->et_number_posts, $package->et_duration);
            }
        }
        $class_select = 'class="form-group package';
        if (isset($package->et_price) && $package->et_price > 0 && isset($package_data[$sku]['qty']) && $package_data[$sku]['qty'] > 0) {
            $order = get_post($orders[$sku]);
            if ($order && !is_wp_error($order)) {
                $class_select .= ' auto-select ' . $order->post_status;
            }
        }
        $class_select .= '"';
    ?>
        <li <?php echo $class_select; ?> data-sku="<?php echo $package->sku ?>" data-id="<?php echo $package->ID ?>" data-price="<?php echo $package->et_price; ?>" <?php if ($package->et_price) { ?> data-label="<?php printf(__("You have selected: %s", 'enginethemes'), $package->post_title); ?>" <?php } else { ?> data-label="<?php _e("You are currently using the 'Free' plan", 'enginethemes'); ?>" <?php } ?>>
            <a class="select-plan">
                <p class="name-package">
                    <span class="cate-package"><?php echo $package->post_title . ' - '; ?></span>
                    <span class="size-package"><?php if ($text) {
                                                    echo $text;
                                                } ?></span>
                </p>
                <div class="content-package"><?php echo $package->post_content; ?></div>
                <div class="chose-package">
                    <p class="price"><?php echo mje_format_price($package->et_price); ?></p>
                </div>
                <?php if (isset($package_data[$sku]) && $package_data[$sku]['qty'] > 0 && $order->post_status == 'publish') : ?>
                    <h5 class="pack-purchased-label text-right"><i class="fa fa-check-circle text-success"></i> <strong class="text-uppercase text-success"> <?php _e('Purchased', 'enginethemes'); ?> </strong> - <span> <?php echo $package_data[$sku]['qty'] . ' ';
                                                                                                                                                                                                                            _e('posts left', 'enginethemes'); ?> </span>
                    </h5>
                <?php endif; ?>
            </a>
        </li>
    <?php } ?>
</ul>