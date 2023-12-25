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

                           
    wp_reset_postdata();
?>
    <script type="text/javascript">
        var plan_id='<?php  echo $plan_id; ?>';
        //console.log(plan_id);
    </script> 
<?php
    } 
}
?>
<div class="detail-subscription-wrapper">
    
    <div class="container detail-subscription">        
        <?php if(isset($plan_id)) : ?>
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
        <h2 class="subscription-headtitle">Subscription Checkout</h2>
        <div class="row checkout-subscription-area">

            <div class="col-sm-12 col-md-3">
                <div id="paypal-button-container-<?php echo $plan_id; ?>"></div>
            </div>

            <div class="col-sm-12 col-md-9">                
                <div class="custom-checkout-title"><?php echo $converted_plan->title; ?></div>
                <div class="custom-checkout-info">
                    <h4 class="text-summary">Summary <i id="summaryInfo" class="fa fa-info-circle"></i></h4>   
                    <div class="custom-checkout-summary">
                        <p>Number of posts per month :<span class="pull-right"><?php echo $converted_plan->plan_number_posts; ?> Posts</span></p>                        
                        <p>Transaction fee :<span class="pull-right"><?php echo $converted_plan->transaction_fee; ?> %</span></p>                          
                    </div>
                    <h4 class="custom-checkout-text-price">Total : <?php echo $converted_plan->price_text; ?></h4>   
                </div>

               <!-- <div class="text-subscribe-alert">
                    If you subscribe to another plan, the existing posts from the current plan will be lost.
                </div> -->
            </div>

        </div>
        <?php else:  ?>
             <h3 class="no-plans-text text-center">Can not find the plan. Please go back to <a href="<?php echo site_url('/subscription/'); ?>">pricing page</a> </h>
        <?php endif; ?>
    </div>

</div>
<?php
get_footer();
?>
