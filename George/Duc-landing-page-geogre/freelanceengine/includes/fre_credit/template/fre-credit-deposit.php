<?php
/**
 * Template Name: Recharge Pages
 */
global $user_ID;
$user_wallet = FRE_Credit_Users()->getUserWallet($user_ID);
$project_id = !empty($_GET['project_id']) ? $_GET['project_id'] : '';
et_write_session('project_id',$project_id);
?>
<div class="post-place-warpper" id="upgrade-account">
    <?php
    $bid_id = isset($_GET['bid_id']) ? $_GET['bid_id'] : 0;
	if( $bid_id ){
		// new section from version 1.2.3
		$bid  = get_post($bid_id);
        $deposit_info = fre_get_deposit_info($bid_id);
        //$total_pay  = (float)$deposit_info['total_pay'];

        $available = FRE_Credit_Users()->getUserWallet($user_ID);
        $et_price = $deposit_info['data_not_format']['total'] -  $available->balance;
        $total_pay = round($et_price,2);


        $text = '';
		?>
    	<ul class="fre-post-package hide fre_credit\template\fre-credit-deposit.php">
            <li data-sku="fix_sku"
                data-id="fix_id"
                data-package-type="fre_credit_fix"
                data-price="<?php echo $total_pay; ?>"
                data-title="Buy credit for project <?php echo get_the_title($bid->post_ttile);?>"
                data-description="<?php echo $text;?>">
                <label class="fre-radio" >
                    <input name="post-package"  type="radio" checked>
                    <span>Buy credit for <i> Post ab </i> project.</span>
                </label>
            </li>

        </ul>
        <input type="hidden" id="itemCheckoutID" value="<?php echo $bid->ID;?>" data-package-type ="fre_credit_fix" />
        <?php
            include dirname(__FILE__) . '/fre-credit-deposit-step4.php';
        // end new section.

	} else {
		include dirname(__FILE__) . '/fre-credit-deposit-step1.php';
		include dirname(__FILE__) . '/fre-credit-deposit-step4.php';
	} ?>
</div> <!-- end .post-place-warppe !-->
