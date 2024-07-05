<?php
if ($mjob_order->seller_id == get_current_user_id()) {
    $is_seller = true;
}
?>
<div class="box-shadow">
    <div class="order-detail-price">
        <div class="order-price">

            <p class="title-cate"><?php _e('Price', 'enginethemes'); ?></p>
            <p class="price-items"><?php echo $mjob_order->mjob_price_text; ?></p>
            <p class="time-order"><i class="fa fa-clock-o" aria-hidden="true"></i><?php _e('Time of delivery', 'enginethemes'); ?></p>
            <?php
            if (!$mjob_order_delivery = get_post_meta($mjob_order->ID, 'mjob_order_delivery', true))
                $mjob_order_delivery = $mjob_order->mjob_time_delivery;
            ?>
            <p class="days-order"><?php echo sprintf(__('%s day(s)', 'enginethemes'), $mjob_order_delivery); ?></p>
        </div>
        <div class="total-order">
            <p class="title-cate"><?php _e('Other Fees', 'enginethemes'); ?></p>

            <p class="no-extra"><?php _e('Buyer Commission Fee', 'enginethemes');
                                echo ' (' . $mjob_order->fee_commission . '%)';
                                ?>
                <span class="order-extra-fee">
                    <?php echo mje_shorten_price(get_post_meta($mjob_order->ID, 'fee_commission_value', true)); ?>
                </span>
            </p>
            <?php

            if (mje_enable_extra_fee()) {

                $first_label = ae_get_option('extra_fee_percent_label');
                $second_label = ae_get_option('extra_fee_fixed_label');


                $extra_fee_fixed = get_post_meta($mjob_order->ID, 'extra_fee_fixed', true);
                $extra_fee_percent = get_post_meta($mjob_order->ID, 'extra_fee_percent', true);
                $extra_fee_percent_value = get_post_meta($mjob_order->ID, 'extra_fee_percent_value', true);


            ?>
                <div class="full" style="position: relative;">

                    <p class="no-extra"><?php echo $second_label; ?> <span class="order-extra-fee"><?php echo mje_shorten_price($extra_fee_fixed); ?></span></p>
                    <p class="no-extra"><?php echo $first_label; ?> <span class="order-extra-fee"><?php echo mje_shorten_price($extra_fee_percent_value); ?></span></p>
                </div>
            <?php } ?>
        </div>
        <div class="order-extra">
            <p class="title-cate"><?php _e('Extra', 'enginethemes'); ?></p>
            <?php
            $mjob_price = $mjob_order->mjob_price;
            if (!empty($mjob_order->extra_info)) :
            ?>
                <ul>
                    <?php
                    foreach ($mjob_order->extra_info as $key => $extra) {
                        $extra = (object)$extra;
                    ?>
                        <li>
                            <p class="extra-title"><?php echo $extra->post_title; ?></p>
                            <p class="price-items"><?php echo mje_shorten_price($extra->et_budget);  ?></p>
                        </li>
                    <?php } ?>
                </ul>
            <?php else : ?>
                <p class="no-extra">
                    <?php _e('There are no extra services', 'enginethemes'); ?>
                </p>
            <?php endif; ?>

        </div>    
        <!-- custom code here -->    
        <?php 
        $order_product_id=get_post_meta($mjob_order->ID,'et_order_product_id',true);
        $is_ship=get_post_meta( $mjob_order->ID,'is_ship',true);
        $shipping_cost=get_post_meta( $mjob_order->ID,'shipping_cost',true);
        $shipping_address=get_post_meta($mjob_order->ID,'shipping_address',true);
            if($is_ship=='true') :
        ?>        
            <div class="total-order shipping-info-orderPage">
            <p class="title-cate">Shipping Information</p>            
            <p class="no-extra">
                Address            
                <span class="order-extra-fee">
                <?php echo $shipping_address; ?>
                </span>
            </p>
            <p class="no-extra">
                Cost           
                <span class="order-extra-fee">
                <?php echo mje_shorten_price($shipping_cost); ?>
                </span>
            </p>
            
            </div>
        <?php
            endif;
        ?>
        <!-- end custom code here -->
        <div class="total-order">
            <p class="title-cate"><?php (isset($is_seller) && $is_seller == true) ? _e('Charged', 'enginethemes') : _e('Total', 'enginethemes'); ?></p>
            <p class="price-items <?php if (isset($is_seller) && $is_seller == true) echo 'charged_price_show_to_seller'; ?>"><?php echo mje_shorten_price($mjob_order->amount); ?></p>
        </div>
        <?php
        if (isset($is_seller) && $is_seller == true) : // start seller revenue block
            $gross_revenue = (get_post_meta($mjob_order->ID, 'seller_gross_income', true));
            $gross_revenue_text = ($gross_revenue != '' && $gross_revenue > 0)
                ? mje_shorten_price($gross_revenue)
                : '';
            //var_dump(ae_get_option('order_commission_buyer', 0));
            $seller_commission = (get_post_meta($mjob_order->ID, 'seller_commission', true))
                ? (float) get_post_meta($mjob_order->ID, 'seller_commission', true)
                : 0;
            $seller_commission_value = (get_post_meta($mjob_order->ID, 'seller_commission_value', true));
            $seller_commission_value_text = ($seller_commission_value != '' && $seller_commission_value > 0)
            ? mje_shorten_price(get_post_meta($mjob_order->ID, 'seller_commission_value', true))
            : '';
            $net_revenue = mje_shorten_price($mjob_order->real_amount);
        ?>

            <?php if ($gross_revenue_text != '' && $seller_commission_value_text != '') : ?>
                <div class="order-extra">
                    <p class="title-cate"><?php _e('Seller Revenue', 'enginethemes'); ?></p>
                    <ul>
                        <li>
                            <p class="extra-title"><?php _e('Subtotal', 'enginethemes'); ?></p>
                            <p class="price-items"><?php echo $gross_revenue_text;  ?></p>
                        </li>
                        <!-- custom code here ( show shipping cost in seller revenue if yes) -->
                        <?php if($is_ship=='true') : ?>
                            <li>
                                <p class="extra-title">Shipping Cost</p>
                                <p class="price-items"><?php echo mje_shorten_price($shipping_cost);  ?></p>
                            </li>
                        <?php endif; ?>
                         <!-- end custom code here -->
                        <li>
                            <p class="extra-title">
                                <?php _e('Seller Commission Fee', 'enginethemes');
                                if ($seller_commission != 0) echo ' (' . $seller_commission . '%) '; ?>
                            </p>
                            <p class="price-items"><?php echo ' - ' . $seller_commission_value_text;  ?></p>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="total-order">
                <p class="title-cate"><?php _e('Net Revenue', 'enginethemes'); ?></p>
                <p class="price-items"><?php echo $net_revenue; ?></p>
            </div>
        <?php
        endif; // end seller revenue block
        ?>
    </div>
</div>