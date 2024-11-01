<?php
class MJE_Short_Videos_Backend
{
    public static $instance;

    function __construct(){        		       
		$this->init_hook();     
	}

    function init_hook()
    {              
        add_action('init',array($this, 'register_post_type_short_video' ));
        
        //hook action to add short video
        add_action('ae_insert_mjob_post',array($this,'add_short_video'),10,2);

        //show video player to edit mjob form
        add_filter('ae_convert_mjob_post',array($this,'show_video_player_edit_mjob_form'),99,1);
        add_action('ae_update_mjob_post',array($this,'update_short_video_mjob'),999,2);

        //automatic cleaning video files system ( the videos dont belong to any service or post)
        add_filter('cron_schedules', array($this,'delete_trash_video_files_schedules'), 99);
        add_action('init', array($this,'schedule_delete_trash_video_task'),999);
        add_action('collect_delete_trash_video', array($this,'collect_delete_trash_video_action'),999);        

    }

    function delete_trash_video_files_schedules($schedules)
    {
         // Add a new custom cron schedule named 'daily'
            $schedules['daily_midnight'] = array(
             'interval' => 24 * 60 * 60, // 24 hours in seconds           
            'display' => 'Once Daily at 12 PM',
        );
        return $schedules;
    }

    function schedule_delete_trash_video_task()
    {
        if (!wp_next_scheduled('collect_delete_trash_video')) {
            wp_schedule_event(strtotime('12:00 PM'), 'daily_midnight', 'collect_delete_trash_video');
           //wp_schedule_event(time(), 'daily_midnight', 'collect_delete_trash_video');
        }
    }

    function collect_delete_trash_video_action()
    {
        global $post;
    
        $args_attachment = array(
            'post_type' => 'attachment', 
            'post_status' =>'inherit',             
            'posts_per_page' => -1, 
            'meta_query'=> array(            
                array(
                    'key' => 'is_temp_short_video',
                    'compare' => 'EXISTS', 
                ),        
            ),
            'date_query' => array(
                array(
                    'column' => 'post_date', 
                    'before' => '1 day ago',
                ),
            ),
        );
    
        $query = new WP_Query($args_attachment);
        if ($query->have_posts()) 
        {            
            while ($query->have_posts())
            {
                $query->the_post();     
                wp_delete_attachment($post->ID,true);
            }
            wp_reset_postdata();      
        }        
    
    }

    function register_post_type_short_video()
    {
        $labels = array(
            'name'               => _x( 'Short Video', 'post type general name', 'mje_short_video' ),
            'singular_name'      => _x( 'Short Video', 'post type singular name', 'mje_short_video' ),
            'menu_name'          => _x( 'Short Videos', 'admin menu', 'mje_short_video' ),
            'name_admin_bar'     => _x( 'Short Video', 'add new on admin bar', 'mje_short_video' ),
            'add_new'            => _x( 'Add New', 'Short Video', 'mje_short_video' ),
            'add_new_item'       => __( 'Add New Short Video', 'mje_short_video' ),
            'new_item'           => __( 'New Short Video', 'mje_short_video' ),
            'edit_item'          => __( 'Edit Short Video', 'mje_short_video' ),
            'view_item'          => __( 'View Short Video', 'mje_short_video' ),
            'all_items'          => __( 'All Short Videos', 'mje_short_video' ),
            'search_items'       => __( 'Search Short Video', 'mje_short_video' ),
            'not_found'          => __( 'No Short Videos found', 'mje_short_video' ),
            'not_found_in_trash' => __( 'No Short Videos found in trash', 'mje_short_video' ),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'short_video' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ), // Adjust as needed
        );
    
