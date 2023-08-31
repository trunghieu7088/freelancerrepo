<?php
global $user_ID;
$subscriber = get_mebership_of_member($user_ID);

$permalink  = home_url();
$plan = 0;


if($subscriber){
    $start_date = strtotime($subscriber->start_date);
    $start_date = date( get_option( 'date_format' ), $start_date);
   // $plan       = fre_get_plan_by_sql($subscriber->plan_sku, $subscriber->pack_type);
    $plan       = membership_get_pack($subscriber->plan_sku, $subscriber->pack_type);


}
?>
<div class="page-purchase-package-wrap">
    <div class="fre-purchase-package-box">
        <div class="step-payment-complete">
            <h2><?php _e( "Your subscription has been updated successfully.", 'enginethemes' ); ?></h2>
            <div class="fre-table">
                <?php if($plan){ ?>
                    <div class="fre-table-row">
                        <div class="fre-table-col fre-payment-date"><?php _e( "Plan Name:", 'enginethemes' ); ?></div>
                        <div class="fre-table-col"><?php echo $plan->post_title; ?></div>
                    </div>
                    <div class="fre-table-row">
                        <div class="fre-table-col fre-payment-date"><?php _e( "Start Date:", 'enginethemes' ); ?></div>
                        <div class="fre-table-col"><?php echo $start_date; ?></div>
                    </div>
                    <div class="fre-table-row">
                        <div class="fre-table-col fre-payment-type"><?php _e( "Expiration date:", 'enginethemes' ); ?></div>
                        <div class="fre-table-col"><?php echo date('M d, Y', $subscriber->expiry_time); ?></div>
                    </div>
                    <div class="fre-table-row">
                        <div class="fre-table-col fre-payment-total"><?php _e( "Total:", 'enginethemes' ); ?></div>
                        <div class="fre-table-col"><?php echo fre_membersip_price_format( $subscriber ); ?></div>
                    </div>
                <?php } else { ?>
                <?php } ?>
            </div>
            <div class="fre-view-project-btn">
                <p><?php _e( "Your subscription is now available in the system.", 'enginethemes' ); ?></p>
                <a class="fre-btn"
                   href="<?php echo $permalink; ?>"><?php _e( "Home", 'enginethemes' ); ?></a>
            </div>
        </div>
    </div>
</div>