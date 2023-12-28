<?php
/*
Template Name: Custom Subscription Template
*/
$wp_current_user=wp_get_current_user();
if ( in_array( 'client', $wp_current_user->roles, true ) ) {
    $is_client=true;
}
else
{
    $is_client=false;
}
if($is_client)
{
    wp_redirect(site_url(''));
}
?>
<?php 
get_header();
global $post;
$args_plans = array(
    'post_type'      =>  'subscription_plan', 
    'numberposts' => -1,
    'post_status' =>'publish',
    'meta_key'       => 'price_per_month', //order by price 
    'orderby'        => 'meta_value_num', 
    'order'          => 'ASC',
);
$list_plans=get_posts($args_plans);
?>
<div class="subscription-wrapper">
    <h3 class="text-center text-introduction-plan"> Select Your Subscription Plan to Begin posting services</h3>
    <div class="container custom-plans-container">
        

        <!-- template code -->
        <!--
         <div class="subscription-plan">
            <div class="subscription-plan-header">
                <h2>Free Plan</h2>
                <div class="planprice">
                    0$
                </div>
            </div>

            <div class="subscription-plan-body"> 
                <div class="subscription-payment-info">              
                    <p class="subscription-description">Invitation-only access</p>
                    <p class="subscription-payment-note">No credit card required</p>
                </div>
                <div class="subscription-information-detail">          
                    <p><i class="fa fa-check-circle"></i> 1 Free expertise listing</p>
                    <p><i class="fa fa-check-circle"></i> Homepage promotion</p>
                    <p><i class="fa fa-check-circle"></i> 10% transaction fee</p>                   
                    <p><i class="fa fa-check-circle"></i> Live chat with client</p>                  
                    <p><i class="fa fa-check-circle"></i> Include gig extra such as online course and ebooks</p>
                </div>
            </div>

            <div class="subscription-plan-footer">                   
                <a href="#" class="subscription-button">Select</a>                   
            </div>

        </div> 
        -->
         <!-- end template code -->
        <?php if(!empty($list_plans)) : ?>
                <?php foreach($list_plans as $plan): ?>
                    <?php 
                    setup_postdata( $plan ); 
                    $converted_plan=convert_subscription_plan($plan);
                    ?>
                    <div class="subscription-plan">
                        <div class="subscription-plan-header">
                            <h2><?php echo $converted_plan->title; ?></h2>
                            <div class="planprice">
                                <?php echo $converted_plan->price_text; ?>
                            </div>
                        </div>

                        <div class="subscription-plan-body"> 
                            <div class="subscription-payment-info">              
                                <p class="subscription-description"><?php echo $converted_plan->description; ?></p>
                                <p class="subscription-payment-note"><?php echo $converted_plan->subtitle; ?></p>
                            </div>
                            <div class="subscription-information-detail">   
                                <?php 
                                    if(!empty($converted_plan->advertisement))
                                    {
                                        foreach($converted_plan->advertisement as $advertisement_item)
                                        {
                                            if($advertisement_item !='false')
                                            {
                                                echo '<p> <i class="fa fa-check-circle"></i> ';
                                                echo $advertisement_item;
                                                echo '</p>';
                                            }
                                           
                                        }
                                    }
                                ?>                                      
                            </div>
                        </div>

                        <div class="subscription-plan-footer">                   
                            <a href="<?php echo site_url('/subscribe/?plan='.$converted_plan->slug); ?>" class="subscription-button">Select</a>                   
                        </div>

                    </div>
                <?php endforeach; ?>
        <?php else : ?>
            <h3 class="text-center no-plans-text">There no plans to subscribe</h3>
        <?php endif; ?>
        
        
     
    </div>

</div>
<?php get_footer(); ?>