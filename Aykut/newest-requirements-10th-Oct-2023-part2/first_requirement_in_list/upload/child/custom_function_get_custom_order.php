<?php
add_action( 'wp_ajax_get_info_custom_order', 'get_info_custom_order_init' );

function get_info_custom_order_init()
{
    $custom_order=get_post($_REQUEST['customOrderIDSent']);
    if($custom_order)
    {
        $amountpage=get_post_meta($custom_order->ID,'amountpage',true);
        $topic=get_post_meta($custom_order->ID,'topic',true);        
        $kindwork=get_term(get_post_meta($custom_order->ID,'kindwork',true));
        $kindworkID=$kindwork->term_id;
        $etd=get_post_meta($custom_order->ID,'custom_order_deadline',true);
        
        $response['success']=true;
        $response['amountpage']=$amountpage;
        $response['topic']=$topic;
        $response['kindworkID']=$kindworkID;
        $response['etd']=$etd;        
    }
    else
    {
        $response['success']=false;
    }        
    wp_send_json($response);
    die();
}