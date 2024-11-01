<?php
class MJE_Short_Videos_Frontend
{
    public static $instance;

    function __construct(){        		       
		$this->init_hook();     
	}

    function init_hook()
    {
        add_shortcode('mje_short_video_slider_section',array($this,'mje_short_video_slider_section_init') ,99);           
        
        add_action('wp_ajax_short_video_upload_file',array($this,'short_video_upload_file_action'),99);
        add_action('wp_ajax_delete_short_video_on_server',array($this,'delete_short_video_on_server_action'),99);        
        
        //display video / youtube player for description
        add_action('mjob_post_after_description',array($this,'insert_video_player_mjob'),99,1);        
        
    }

    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //upload short video for service
    function short_video_upload_file_action()
    {
        if (!is_user_logged_in()) {
            die();
        }
        if (!check_ajax_referer('short_video_upload_nonce', $_POST['_ajax_nonce'])) {
            die();
        }
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        if (isset($_FILES['file']) && isset($_POST['chunk']) && isset($_POST['chunks'])) 
        {
            $file = $_FILES['file'];
            $chunk = intval($_POST['chunk']);
            $chunks = intval($_POST['chunks']);

            $upload_dir = wp_upload_dir();
            //$targetDir = $upload_dir['path'] . '/';
            $targetDir = str_replace('\\', '/', $upload_dir['path']).'/';
            $targetFile = $targetDir . sanitize_file_name($_REQUEST['custom_file_name']);  

            // Append the chunk number to the target file     
            $targetFileChunked = $targetFile.'_part' . $chunk;
            if (move_uploaded_file($file['tmp_name'], $targetFileChunked)) 
            {
                if ($chunk == $chunks - 1) {
                    // All chunks have been uploaded, combine them
                    $combinedFile = $targetFile;
                    for ($i = 0; $i < $chunks; $i++) {
                        $chunkedFile = $targetFile . '_part' . $i;
                        file_put_contents($combinedFile, file_get_contents($chunkedFile), FILE_APPEND);
                        // Delete the chunked file
                        unlink($chunkedFile);
                    }                               
                    $attachment = array(
                        'guid'           => $upload_dir['url'].'/'.sanitize_file_name($_REQUEST['custom_file_name']),
                        'post_mime_type' => $_REQUEST['custom_file_type'],
                        'post_title'     =>  sanitize_file_name($_REQUEST['custom_file_name']),
                        'post_content'   => 'attached Video for service',
                        'post_status'    => 'inherit'
                    );
                    
                    
                    $attach_id = wp_insert_attachment($attachment, $_REQUEST['custom_file_name']);                                         
                    
                    if ($attach_id) {
                        update_post_meta($attach_id, '_wp_attached_file', str_replace(site_url() . '/wp-content/uploads/', '', $upload_dir['url'].'/'.sanitize_file_name($_REQUEST['custom_file_name'])));                    
                        update_post_meta($attach_id,'custom_attachment_type',$file_type);                                        
                        update_post_meta($attach_id,'is_temp_short_video','true');
                                            
                        $response = array(
                            'success' => true,
                            'message' => 'File uploaded successfully.',
                            'attach_id' => $attach_id,
                        );
                        wp_send_json($response);
                }

                }
            }
        }

    }

    //delete short video on server
    function delete_short_video_on_server_action()
    {
        if (!is_user_logged_in()) {
            die();
        }
        $result_delete=wp_delete_attachment($_POST['attach_file_id_delete'],true);
        if($result_delete)
        {
            $response = array(
                'success' => true,
                'message' => __('File has been deleted.','moving_platform'),           
            );
        }
        else{
            $response = array(
                'success' => false,                
                'message' => __('Failed to delete file','moving_platform'),            
            );
        }
    
        wp_send_json($response);
        die();
    }
    
