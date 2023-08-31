<?php
add_action('init', 'AffInit');
function AffInit() { 
        session_start();				
		$ref=$_GET['ref'];
		$ref_user=get_user_by('login',$ref);
 		if( $ref_user )
		{
			 $_SESSION['refferal']=$ref_user->ID;
		}			      

}

add_action('wp_enqueue_scripts', 'override_paymentjs');
function override_paymentjs()
{    
    wp_deregister_script('order-mjob');
    wp_deregister_script('single-mjob');
    wp_enqueue_script('order-mjob', get_stylesheet_directory_uri() . '/assets/js/payment.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front',
            ), ET_VERSION, true);

     wp_enqueue_script('single-mjob', get_stylesheet_directory_uri() . '/assets/js/single-mjob.js', array(
            'jquery',
            'underscore',
            'backbone',
            'appengine',
            'front',
            ), ET_VERSION, true);

}

//add_action('init','testwp');

function testwp()
{
	global $wpdb;                            
                            $affiliate_info = $wpdb->get_row( "SELECT * FROM wp_affiliate_wp_affiliates WHERE user_id = 7", ARRAY_A );
                            var_dump($affiliate_info);
                            if($affiliate_info)
                            {
                            	$affiliate_id=$affiliate_info->affiliate_id;
                                $table='wp_affiliate_wp_referrals';
                                $data=array('affiliate_id'=> 7 ,'customer_id'=>get_current_user_id(),'description'=>'MjE Checkout','status' => 'unpaid','amount'=>10,'currency'=>'USD','context'=>'mje_checkout','payout_id'=>0,'date'=>'notnow');
                               // $format=array('%d','%d','%s','%s','%d','%s','%s','%d','%s');
                                //$result=$wpdb->insert($table,$data,$format);
                                $result=$wpdb->insert($table,$data);
                                var_dump($result);
                                echo '<br>';
                                var_dump($wpdb->last_error);
                            }
}

//add_action('init','testaffoption');
function testaffoption()
{
    
    $affsettings = get_option('affwp_settings');
    echo $affsettings['referral_rate'];
    echo '<br>';
    echo $affsettings['currency'];
}