        register_post_type( 'short_video', $args );
    }


    public static function get_instance()    
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    function add_short_video($result,$args)
    {        
        
        if(isset($args['choosenvideoType']) && $args['choosenvideoType']=='upload')
        {               
            if(isset($args['short_video_attach_id']) && !empty($args['short_video_attach_id']))
            {
                
                $short_video_args=array(
                        'post_title'=>sanitize_text_field($args['video_caption']),
                        'post_content'=>wp_strip_all_tags($args['video_caption']),
                        'post_status'=>'publish',
                        'post_type'=>'short_video',
                );
                $inserted_short=wp_insert_post($short_video_args);

                if($inserted_short && !is_wp_error($inserted_short))
                {
                    update_post_meta($inserted_short,'short_video_type',$args['choosenvideoType']); //update video type for video
                    update_post_meta($inserted_short,'service_list',array($result)); //update service ids list for video
                    update_post_meta($inserted_short,'short_video_view_count',0); 

                    //update attach video ( associate to video post) and remove meta temp file
                    $attach_update = array('ID' => $args['short_video_attach_id'], 'post_parent' => $inserted_short); 
                    wp_update_post($attach_update);                    
                    delete_post_meta($args['short_video_attach_id'],'is_temp_short_video','true');
                
                    update_post_meta($result,'short_video_id',$inserted_short); //update short video id to service                    
                }

            }
        }
        if(isset($args['choosenvideoType']) && $args['choosenvideoType']=='youtube')
        {
                if(isset($args['youtube_video_caption']) && isset($args['youtube_video_link']))            
                {
                    $youtube_video_args=array(
                        'post_title'=>sanitize_text_field($args['youtube_video_caption']),
                        'post_content'=>wp_strip_all_tags($args['youtube_video_caption']),
                        'post_status'=>'publish',
                        'post_type'=>'short_video',
                );
                $youtube_short=wp_insert_post($youtube_video_args);
                if($youtube_short && !is_wp_error($youtube_short))
                {
                    update_post_meta($youtube_short,'short_video_type',$args['choosenvideoType']); //update video type for video
                    update_post_meta($youtube_short,'service_list',array($result)); //update service ids list for video
                    update_post_meta($youtube_short,'short_video_count',0); 
                    
                    update_post_meta($youtube_short,'youtube_video_link',$args['youtube_video_link']); 

                    $youtube_video_id=$this->extract_youtube_video_id($args['youtube_video_link']);
                    update_post_meta($youtube_short,'youtube_video_id',$youtube_video_id); 
                    
                    update_post_meta($result,'short_video_id',$youtube_short); //update short video id to service                    
                }
              
            }
        }
    }

    function extract_youtube_video_id($url) {
        // Kiểm tra nếu URL là từ youtu.be
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Kiểm tra nếu URL là dạng youtube.com/watch
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Kiểm tra nếu URL là dạng youtube.com/shorts
        if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Trả về null nếu không tìm thấy video_id
        return null;
    }
    
    function show_video_player_edit_mjob_form($result)
    {
        $video_html_render='';   
        $video_caption='';
        $short_video=get_short_video_mjob($result->ID);
                
        if(!empty($short_video) && !empty($short_video['url']))
        {       
            $video_html_render=$this->render_video_player_edit_form($short_video);
            $video_caption=$short_video['video_caption'];
        }
        $result->video_html_render= $video_html_render;
        $result->current_video_caption=$video_caption;
        return $result;
    }

    function render_video_player_edit_form($short_video)
    {
        ob_start();
        ?>
        <div id="mjob-video-edit-wrapper" class="mjob-edit-video-player-area">
        <?php if($short_video['type']=='upload'): ?>           
                <video id="mjob-edit-video-player" class="mjob-edit-video-player" disablepictureinpicture playsinline>
                    <source src="<?php echo $short_video['url']; ?>"  type="<?php echo $short_video['mime_type']; ?>" />    
                </video>            
        <?php endif; ?>

        <?php if($short_video['type']=='youtube'): ?>   
            <?php $youtube_src_mjob='https://www.youtube.com/embed/'.$short_video['url'].'?autoplay=0&mute=0&loop=1&color=white&controls=0&playsinline=1&rel=0&enablejsapi=1&playlist='.$short_video['url']; ?>
            <iframe    
                style="border-radius:10px;"            
                class="mjob-edit-video-player" 
                src="<?php echo $youtube_src_mjob; ?>" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope;" 
                >
                </iframe>        
        <?php endif; ?>
            
        </div>

        <div class="remove-video-player-area">
                <input type="hidden" name="remove-video-id" id="remove-video-id" value="<?php echo $short_video['video_id']; ?>">
                <button id="remove-video-player" type="button" class="btn-danger btn">
                    Remove Video
                </button>

                <button id="undo-video-player" type="button" class="btn-info btn" style="display:none">
                    Undo
                </button>
        </div>

        <?php
        return ob_get_clean();
    }
    
    function update_short_video_mjob($result,$args)
    {
        //remove old video if the users choose to remove or upload / insert new video/ youtube link
        if((isset($args['short_video_attach_id']) && !empty($args['short_video_attach_id'])) || // if user upload new video 
            (isset($args['remove_current_video']) && $args['remove_current_video']=='remove') || // if user click to remove button
            ( $args['choosenvideoType']=='upload' || $args['choosenvideoType']=='youtube') // if user insert new youtube
            )
        {
            delete_post_meta($result,'short_video_id'); //remove out of mjob
            //delete short video and files.
            $delete_type=get_post_meta($args['remove_current_video_id'],'short_video_type',true);
            if($delete_type=='upload') // if upload type --> delete files on server.
            {
                $attached_video=get_attached_media('video',$args['remove_current_video_id']);
                if($attached_video && !is_wp_error($attached_video))
                {
                    foreach($attached_video as $delete_video)
                    {
                        wp_delete_attachment($delete_video->ID,true); // delete attachment and file
                    }
                }
            }          

            wp_delete_post($args['remove_current_video_id']); // delete post            
        }
        //only update video caption of current video
       
            $current_video=get_short_video_mjob($result);
            if($current_video && isset($args['current_video_caption']))
            {
                $update_video=array('ID'=>$current_video['video_id'],'post_title'=>$args['current_video_caption']);
                wp_update_post($update_video);
            }
                           
        //update new video upload
        if(isset($args['choosenvideoType']) && $args['choosenvideoType']=='upload')
        {               
            if(isset($args['short_video_attach_id']) && !empty($args['short_video_attach_id']))
            {
                
                $short_video_args=array(
                        'post_title'=>sanitize_text_field($args['video_caption']),
                        'post_content'=>wp_strip_all_tags($args['video_caption']),
                        'post_status'=>'publish',
                        'post_type'=>'short_video',
                );
                $inserted_short=wp_insert_post($short_video_args);

                if($inserted_short && !is_wp_error($inserted_short))
                {
                    update_post_meta($inserted_short,'short_video_type',$args['choosenvideoType']); //update video type for video
                    update_post_meta($inserted_short,'service_list',array($result)); //update service ids list for video
                    update_post_meta($inserted_short,'short_video_view_count',0); 

                    //update attach video ( associate to video post) and remove meta temp file
                    $attach_update = array('ID' => $args['short_video_attach_id'], 'post_parent' => $inserted_short); 
                    wp_update_post($attach_update);                    
                    delete_post_meta($args['short_video_attach_id'],'is_temp_short_video','true');
                
                    update_post_meta($result,'short_video_id',$inserted_short); //update short video id to service                    
                }

            }           
        }

        //update new video youtube
        if(isset($args['choosenvideoType']) && $args['choosenvideoType']=='youtube')
        {
                if(isset($args['youtube_video_caption']) && isset($args['youtube_video_link']))            
                {
                    $youtube_video_args=array(
                        'post_title'=>sanitize_text_field($args['youtube_video_caption']),
                        'post_content'=>wp_strip_all_tags($args['youtube_video_caption']),
                        'post_status'=>'publish',
                        'post_type'=>'short_video',
                );
                $youtube_short=wp_insert_post($youtube_video_args);
                if($youtube_short && !is_wp_error($youtube_short))
                {
                    update_post_meta($youtube_short,'short_video_type',$args['choosenvideoType']); //update video type for video
                    update_post_meta($youtube_short,'service_list',array($result)); //update service ids list for video
                    update_post_meta($youtube_short,'short_video_count',0); 
                    
                    update_post_meta($youtube_short,'youtube_video_link',$args['youtube_video_link']); 

                    $youtube_video_id=$this->extract_youtube_video_id($args['youtube_video_link']);
                    update_post_meta($youtube_short,'youtube_video_id',$youtube_video_id); 
                    
                    update_post_meta($result,'short_video_id',$youtube_short); //update short video id to service                    
                }
            
            }
        }
    }
}
new MJE_Short_Videos_Backend();