    function insert_video_player_mjob($mjob_post)
    {     
        $short_video=get_short_video_mjob($mjob_post->ID);
                
        if(!empty($short_video) && !empty($short_video['url']))
        {           
        ?>       
            <div class="video-area-description">
            <button id="show-big-player-mjob-btn" data-video-type="<?php echo $short_video['type']; ?>"  data-toggle="modal" class="btn btn-info" data-target="#mjob-player-big-modal"><i class="fa fa-play"></i> Show Video</button>
            <div class="modal fade mjob-player-big" id="mjob-player-big-modal" role="dialog">
                <div class="modal-dialog mjob-big-dialog">
                    <?php if($short_video['type']=='upload'): ?>
                        <div class="modal-content mjob-player-big-content">                        
                                <video id="mjob-modal-video-player" class="mjob-modal-video-player" disablepictureinpicture playsinline>
                                    <source src="<?php echo $short_video['url']; ?>"  type="<?php echo $short_video['mime_type']; ?>" />    
                                </video>                                            
                        </div>
                    <?php endif; ?>   

                    <?php if($short_video['type']=='youtube'): ?>
                        <?php                         
                        $youtube_src_mjob='https://www.youtube.com/embed/'.$short_video['url'].'?autoplay=0&mute=0&loop=1&color=white&controls=0&playsinline=1&rel=0&enablejsapi=1&playlist='.$short_video['url'];
                        ?>
                        <div class="modal-content mjob-youtube-big-content">                    
                                <iframe 
                                id="yt-mjob-big-player"
                                class="mjob-modal-youtube-player" 
                                src="<?php echo $youtube_src_mjob; ?>" 
                                title="YouTube video player" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope;" 
                                    >
                                </iframe>                                                                   
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            </div> 
            <?php              
            
        }     
    }

    function get_short_videos($condition= array())
    {
        $video_collection=array();
        $video_collection_info=array();

        if($condition && !empty($condition))
        {
            $page_number=!isset($condition['page_number']) ? : 1;
            $video_args=array(
                'post_type' => 'short_video',  
                'posts_per_page'=>$condition['posts_per_page'],
                'paged'=>$page_number,
                'post_status'=>'publish',                
            );

            if($condition['orderby']=='view')
            {
                //sort by view count
                $video_args['meta_key']='short_video_view_count';
                $video_args['order']=$condition['order'];
                $video_args['orderby']='meta_value_num'; 
            }
            else
            {
                //sort by date
                $video_args['order']=$condition['order'];
                $video_args['orderby']='date'; 
            }
            $short_video_query=new WP_Query($video_args);
            if($short_video_query->have_posts())
            {
                while($short_video_query->have_posts())
                {
                    $short_video_query->the_post();
                    $current_video = get_post(); // Get the current post object instead of using global $post
                    $converted_video=$this->convert_short_video($current_video);
                    $video_collection[]=$converted_video;
                }
            }
            $video_collection_info['video_list']= $video_collection;
            $video_collection_info['max_num_pages']= $short_video_query->max_num_pages;
            $video_collection_info['found_posts']= $short_video_query->found_posts;
            wp_reset_postdata();
        }
        return $video_collection_info;
    }

    //convert short video
    function convert_short_video($short_video)
    {       
        $short_video->videoType=get_post_meta($short_video->ID,'short_video_type',true);
        $short_video->videoCaption=$short_video->post_title;
        $short_video->viewCount=get_post_meta($short_video->ID,'short_video_view_count',true) ? : 0;
        $short_video->videoInfo=get_short_video_url($short_video->ID);
        //video owner
        $video_owner=short_video_get_owner_info($short_video->post_author);        
        $short_video->ownerName=$video_owner->display_name;
        $short_video->ownerRating=$video_owner->ownerRating;
        $short_video->ownerAvatarURL=$video_owner->ownerAvatarURL;
        $short_video->ownerProfileURL=$video_owner->ownerProfileURL;
        $short_video->ownerLocation=$video_owner->ownerLocation;
        $short_video->ownerLanguage=$video_owner->ownerLanguage;

        //service list
        $short_video->serviceList=get_service_list_short_video($short_video->ID);
       
        return $short_video;
    }

