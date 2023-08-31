<?php
function getEcpaySettings(){
	$settings = array(
		'mer_id' 		=>'',
		'MerchantID' 	=> '',
		'hash_key' 		=> '',
		'hash_iv' 		=> '',
		'pay_use' 		=> 2 ,
	);

	$allpay                			= ae_get_option('allpay');
	$settings['mer_id']    			= trim($allpay['MerchantID']);
	$settings['MerchantID']    		= trim($allpay['MerchantID']);
	$settings['hash_key']  			= trim($allpay['hash_key']);
	$settings['hash_iv']   			= trim($allpay['hash_iv']);
	$settings['IgnorePayment'] 		= trim($allpay['IgnorePayment']);
	if( isset($allpay['pay_use']) && !empty($allpay['pay_use']) )
		$settings['pay_use'] 		= $allpay['pay_use'];

	return (object) $settings;
}