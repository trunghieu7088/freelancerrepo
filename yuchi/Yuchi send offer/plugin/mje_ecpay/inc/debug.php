<?php

Log: ecPayLog

Call: ecPayLog();

Check Verify method: ecpay_receive_response
Call: ecpay_receive_response();

$merchantTradeNo = $inputs['stageOrderPrefix'] . $inputs['orderId'];

=>
$merchantTradeNo = 'MJE_ECPAY_' . $inputs['orderId'];

'MerchantTradeNo' => $this->generate_merchant_trade_no($order_id),

=> luu order_id o day.

 //'MerchantTradeNo' => $this->generate_merchant_trade_no($order_id),

 doi thanh:
 //'MerchantTradeNo' => 'MJE_ECPAY_ORDER_PREFIC_'.$order_id,

 Form submit:

 success: function(status, response, xhr) {
            console.log(response);
            alert('1');
            return false;
            var api = response.data.api_ap;
            var form_submit = wp.template("form_submit");
            var formFullInfo = form_submit(api);
            console.log(formFullInfo);
            $("#checkout-step2").html(formFullInfo);
            $("#allpay_payment_form").submit();

        }