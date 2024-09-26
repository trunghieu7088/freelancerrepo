<?php
/*
Template Name: Checkout Requests page
*/
?>
<?php
get_header();
$is_cart=false;
$admin_data=AdminData::get_instance();
if(isset($_SESSION['request_cart']) && !empty($_SESSION['request_cart']))
{
    $is_cart=true;
    $request_cart_list=$_SESSION['request_cart'];
    $total_item=count($request_cart_list);
    $price_per_item=$admin_data->getValue('moving_request_price');
    
    $total_price=$total_item * $price_per_item;
    
    $request_collection=implode(',',$request_cart_list);
}
$moving_instance=Moving_Platform_Main::get_instance();
?>
<div class="container">
    <div class="checkout-title">
        <?php _e('Your cart','moving_platform'); ?>
    </div>
    <?php if($is_cart): ?>
    <div class="checkout-wrapper">
        <div class="row">
            
            <div class="col-md-8 col-lg-8 col-sm-12 product-request-list">

                <!-- request item headline-->
                <div class="product-request-item custom-headline">
                    <div class="product-request-info">
                        <p class="checkout-label-title"><?php _e("Item List",'moving_platform'); ?></p>
                    </div>
                    <div class="product-request-price">
                        <p class="checkout-label-title"><?php _e("Price",'moving_platform'); ?></p>
                    </div>
                </div>
                <!-- end request item headline -->

                <?php foreach($request_cart_list as $request_cart_item): ?>    
                    <?php 
                        $item_info=$moving_instance->convert_moving_request(get_post($request_cart_item));
                    ?>                
                <!-- request item -->                 
                 <div class="product-request-item custom-request-item" data-request-cart-item="<?php echo $item_info->ID; ?>">
                    <div class="product-request-info custom-request-grid">
                        <p class="custom-request-title"># <?php echo $item_info->post_title; ?></p>
                        <p class="custom-location-info">
                            <?php _e('Departure: ','moving_platform'); ?> <?php echo $item_info->departure_address.' , '.$item_info->departure_city.' , '.__('Postal code:','moving_platform').' '.$item_info->postal_code_departure; ?>
                            <br>
                            <?php _e('Arrival: ','moving_platform'); ?> <?php echo $item_info->arrival_address.' , '.$item_info->arrival_city.' , '.__('Postal code:','moving_platform').' '.$item_info->postal_code_arrival; ?>
                        </p>
                        <p class="custom-date-info">
                            <i class="fas fa-plane-departure"></i> <span><?php echo $item_info->arrival_date; ?></span>
                            <i class="second-item fas fa-plane-arrival"></i> <span><?php echo $item_info->departure_date; ?></span>
                        </p>
                    </div>
                    <div class="product-request-price custom-request-price-grid">
                        <p class="custom-product-price"><?php echo $price_per_item ?> €</p>
                        <p class="custom-remove-product"><a href="#" data-remove-cart-item="<?php echo $item_info->ID; ?>" class="request-remove-action m-remove-item"><i class="fa fa-trash"></i></a></p>
                    </div>
                </div>
                <!-- end request item -->
                <?php endforeach; ?>

                <div class="add-more-item">
                    <p><a class="add-more-btn" href="<?php echo site_url('all-requests'); ?>"><?php _e('Add more item','moving_platform'); ?></a></p>
                </div>

            </div>

            <div class="col-md-4 col-lg-4 col-sm-12 payment-section">
                <div class="checkout-payment-title">
                    <?php _e('Payment info','moving_platform'); ?>
                </div>
                <div class="payment-main-content">
                    <form class="multiple-checkout-form" id="multiple-checkout-form">
                        <!-- info to submit -->
                        <input type="hidden" id="action" name="action" value="m_checkout_requests">
                        <input type="hidden" name="m_checkout_nonce" id="m_checkout_nonce" value="<?php echo wp_create_nonce('m_checkout_nonce'); ?>">                                        
                        <input type="hidden" id="total_price" name="total_price" value="<?php echo $total_price; ?>">
                        <input type="hidden" id="request_collection" name="request_collection" value="<?php echo $request_collection; ?>">
                        <!-- end -->
                        <div class="payment-summarize-text">
                            <p class="payment-sub-info"><?php _e('Number of items','moving_platform'); ?></p>
                            <p id="total_items"><?php echo $total_item; ?></p>
                        </div>
                        <div class="payment-summarize-text">
                            <p class="payment-sub-info"><?php _e('Total Price','moving_platform'); ?></p>
                            <p><span id="total_price_text"><?php echo $total_price; ?></span> €</p>
                        </div>
                        <div class="custom-break-line"></div>
                        <input class="multiple-stripe-input" type="text" name="billing_name" id="billing_name" placeholder="<?php _e('Billing name','moving_platform'); ?>" value="">
                        <!-- use to show error -->
                        <span></span>

                        <div class="multiple-checkot-stripe-card-container">
                            <!-- A Stripe Element will be inserted here. -->
                            <div id="m-custom-stripe-cardNum" class="m-stripe-fields"></div>  
                            <div id="m-custom-stripe-expiry" class="m-stripe-fields"></div>  
                            <div id="m-custom-stripe-cvc" class="m-stripe-fields"></div>  
                            <!-- A Stripe Element will be inserted here. -->               
                        </div> 
                        
                        <!-- Stripe card error -->               
                        <div id="m-stripe-card-errors"></div>

                        <button class="m-checkout-btn" type="submit" name="m-checkout-stripe" id="m-checkout-stripe">
                            <i class="fa-solid fa-credit-card"></i>
                            <?php _e('Checkout','moving_platform'); ?>                            
                        </button>

                    </form>
                </div>
            </div>

            
        </div>                   
    </div>
    <?php else: ?>
        <p><?php _e('No items in cart.','moving_platform'); ?> <a href="<?php echo site_url('all-requests'); ?>"><?php _e('Browse list to add item','moving_platform'); ?></a></p>
    <?php endif; ?>
    
</div>
<?php
get_footer();
