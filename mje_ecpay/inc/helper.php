<?php
if(!class_exists('ECPay_EncryptType', false))
{
    abstract class ECPay_EncryptType {
        // MD5(預設)
        const ENC_MD5 = 0;

        // SHA256
        const ENC_SHA256 = 1;
    }
}
function getFeedback($data) {
        // Filter inputs
        $whiteList = array(
            'hashKey',
            'hashIv',
        );
        $inputs = $this->only($data, $whiteList);

        // Set SDK parameters
        $this->sdk->MerchantID = $this->getMerchantId();
        $this->sdk->HashKey = $inputs['hashKey'];
        $this->sdk->HashIV = $inputs['hashIv'];
        $this->sdk->EncryptType = ECPay_EncryptType::ENC_SHA256;
        try {
            $feedback = $this->sdk->CheckOutFeedback();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if ($error === 'CheckMacValue verify fail.') {
                // 定期定額可能有 MD5 壓碼的舊訂單，增加 MD5 壓碼相容性
                $this->sdk->EncryptType = ECPay_EncryptType::ENC_MD5;
                $feedback = $this->sdk->CheckOutFeedback();
            } else {
                throw new Exception ($error);
            }
        }
        if (count($feedback) < 1) {
            throw new Exception($this->provider . ' feedback is empty.');
        }
        return $feedback;
    }

function getEcpayValidFeedback($data) {
    $feedback = $this->getFeedback($data); // feedback
    $data['merchantTradeNo'] = $feedback['MerchantTradeNo'];
    $info = getEcpayTradeInfo($data); // Trade info

    // Check the amount
    if ( $feedback['TradeAmt'] !== $info['TradeAmt'] )  {
        throw new Exception('Invalid ECPay feedback.(1)');
    }

    return $feedback;
}
/**
 * clone method funciton only
 * **/
function onlyHelper($source = array(), $whiteList = array()) {
        $variables = array();

        // Return empty array when do not set white list
        if (empty($whiteList) === true) {
            return $source;
        }

        foreach ($whiteList as $name) {
            if (isset($source[$name]) === true) {
                $variables[$name] = $source[$name];
            } else {
                $variables[$name] = '';
            }
        }
        return $variables;
    }

/** clone method getTradeInfo
 *
 **/
function getEcpayTradeInfo($data) {
        // Filter inputs
    $whiteList = array(
        'hashKey',
        'hashIv',
        'merchantTradeNo',
    );
    $inputs = onlyHelper($data, $whiteList);
    $settings = getEcpaySettings();
    // Set SDK parameters
    $sdk  = $this->factory();
    $this->sdk->MerchantID = $settings->mer_id;
    $this->sdk->HashKey = $inputs['hashKey'];
    $this->sdk->HashIV  = $inputs['hashIv'];
    $this->sdk->ServiceURL = $this->getUrl('queryTrade');
    $this->sdk->EncryptType = $this->encryptType;
    $this->sdk->Query['MerchantTradeNo'] = $inputs['merchantTradeNo'];
    $info = $this->sdk->QueryTradeInfo();
    if (count($info) < 1) {
        throw new Exception($this->provider . ' trade info is empty.');
    }
    return $info;
}