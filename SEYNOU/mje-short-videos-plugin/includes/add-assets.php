<?php
add_action('wp_enqueue_scripts', 'mje_short_video_addAssetsFiles',99);
function mje_short_video_addAssetsFiles()
{    
    wp_enqueue_style( 'mje-short-videos-style', MJE_SHORT_VIDEOS_URL. 'assets/css/mje_short_video.css', array(), MJE_SHORT_VIDEOS_PLUGIN_VERSION ) ;

    wp_enqueue_style('mje-short-videos-swiper-css', MJE_SHORT_VIDEOS_URL.'assets/libs/swiper/swiper.css', array(), MJE_SHORT_VIDEOS_PLUGIN_VERSION);

    wp_enqueue_script('mje-short-videos-swipe-js', MJE_SHORT_VIDEOS_URL.'assets/libs/swiper/swiper.js', array(), MJE_SHORT_VIDEOS_PLUGIN_VERSION);    

    wp_enqueue_script('mje-short-videos-youtube-handle-js', MJE_SHORT_VIDEOS_URL.'assets/js/handle-youtube-player.js', array('jquery'), MJE_SHORT_VIDEOS_PLUGIN_VERSION,  
    array('strategy' => 'defer','in_footer'=>true)
     );
}