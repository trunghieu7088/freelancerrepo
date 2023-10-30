<?php
function format_shipping_cost($shipping_cost)
{
    if($shipping_cost < 0 || !$shipping_cost)
    {
        $shipping_cost=0;
    }
    return $shipping_cost;
}

// add shipping cost when post a mjob
add_action('ae_insert_mjob_post','add_shipping_cost_mjob',10,2);

function add_shipping_cost_mjob($result,$args)
{
    $shipping_cost=format_shipping_cost($args['shipping_cost']);    
    update_post_meta($result,'shipping_cost',$shipping_cost);
    update_post_meta($result,'provide_shipping_service',true);
}

// add shipping cost when the users purchase a mjob if yes
add_action('after_insert_mjob_order','add_shipping_cost_order',10,2);

function add_shipping_cost_order($result,$request)
{
    
    if($request['is_ship']=='true')
    {
        update_post_meta($result->ID,'is_ship',$request['is_ship']);
        update_post_meta($result->ID,'shipping_cost',$request['shipping_cost']);
        
        $amount=get_post_meta($result->ID,'amount',true);
        $real_amount=get_post_meta($result->ID,'real_amount',true);
        
        $amount=$amount+$request['shipping_cost'];
        $real_amount=$real_amount+$request['shipping_cost'];
        
        update_post_meta($result->ID,'amount',$amount);
        update_post_meta($result->ID,'real_amount',$real_amount);
        update_post_meta($result->ID,'shipping_address',$request['shipping_address']);
    }
    else
    {
        update_post_meta($result->ID,'shipping_cost',0);
        update_post_meta($result->ID,'is_ship','false');
    }   
}

add_action('mje_after_process_payment','add_shipping_cost_invoice',10,2);

function add_shipping_cost_invoice( $payment_return, $data)
{
    $custom_product_id=get_post_meta($data['order_id'],'et_order_product_id',true);
    $shipping_cost=format_shipping_cost(get_post_meta($custom_product_id,'shipping_cost',true));    
    $shipping_address=get_post_meta($custom_product_id,'shipping_address',true);    
    $is_ship=get_post_meta($custom_product_id,'is_ship',true);
    update_post_meta($data['order_id'],'shipping_cost',$shipping_cost);
    update_post_meta($data['order_id'],'shipping_address',$shipping_address);
    update_post_meta($data['order_id'],'is_ship',$is_ship);
}

// add shipping cost to the dispute if the admin choose the buyer win and refund to him

add_action('mje_resolved_mjob_order','add_shipping_cost_for_dispute',10,1);

function add_shipping_cost_for_dispute($request)
{
    $order_id = $request['post_parent'];
    $is_ship=get_post_meta($order_id,'is_ship',true);
    if($is_ship=='true')
    {
        $shipping_cost=get_post_meta($order_id,'shipping_cost',true);
        if(isset($request['winner']) || !empty($request['winner']))
        {   
                
                $refund_buyer_fee = isset($request['check_for_refun_fee_check'][0]) ? 1 : 0;
                if ($refund_buyer_fee) {
                $buyer = get_post_meta($order_id, 'buyer_id', true);
                $buyer_commission=get_post_meta($order_id, 'fee_commission', true);
                if ($buyer == $request['winner']) {
                        $wallet       = AE_WalletAction()->getUserWallet($buyer);
                        if($buyer_commission > 0)
                        {                                                
                            $shipping_cost_commission = $shipping_cost * ($buyer_commission * 0.01) / (1 + $buyer_commission * 0.01);
                            $shipping_cost=$shipping_cost - $shipping_cost_commission;
                        }
        
                        $wallet->balance += $shipping_cost;
                        AE_WalletAction()->setUserWallet($buyer, $wallet);                    
                    
                    }
                }                    
        }
    }
    
}