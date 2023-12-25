<?php
/*
Template Name: My Subscription Template
*/
?>
<?php
if(!is_user_logged_in())
{
    wp_redirect(site_url());
}
get_header();
$current_user=wp_get_current_user();
$user_profile_id=get_user_meta($current_user->ID,'user_profile_id',true);
$current_subscription=get_post_meta($user_profile_id,'current_subscription_plan',true);
$subscription_status=get_post_meta($user_profile_id,'subscription_status',true);
if($current_subscription && $subscription_status=='Active')
{   
    $converted_current_subscription=convert_subscription($current_subscription);    
}

?>
 <div id="content">
        <div class="block-page">
            <div id="invoices-container" class="container dashboard withdraw">
                <div class="row title-top-pages">
                    <p class="block-title"><?php _e('My Subscription', 'enginethemes'); ?></p>
                    <p><a href="<?php echo et_get_page_link('dashboard'); ?>" class="btn-back"><i class="fa fa-angle-left"></i><?php _e('Back to dashboard', 'enginethemes'); ?></a></p>
                </div>
                <div class="row profile">
                    <div class="col-lg-3 col-md-3 col-sm-12 col-sx-12 profile">
                        <?php get_sidebar('my-profile'); ?>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-12 col-sx-12">
                        <div class="subscription-box-info">
                            <div class="my-subscription-title">Current Subscription</div>
                            <div class="subscription-line"></div>
                            <div class="my-subscription-info">
                            <?php if($current_subscription && $subscription_status=='Active') : ?>
                                <table>
                                     <tr>
                                        <td>Plan Name</td>
                                        <td class="highlight-info"><?php echo $converted_current_subscription->plan_name;  ?></td>
                                    </tr>
                                    <tr>
                                        <td>Remaining Posts</td>
                                        <td class="highlight-info"><?php echo $converted_current_subscription->remaining_post_text;  ?></td>
                                    </tr>                                   
                                    <tr>
                                        <td>Price per month</td>
                                        <td class="highlight-info"><?php echo $converted_current_subscription->plan_price_text;  ?></td>
                                    </tr>
                                    <tr>
                                        <td>Subscribe on (Y-m-d)</td>
                                        <td class="highlight-info"><?php echo $converted_current_subscription->subscription_date_raw;  ?></td>
                                    </tr>

                                    <tr>
                                        <td>Last renew on</td>
                                        <td class="highlight-info"><?php echo $converted_current_subscription->subscription_date_show;  ?></td>
                                    </tr>
                                    
                                    <tr>
                                        <td>Auto renew on</td>
                                        <td class="highlight-info"><?php echo $converted_current_subscription->next_subscription_date_show;  ?></td>
                                    </tr>
                                    
                                </table>
                            </div>
                            <div class="subscription-action">
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#cancelSubscriptionModal">Cancel subscription</a> or <a href="<?php echo site_url('subscription'); ?>">Change Plan</a>                                
                            </div>
                            <?php else: ?>
                                <p class="text-no-subscribe">You have not subscribed to any plans yet. <a href="<?php echo site_url('subscription'); ?>">Subscribe here</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- modal cancel subscription -->    
<div id="cancelSubscriptionModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Cancel subscription ?</h4>
      </div>
      <div class="modal-body">
        <h4 class="text-center text-warning cancel-subscription-warning">
            If you cancel the subcription, all the services that you have submitted will be archived.
            <br>
            You cannot submit services anymore.
            <br>
            You cannot undo this action.
        </h4>
      </div>
      <div class="modal-footer">
        <button type="button" data-unsubscribe-nonce="<?php echo wp_create_nonce('unsubcribe_plan_nonce'); ?>" data-subscription-id="<?php echo $current_subscription; ?>" id="cancelSubscriptionbtn" name="cancelSubscriptionbtn" class="btn btn-danger">Cancel Subscription</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<!-- end modal cancel subscription -->
<?php
get_footer();
?>