    function mje_short_video_slider_section_init($atts)
    {
        $atts = shortcode_atts(array(
            'orderby' => 'date', // date or view , default is date
            'order' =>'desc', // desc or asc , default is desc
            'posts_per_page' => 10, // the amount of video displays, default is 10
            'title' => 'Short Videos', // block title, default is Short Videos    
        ), $atts, 'mje_short_video_slider_section');
        
        $short_video_collection=$this->get_short_videos($atts);
        ob_start();   
        if(is_array($short_video_collection) && $short_video_collection['found_posts'] > 0)
        {

        ?>
        <div class="mje-short-videos-top-wrapper">

            <div class="container mje-short-videos-list-wrapper">
                <p class="mje-short-video-block-title"><?php echo esc_html($atts['title']); ?></p>                
                <!--start slider -->
                <div class="row swiper mje-short-video-slider">
                    <div class="swiper-wrapper">                      

                        <?php foreach($short_video_collection['video_list'] as $video_item): ?>                         
                            <div data-swiperb-item="<?php echo $video_item->ID; ?>" class="col-md-3 col-lg-3 col-sm-12 swiper-slide loading-skeleton-wrapper <?php if($video_item->videoType=='upload') echo 'mje-short-video-local-item'; else echo 'mje-short-video-item'; ?>">                               
                                <!-- local player -->
                                <?php if($video_item->videoType == 'upload'): ?>  
                                    
                                        <video data-video-item="<?php echo $video_item->ID; ?>" data-plyr-config="<?php echo htmlspecialchars(json_encode($video_item), ENT_QUOTES, 'UTF-8'); ?>" data-video-src="<?php echo $video_item->videoInfo->url; ?>" class="local-item-video-player" disablepictureinpicture playsinline>
                                            <source src="<?php echo $video_item->videoInfo->url; ?>" type="<?php echo $video_item->videoInfo->mime_type; ?>" />    
                                        </video>
                                        <div class="open-box-info-area"><button type="button" data-box-item="<?php echo $video_item->ID; ?>" class="open-boxinfo-local-video open-box-info">Info</button></div>                        
                                     <!-- content for big modal ( not display , use js clone this elements) -->
                                    <div id="short-video-serviceList-<?php echo $video_item->ID; ?>" style="display:none;">
                                        <?php foreach($video_item->serviceList as $service_item): ?>
                                            <div class="video-mjob-section">
                                                <a href="<?php echo $service_item['service_link']; ?>" class="video-service-link"><?php echo $service_item['service_title']; ?></a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <!-- end content big modal -->
                                <?php endif; ?>

                                <!-- youtube player -->
                                <?php if($video_item->videoType == 'youtube'): ?> 
                                    <div class="yt-video-player-container">                                    
                                        <?php 
                                            $youtube_src='https://www.youtube.com/embed/'.$video_item->videoInfo->url.'?autoplay=0&mute=0&loop=1&color=white&controls=0&playsinline=1&rel=0&enablejsapi=1&playlist='.$video_item->videoInfo->url;
                                        ?>                                   
                                        <iframe data-swiperb-item="<?php echo $video_item->ID; ?>" style="border-radius:10px;" data-short-video-id="<?php echo $video_item->ID; ?>" class="mje-short-video-embed-iframe yt-short-video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope;" src="<?php echo $youtube_src; ?>">
                                        </iframe>
                                        <div class="open-btn-area"><button data-video-info="<?php echo htmlspecialchars(json_encode($video_item), ENT_QUOTES, 'UTF-8'); ?>" type="button" data-box-item="<?php echo $video_item->ID; ?>" class="open-yt-btn">Info</button></div>
                                        <!-- content for big modal ( not display , use js clone this elements) -->
                                        <div id="yt-short-video-serviceList-<?php echo $video_item->ID; ?>" style="display:none;">
                                            <?php foreach($video_item->serviceList as $service_item): ?>
                                                <div class="video-mjob-section">
                                                    <a href="<?php echo $service_item['service_link']; ?>" class="video-service-link"><?php echo $service_item['service_title']; ?></a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <!-- end content big modal -->
                                    </div>
                                    
                                <?php endif; ?>
                               <div class="loading-frame"></div>  

                               <!-- info box content for mobile view -->      
                               <div data-info-box="<?php echo $video_item->ID; ?>" class="video-info-box">
                                    <div class="video-info-box-content">
                                        
                                            <div class="video-owner-profile">
                                                <div class="video-owner-info">
                                                    <a href="<?php echo $video_item->ownerProfileURL; ?>">
                                                        <img class="mobile-box-avatar" src="<?php echo $video_item->ownerAvatarURL; ?>">
                                                        <div class="video-owner-name-rate">
                                                            <span class="mobile-box-ownername"><?php echo $video_item->ownerName; ?></span> 
                                                            <div class="video-rate-it star" data-score="<?php echo $video_item->ownerRating; ?>"></div>
                                                        </div>                                    
                                                    </a>
                                                    
                                                </div>
                                                <div class="video-owner-viewpf">
                                                    <a href="<?php echo $video_item->ownerProfileURL; ?>" class="video-viewpf-btn">
                                                        <?php _e('Profile','mje_short_video'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <div class="video-extra-info">
                                                <div class="video-owner-lang-location">
                                                    <span><i class="fa fa-map-marker"></i> <?php echo $video_item->ownerLocation; ?></span>
                                                    <span><i class="fa fa-globe"></i> <?php echo $video_item->ownerLanguage; ?></span>                            
                                                </div>
                                                <div class="video-owner-bio">                                                                                            
                                                    <?php echo $video_item->videoCaption; ?>
                                                </div>
                                            </div>

                                            <!-- service_list section -->
                                            <div class="mobileview-list-service">
                                                    <?php foreach($video_item->serviceList as $service_item): ?>
                                                        <div class="video-mjob-section">
                                                            <a href="<?php echo $service_item['service_link']; ?>" class="video-service-link"><?php echo $service_item['service_title']; ?></a>
                                                        </div>
                                                    <?php endforeach; ?>
                                            </div>
                                            <div class="close-mobile-box-info">
                                                <button data-close-box="<?php echo $video_item->ID; ?>" class="close-box-info-btn close-box-js" type="button">Close</button>
                                            </div>
                                            <!-- end service_list section -->
                                        

                                    </div>
                               </div>             
                               <!-- end info box content for mobile view -->      
                            </div>
                        <?php endforeach; ?>
                     
                    </div>
                </div>
                <!--end slider -->

                <!-- next / prev buttons -->
                <div class="video-mje-next-area">
                    <i class="fa fa-chevron-right"></i>
                </div>

                <div class="video-mje-prev-area">
                    <i class="fa fa-chevron-left"></i>
                </div>
                <!-- end next / prev buttons -->
           
            </div>

        </div>
        <!-- modal full screen -->
        <div class="modal fade video-player-big" id="video-player-big-modal" role="dialog">

            <div class="modal-dialog modal-lg">
                <div class="modal-content video-player-big-content">
                    <!-- video section -->
                    <div class="video-media-section">
                        <video id="big-local-video" class="big-local-video" disablepictureinpicture playsinline>
                            <source src="" type="" />    
                        </video>
                       
                    </div>

                    <!-- info section -->
                    <div class="video-info-section">
                        <div class="video-owner-profile">
                            <div class="video-owner-info">
                                <a id="video-owner-profile-wrap-link" href="#">
                                    <img id="video-owner-avatar" src="">
                                    <div class="video-owner-name-rate">
                                        <span id="video-owner-name"></span> 
                                        <div id="video-owner-score" class="video-rate-it star" data-score="1"></div>
                                    </div>                                    
                                </a>
                                
                            </div>
                            <div class="video-owner-viewpf">
                                <a href="#" class="video-viewpf-btn" id="video-owner-viewpf-btn">
                                    <?php _e('View Profile','mje_short_video'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="video-extra-info">
                            <div class="video-owner-lang-location">
                                <span><i class="fa fa-map-marker"></i> <span id="video-owner-location"></span></span>
                                <span><i class="fa fa-globe"></i> <span id="video-owner-language"></span></span>                            
                            </div>
                            <div class="video-owner-bio" id="video-caption-content">                                                                                            
                                
                            </div>
                        </div>

                        <!-- service_list section -->
                        <div id="serviceListSection">

                        </div>
                        <!-- end service_list section -->

                    </div>     

                </div>
            </div>
                                
        </div>
        <!-- end modal full screen -->

        <!-- YT modal full screen -->
        <div class="modal fade video-player-big" id="yt-video-player-big-modal" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content video-player-big-content">
                    <!-- video section -->
                    <div class="video-media-section">
                        <iframe 
                                data-youtube-id=""
                                data-video-url=""
                                id="yt-big-player-video-list"
                                class="list-modal-youtube-player" 
                                src="https://www.youtube.com/embed/WZM3VtPiUyo?autoplay=0&mute=0&loop=1&color=white&controls=0&playsinline=1&rel=0&enablejsapi=1&playlist=WZM3VtPiUyo" 
                                title="YouTube video player" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope;"                                 
                            >
                        </iframe>     
                    </div>

                    <!-- info section -->
                    <div class="video-info-section">
                        <div class="video-owner-profile">
                            <div class="video-owner-info">
                                <a id="yt-video-owner-profile-wrap-link" href="#">
                                    <img id="yt-video-owner-avatar" src="">
                                    <div class="video-owner-name-rate">
                                        <span id="yt-video-owner-name"></span> 
                                        <div id="yt-video-owner-score" class="video-rate-it star" data-score="1"></div>
                                    </div>                                    
                                </a>
                                
                            </div>
                            <div class="video-owner-viewpf">
                                <a href="#" class="video-viewpf-btn" id="yt-video-owner-viewpf-btn">
                                    <?php _e('View Profile','mje_short_video'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="video-extra-info">
                            <div class="video-owner-lang-location">
                                <span><i class="fa fa-map-marker"></i> <span id="yt-video-owner-location"></span></span>
                                <span><i class="fa fa-globe"></i> <span id="yt-video-owner-language"></span></span>                            
                            </div>
                            <div class="video-owner-bio" id="yt-video-caption-content">                                                                                            
                                
                            </div>
                        </div>

                        <!-- service_list section -->
                        <div id="yt-serviceListSection">

                        </div>
                        <!-- end service_list section -->

                    </div>     

                </div>
            </div>
                    
        </div>
        <!-- end YT modal full screen -->

        <?php
        }
        return ob_get_clean();      
    }
}
new MJE_Short_Videos_Frontend();