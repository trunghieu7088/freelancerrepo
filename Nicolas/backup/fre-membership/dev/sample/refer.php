<?php

function schedule_trigger_event() {
	$cron = $GLOBALS['membership_cron'] ;
	add_action( 'excute_membership_event',array($cron, 'auto_checking_subscription') );
    wp_clear_scheduled_hook( 'excute_membership_event' );

    if (! wp_next_scheduled ( 'excute_membership_event' )) {
        wp_schedule_event( time(), 'hourly', 'excute_membership_event' );
    }
        if (! wp_next_scheduled ( 'excute_membership_event' )) {
    	et_cron_log('Init: register schedule_event in init');
        wp_schedule_event( time(), 'every_six_hours', 'excute_membership_event' );

    }
}
// add_action( 'init', 'schedule_trigger_event' );
function membership_text_translate( $translated, $text, $domain ) {
    if($domain == 'enginethemes'){
        return '111';
    }
    return $translated;
}
//add_filter( 'gettext', 'membership_text_translate', 10, 3 );