<?php
/*
Template Name: Custom Detail Subscription Template
*/
?>
<?php
$wp_current_user=wp_get_current_user();
if ( in_array( 'client', $wp_current_user->roles, true ) ) {
    $is_client=true;
}
else
{
    $is_client=false;
}

if(!is_user_logged_in() || $is_client)
{
    wp_redirect(site_url());
}
get_header();


if(isset($_GET['plan']) && !empty($_GET['plan']))
{
    
    $args = array(
        'name'           => $_GET['plan'],
        'post_type'      => 'subscription_plan',  // Replace with your post type if it's different
        'posts_per_page' => 1,       // Limit to 1 result
        'post_status' =>'publish',
    );
    
    $plan_info = new WP_Query($args);
    if ($plan_info->have_posts()) 
    {            
        $plan_info->the_post();
        $wp_plan=get_post(get_the_ID());
        $converted_plan=convert_subscription_plan($wp_plan);
        $plan_id = get_post_meta(get_the_ID(),'paypal_plan_id',true);
        $is_free_plan=get_post_meta(get_the_ID(),'is_free_plan',true);
        if($is_free_plan=='true')
        {
            $user_profile_id=get_user_meta($wp_current_user->ID,'user_profile_id',true);
            $free_plan_collection=get_post_meta($user_profile_id,'free_plan_collection',true);
            if(gettype($free_plan_collection)=='array' && !empty($free_plan_collection))
            {
                if(in_array( $plan_id, $free_plan_collection))
                {
                    $is_resubscribe_free_plan=true;
                }
                else
                {
                    $is_resubscribe_free_plan=false;
                }
            }
            else
            {
                $is_resubscribe_free_plan=false;
            }           
        }
        else
        {
            $is_resubscribe_free_plan=false;
        }

                           
    wp_reset_postdata();
?>
    <script type="text/javascript">
        var plan_id='<?php  echo $plan_id; ?>';
        //console.log(plan_id);
    </script> 
<?php
    } 
}
$capability_to_resubscribe=capability_to_resubscribe($plan_id);
?>
<div class="detail-subscription-wrapper">
    
    <div class="container detail-subscription">        
        <?php if(isset($plan_id) && $capability_to_resubscribe==true && $is_resubscribe_free_plan != true) : ?>
            <!-- hidden form to submit when the users make payment successfully -->
            <form id="subscriptionForm" action="" name="subscriptionForm">
                <input type="hidden" id="subscribe_plan_nonce" name="subscribe_plan_nonce" value="<?php echo wp_create_nonce('subscribe_plan_nonce'); ?>">                             
                <input type="hidden" name="action" id="action" value="subscribePlan">
                <input type="hidden" name="wp_plan_id" id="wp_plan_id" value="<?php echo $wp_plan->ID; ?>">
                <input type="hidden" name="paypal_plan_id" id="paypal_plan_id" value="<?php  echo $plan_id; ?>">
                <input type="hidden" name="paypal_order_id" id="paypal_order_id" value="">                
                <input type="hidden" name="paypal_subscription_id" id="paypal_subscription_id" value="">
                <input type="hidden" name="paypal_paymentSource" id="paypal_paymentSource" value="">                
            </form>
            <!-- end form -->
        <h2 class="subscription-headtitle-newcode">Subscription</h2>
        <div class="row checkout-subscription-area">

            <div class="col-sm-12 col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-plan-custom-header">
                            <tr>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody class="table-checkout-plan-info">
                            <tr>
                                <td><?php echo $converted_plan->title; ?></td>
                                <td>1</td>
                                <td><?php echo $converted_plan->price_text; ?></td>
                                <td><?php echo $converted_plan->price_text; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div> 

                <div class="row payment-to-info">
                    <div class="col-sm-12 col-md-6 right-column-payment">
                        <h3 class="text-payment-to">Payment to</h3>
                        <p class="text-payment-receive">Guide per hour</p>
                        <p class="text-payment-address">Address</p>
                    </div>
                    
                    <div class="col-sm-12 col-md-6 left-column-payment">
                        <div class="left-column-payment-item"><?php echo $converted_plan->price_text; ?></div>
                        <div class="left-column-payment-item">Include tax</div>
                        <div class="left-column-payment-item text-uppercase">total: <?php echo $converted_plan->price_text; ?></div>
                        
                        <?php if($converted_plan->is_free_plan == 'true'): ?>
                            <div class="subscribe-free-plan-area">
                                <a id="free-subscribe-plan-btn" href="javascript:void(0)">Free Subscribe</a>
                            </div>
                        <?php else: ?>                           
                            <div id="paypal-button-container-<?php echo $plan_id; ?>"></div>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="row payment-tos">
                    <div class="col-sm-12 col-md-12 payment-tos-col">
                        <h3 class="payment-tos-text">TERM & CONDITIONS</h3>
                        <ul class="text-tos-li">
                            <li>You agree to use our services in compliance with all applicable laws and regulations.</li>
                            <li>You shall not engage in any prohibited activities as outlined in our policies.</li>
                            <li>You agree to pay the fees as specified by Guide per hour. Payment terms, methods, and billing cycles are outlined in our pricing and payment policies.</li>
                            <li>details about subscription terms, renewal processes, and cancellation policies are provided in our <a href="#">term of service.</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row text-cancel-new">
                    <div class="col-sm-12 col-md-12 text-cancel-new-item">
                        <h4>You can cancel or upgrade your plan at any time from your dashboard</h4>
                    </div>
                </div>

            </div>            

        </div>
        <?php else:  ?>
            <?php if($capability_to_resubscribe==false): ?>
                <h3 class="no-plans-text text-center">You are subscribing to this plan. Please go back to <a href="<?php echo site_url('/subscription/'); ?>">pricing page</a> to change plan</h>                              
            <?php else:  ?>    

                <?php if($is_resubscribe_free_plan==true): ?>
                    <h3 class="no-plans-text text-center">Users who have previously subscribed to this plan and later unsubscribed cannot resubscribe to it because this is free plan. Please go back to <a href="<?php echo site_url('/subscription/'); ?>">pricing page</a> and change plan </h>
                <?php else: ?> 
                    <h3 class="no-plans-text text-center">Can not find the plan. Please go back to <a href="<?php echo site_url('/subscription/'); ?>">pricing page</a> </h>
                <?php endif; ?>

            <?php endif; ?>
             
        <?php endif; ?>
    </div>

</div>
<?php
get_footer();
?>
